<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Ridibooks\Cms\Auth\LoginService;
use Ridibooks\Cms\Service\Auth\Authenticator\BaseAuthenticator;
use Ridibooks\Cms\Service\Auth\Authenticator\OAuth2Authenticator;
use Ridibooks\Cms\Service\Auth\Authenticator\PasswordAuthenticator;
use Ridibooks\Cms\Service\Auth\Authenticator\TestAuthenticator;
use Ridibooks\Cms\Service\Auth\Session;
use Silex\Api\BootableProviderInterface;
use Silex\Application;

class AuthServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    public function register(Container $app)
    {
        $app['auth.options'] = [];

        $app['auth.enabled'] = [
            OAuth2Authenticator::AUTH_TYPE,
            PasswordAuthenticator::AUTH_TYPE,
            TestAuthenticator::AUTH_TYPE,
        ];

        $app['auth.session'] = function (Container $app): Session\SessionStorageInterface {
            $enabled = $app['auth.enabled'];

            $cookie_keys = [BaseAuthenticator::KEY_AUTH_TYPE => 'auth_type'];
            foreach ($enabled as $enabled_type) {
                if (isset($app['auth.' . $enabled_type . '.cookie_keys'])) {
                    $cookie_keys = array_merge($cookie_keys, $app['auth.' . $enabled_type . '.cookie_keys']);
                }
            }

            return new Session\CookieSessionStorage($cookie_keys);
        };

        $app['auth.authenticator'] = function (Container $app): BaseAuthenticator {
            /** @var Session\SessionStorageInterface $session */
            $session = $app['auth.session'];
            $auth_type = $session->get(BaseAuthenticator::KEY_AUTH_TYPE);

            return $app['auth.' . $auth_type . '.authenticator'];
        };

        // OAuth2 authenticators
        $app['auth.oauth2.clients'] = [
            // 'oauth2 provider name' => ${Auth\OAuth2ClientInterface object}
        ];

        $app['auth.oauth2.cookie_keys'] = [
            OAuth2Authenticator::KEY_PROVIDER => 'oauth2_provider',
            OAuth2Authenticator::KEY_ACCESS_TOKEN => 'oauth2_access_token',
            OAuth2Authenticator::KEY_REFRESH_TOKEN => 'oauth2_refresh_token',
            OAuth2Authenticator::KEY_STATE => 'oauth2_state',
            OAuth2Authenticator::KEY_RETURN_URL => 'oauth2_return_url',

            // TODO: Remove these
            LoginService::TOKEN_COOKIE_NAME => LoginService::TOKEN_COOKIE_NAME,
            LoginService::ADMIN_ID_COOKIE_NAME => LoginService::ADMIN_ID_COOKIE_NAME
        ];

        $app['auth.oauth2.authenticator'] = function (Container $app) {
            return new OAuth2Authenticator($app['auth.session'], $app['auth.oauth2.clients']);
        };

        // Password authenticators
        $app['auth.password.authenticator'] = function (Container $app) {
            return new PasswordAuthenticator($app['auth.session']);
        };

        // Test authenticators
        $app['auth.test.cookie_keys'] = [
            TestAuthenticator::KEY_USER_ID => 'test_user_id',
        ];

        $app['auth.test.authenticator'] = function (Container $app) {
            $test_option = array_replace([
                'test_user_id' => 'admin',
            ], $app['auth.options']['test']);

            return new TestAuthenticator($app['auth.session'], $test_option['test_user_id']);
        };
    }

    public function boot(Application $app)
    {
        if (empty($app['auth.enabled'])) {
            throw new \InvalidArgumentException(
                'You should enable one of \'' .
                OAuth2Authenticator::AUTH_TYPE . '\', \'' .
                PasswordAuthenticator::AUTH_TYPE . '\', \'' .
                TestAuthenticator::AUTH_TYPE . '\''
            );
        }

        $session = $app['auth.session'];
        if ($session instanceof Session\CookieSessionStorage) {
            $app->before('auth.session:readCookie', Application::EARLY_EVENT);
            $app->after('auth.session:writeCookie', Application::LATE_EVENT);
        }
    }
}
