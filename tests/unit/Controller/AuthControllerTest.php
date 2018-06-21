<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Ridibooks\Cms\Controller\AuthController;
use Ridibooks\Cms\Service\Auth\AuthenticationServiceProvider;
use Ridibooks\Cms\Service\Auth\Authenticator\BaseAuthenticator;
use Ridibooks\Cms\Service\Auth\Authenticator\OAuth2Authenticator;
use Ridibooks\Cms\Service\Auth\Authenticator\TestAuthenticator;
use Ridibooks\Cms\Service\Auth\OAuth2\Client\AzureClient;
use Ridibooks\Cms\Tests\Mock\MockOAuth2Client;
use Silex\Application;
use Silex\Provider\RoutingServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthControllerTest extends TestCase
{
    /** @var AuthController $controller */
    private $controller;

    /** @var Application $app */
    private $app;

    public function setUp()
    {
        $this->controller = $this->getMockBuilder('Ridibooks\Cms\Controller\AuthController')
            ->setMethods(['addUserIfNotExists'])
            ->getMock();

        $app = new Application([
            'debug' => true,
        ]);

        $app->register(new TwigServiceProvider(), [
            'twig.path' => __DIR__ . '/../../../views',
        ]);
        $app->extend('twig', function (\Twig_Environment $twig) {
            $twig->addGlobal('STATIC_URL', '/static');
            $twig->addGlobal('BOWER_PATH', '/static/bower_components');
            return $twig;
        });

        $app->register(new RoutingServiceProvider());
        $app->register(new AuthenticationServiceProvider(), [
            'auth.oauth2.clients' => function () {
                return [
                    AzureClient::PROVIDER_NAME => new MockOAuth2Client(true),
                ];
            },
        ]);

        $app->get('/home')
            ->bind('home');

        $app->get('/login', [$this->controller, 'loginPage'])
            ->bind('login');

        $app->get('/logout', [$this->controller, 'logout']);
        $app->get('/{auth_type}/authorize', [$this->controller, 'authorize'])
            ->value('auth_type', TestAuthenticator::AUTH_TYPE)
            ->bind('default_authorize');

        $app->get('/oauth2/{provider}/authorize', [$this->controller, 'authorizeWithOAuth2'])
            ->value('provider', AzureClient::PROVIDER_NAME)
            ->bind('oauth2_authorize');

        $app->get('/oauth2/callback', [$this->controller, 'callbackFromOAuth2']);

        $this->app = $app;
    }

    public function testLoginPage()
    {
        $return_url = '/some/return/url';

        $request = Request::create('/login?return_url=' . $return_url, 'GET', [], [
            'auth_type' => OAuth2Authenticator::AUTH_TYPE,
            'oauth2_provider' => AzureClient::PROVIDER_NAME,
        ]);

        $response = $this->app->handle($request);

        $oauth2_authorize_url = $this->app['url_generator']->generate('oauth2_authorize', [
            'provider' => AzureClient::PROVIDER_NAME,
        ]);

        $test_authorize_url = $this->app['url_generator']->generate('default_authorize', [
            'auth_type' => TestAuthenticator::AUTH_TYPE,
        ]);

        $this->assertContains('href=\'' . $oauth2_authorize_url . '?return_url=' . urlencode($return_url) . '\'', $response->getContent());
        $this->assertContains('href=\'' . $test_authorize_url . '?return_url=' . urlencode($return_url) . '\'', $response->getContent());
    }

    public function testLogout()
    {
        $request = Request::create('/logout', 'GET', [], [
            'auth_type' => OAuth2Authenticator::AUTH_TYPE,
            'oauth2_provider' => AzureClient::PROVIDER_NAME,
        ]);

        $response = $this->app->handle($request);

        $this->assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertEquals($this->app['url_generator']->generate('login'), $response->headers->get('location'));

        $session = $this->app['auth.session'];
        $this->assertNull($session->get(OAuth2Authenticator::KEY_AUTH_TYPE));
        $this->assertNull($session->get(OAuth2Authenticator::KEY_PROVIDER));
        $this->assertNull($session->get(OAuth2Authenticator::KEY_STATE));
        $this->assertNull($session->get(OAuth2Authenticator::KEY_RETURN_URL));
        $this->assertNull($session->get(OAuth2Authenticator::KEY_ACCESS_TOKEN));
        $this->assertNull($session->get(OAuth2Authenticator::KEY_REFRESH_TOKEN));
    }

    public function testAuthorize()
    {
        $return_url = '/some/return/url';

        $request = Request::create('/' . TestAuthenticator::AUTH_TYPE . '/authorize?return_url=' . $return_url, 'GET', [], [
            'auth_type' => TestAuthenticator::AUTH_TYPE,
        ]);

        $response = $this->app->handle($request);

        $this->assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertEquals($return_url, $response->headers->get('location'));

        $session = $this->app['auth.session'];
        $this->assertEquals($session->get(BaseAuthenticator::KEY_AUTH_TYPE), TestAuthenticator::AUTH_TYPE);
    }

    public function testAuthorizeWithOAuth2()
    {
        $scope = 'some_scope';
        $return_url = '/some/return/url';

        $request = Request::create('/oauth2/' . AzureClient::PROVIDER_NAME . '/authorize?scope=' . $scope . '&return_url=' . $return_url, 'GET', [], [
            'auth_type' => OAuth2Authenticator::AUTH_TYPE,
            'oauth2_provider' => AzureClient::PROVIDER_NAME,
        ]);

        $response = $this->app->handle($request);

        $session = $this->app['auth.session'];
        $this->assertNotNull($session->get(OAuth2Authenticator::KEY_STATE));
        $random_state = $session->get(OAuth2Authenticator::KEY_STATE);

        // authorization url created by MockOauth2Client
        $expected_authorize_url = 'authorization url with scope \'' . $scope . '\', and state \'' . $random_state . '\'';
        $this->assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertEquals($expected_authorize_url, $response->headers->get('location'));

        $this->assertEquals($session->get(BaseAuthenticator::KEY_AUTH_TYPE), OAuth2Authenticator::AUTH_TYPE);
        $this->assertEquals($session->get(OAuth2Authenticator::KEY_PROVIDER), AzureClient::PROVIDER_NAME);
        $this->assertEquals($session->get(OAuth2Authenticator::KEY_RETURN_URL), $return_url);
    }

    public function testCallbackFromOAuth2()
    {
        $code = 'test_code';
        $state = 'random_state';

        // The state stored in session should be matched with a state param passed by callback url
        $session = $this->app['auth.session'];
        $session->set(OAuth2Authenticator::KEY_STATE, $state);
        $session->set(OAuth2Authenticator::KEY_RETURN_URL, '/some/return/url');

        $request = Request::create('/oauth2/callback?code=' . $code . '&state=' . $state, 'GET', [], [
            'auth_type' => OAuth2Authenticator::AUTH_TYPE,
            'oauth2_provider' => AzureClient::PROVIDER_NAME,
        ]);

        $response = $this->app->handle($request);

        $this->assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertEquals('/some/return/url', $response->headers->get('location'));
    }

    public function testCallbackFromOAuth2WithWrongState()
    {
        $code = 'test_code';
        $state = 'random_state';

        // Set a state not matched with previous one
        $session = $this->app['auth.session'];
        $session->set(OAuth2Authenticator::KEY_STATE, 'wrong_state');
        $session->set(OAuth2Authenticator::KEY_RETURN_URL, '/some/return/url');

        $request = Request::create('/oauth2/callback?code=' . $code . '&state=' . $state, 'GET', [], [
            'auth_type' => OAuth2Authenticator::AUTH_TYPE,
            'oauth2_provider' => AzureClient::PROVIDER_NAME,
        ]);

        $response = $this->app->handle($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals('state is not matched', $response->getContent());
    }
}
