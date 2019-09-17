<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Ridibooks\Cms\Service\Auth\Authenticator\BaseAuthenticator;
use Ridibooks\Cms\Service\Auth\Authenticator\OAuth2Authenticator;
use Ridibooks\Cms\Service\Auth\Authenticator\TestAuthenticator;
use Ridibooks\Cms\Service\Auth\Authenticator\CFAuthenticator;
use Silex\Api\BootableProviderInterface;
use Silex\Application;

class AuthenticationServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    public function register(Container $app)
    {
        $app['auth.options'] = [];

        $app['auth.enabled'] = $app['auth.enabled'] ?? [
            OAuth2Authenticator::AUTH_TYPE,
            TestAuthenticator::AUTH_TYPE,
        ];

        //TODO: Remove this after OAuth2 authorization is implemented
        $app['auth.domain_auth_removed'] = str_replace('auth.', '', $_SERVER['HTTP_HOST']);
        $app['auth.domain'] = $_SERVER['HTTP_HOST'];

        $app['auth.cookie.default'] = [
            'path' => '/',
            'lifetime' => 0,
            'domain' => $app['auth.domain_auth_removed'] ?? null,
            'secure' => $app['auth.is_secure'] ?? false,
            'http_only' => true,
        ];

        $app['auth.session'] = function (Container $app): Session\SessionStorageInterface {
            $enabled = $app['auth.enabled'];

            $cookie_default = $app['auth.cookie.default'];

            $cookie_options = [
                BaseAuthenticator::KEY_AUTH_TYPE => [
                    'key' => 'auth_type',
                    'lifetime' => 0,
                ],
            ];

            foreach ($enabled as $enabled_type) {
                if (isset($app['auth.cookie.' . $enabled_type])) {
                    $cookie_options = array_merge($cookie_options, $app['auth.cookie.' . $enabled_type]);
                }
            }

            return new Session\CookieSessionStorage($cookie_options, $cookie_default);
        };

        $app['auth.authenticator'] = $app->factory(function (Container $app): ?BaseAuthenticator {
            /** @var Session\SessionStorageInterface $session */
            $session = $app['auth.session'];
            $auth_type = $session->get(BaseAuthenticator::KEY_AUTH_TYPE);

            return $app['auth.authenticator.' . $auth_type] ?? null;
        });

        // OAuth2 authenticators
        $app['auth.oauth2.clients'] = [
            // 'oauth2 provider name' => ${Auth\OAuth2ClientInterface object}
        ];

        $app['auth.cookie.oauth2'] = [
            OAuth2Authenticator::KEY_PROVIDER => [
                'key' => 'oauth2_provider',
                'lifetime' => 60 * 60 * 24 * 30, // 30 days,
            ],
            OAuth2Authenticator::KEY_ACCESS_TOKEN => [
                'key' => 'cms-token',
                'lifetime' => 60 * 60 * 2, // 2 hours,
            ],
            OAuth2Authenticator::KEY_REFRESH_TOKEN => [
                'key' => 'cms-refresh',
                'domain' => $app['auth.domain'],
                'lifetime' => 60 * 60 * 24 * 30, // 30 days,
            ],
            OAuth2Authenticator::KEY_STATE => [
                'key' => 'oauth2_state',
                'domain' => $app['auth.domain'],
                'lifetime' => 60 * 3, // 3 minutes,
            ],
            OAuth2Authenticator::KEY_RETURN_URL => [
                'key' => 'oauth2_return_url',
                'lifetime' => 60 * 3, // 3 minutes,
            ],
            // TODO: Should be removed (backward compatibility)
            OAuth2Authenticator::KEY_USER_ID => [
                'key' => 'admin-id',
                'lifetime' => 60 * 60 * 2, // 2 hours,
            ],
        ];

        if (in_array(OAuth2Authenticator::AUTH_TYPE, $app['auth.enabled'])) {
            $app['auth.authenticator.oauth2'] = function (Container $app) {
                return new OAuth2Authenticator($app['auth.session'], $app['auth.oauth2.clients']);
            };
        }

        if (in_array(TestAuthenticator::AUTH_TYPE, $app['auth.enabled'])) {
            $app['auth.authenticator.test'] = function (Container $app) {
                $test_option = $app['auth.options']['test'] ?? [];
                return new TestAuthenticator($app['auth.session'], $test_option['test_user_id']);
            };
        }

        $app['auth.cookie.cloudflare'] = [
            CFAuthenticator::KEY_CF_TOKEN => [
                'key' => CFAuthenticator::KEY_CF_TOKEN,
            ],
        ];
        if (in_array(CFAuthenticator::AUTH_TYPE, $app['auth.enabled'])) {
            $app['auth.authenticator.cloudflare'] = function (Container $app) {
                return new CFAuthenticator($app['auth.session']);
            };
        }
    }

    public function boot(Application $app)
    {
        if (empty($app['auth.enabled'])) {
            throw new \InvalidArgumentException(
                'You should enable one of \'' .
                OAuth2Authenticator::AUTH_TYPE . '\', \'' .
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
