<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Application;

class AuthServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    public function register(Container $app)
    {
        $app['auth.options'] = [];

        $app['auth.enabled'] = ['oauth2', 'password', 'test'];

        $app['auth.storage'] = function () {
            return new Storage\AuthCookieStorage();
        };

        // OAuth2 clients array -> [ 'oauth2 provider name' => ${Auth\OAuth2ClientInterface object} ]
        $app['auth.oauth2.clients'] = [];

        // Authenticators
        $app['auth.oauth2.authenticator'] = function (Container $app) {
            return new Authenticator\OAuth2Authenticator($app['auth.oauth2.clients'], $app['auth.storage']);
        };

        $app['auth.password.authenticator'] = function (Container $app) {
            return new Authenticator\PasswordAuthenticator($app['auth.storage']);
        };

        $app['auth.test.authenticator'] = function (Container $app) {
            $test_option = array_replace([
                'test_user_id' => 'admin',
            ], $app['auth.options']['test']);

            return new Authenticator\TestAuthenticator($test_option['test_user_id'], $app['auth.storage']);
        };

        // Controllers
        $app['auth.oauth2.controller'] = function (Container $app) {
            $authenticator = $app['auth.oauth2.authenticator'];
            $home_url = $app['url_generator']->generate('home');
            return new Controller\OAuth2Controller($authenticator, $home_url);
        };

        $app['auth.password.controller'] = function (Container $app) {
            $authenticator = $app['auth.password.authenticator'];
            $home_url = $app['url_generator']->generate('home');
            return new Controller\DefaultController($authenticator, $home_url);
        };

        $app['auth.test.controller'] = function (Container $app) {
            $authenticator = $app['auth.test.authenticator'];
            $home_url = $app['url_generator']->generate('home');
            return new Controller\DefaultController($authenticator, $home_url);
        };
    }

    public function boot(Application $app)
    {
        $enabled = $app['auth.enabled'];

        if (in_array('oauth2', $enabled)) {
            $oauth2_option = array_replace([
                'authorize' => '/auth/oauth2/{provider}/authorize',
                'callback' => '/auth/oauth2/callback',
            ], $app['auth.options']['oauth2']);

            $app->get($oauth2_option['authorize'], 'auth.oauth2.controller:authorize')
                ->bind('oauth2_authorize');

            $app->get($oauth2_option['callback'], 'auth.oauth2.controller:callback')
                ->bind('oauth2_callback');

            $app->before('auth.oauth2.authenticator:readCookie', Application::EARLY_EVENT);
            $app->after('auth.oauth2.authenticator:writeCookie', Application::LATE_EVENT);
        }

        if (in_array('password', $enabled)) {
            $password_option = array_replace([
                'authorize' => '/auth/password/authorize',
            ], $app['auth.options']['password']);

            $app->get($password_option['authorize'], 'auth.password.controller:authorize')
                ->bind('password_authorize');

            $app->before('auth.password.authenticator:readCookie', Application::EARLY_EVENT);
            $app->after('auth.password.authenticator:writeCookie', Application::LATE_EVENT);
        }

        if (in_array('test', $enabled)) {
            $test_option = array_replace([
                'authorize' => '/auth/test/authorize',
            ], $app['auth.options']['test']);

            $app->get($test_option['authorize'], 'auth.test.controller:authorize')
                ->bind('test_authorize');

            $app->before('auth.test.authenticator:readCookie', Application::EARLY_EVENT);
            $app->after('auth.test.authenticator:writeCookie', Application::LATE_EVENT);
        } else {
            throw new \InvalidArgumentException('You should enable one of \'oauth\', \'password\', \'test\'');
        }
    }
}
