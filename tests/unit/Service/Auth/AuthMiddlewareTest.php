<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Tests\Service\Auth;

use PHPUnit\Framework\TestCase;
use Ridibooks\Cms\Service\Auth\AuthenticationServiceProvider;
use Ridibooks\Cms\Service\Auth\Authenticator\TestAuthenticator;
use Ridibooks\Cms\Service\Auth\AuthMiddleware;
use Ridibooks\Cms\Tests\Mock\MockAuthenticator;
use Silex\Application;
use Silex\Provider\RoutingServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddlewareTest extends TestCase
{
    /** @var Application $app */
    private $app;

    public function setUp()
    {
        $app = new Application();

        $app->register(new RoutingServiceProvider());
        $app->register(new AuthenticationServiceProvider(), [
            'auth.enabled' => [
                TestAuthenticator::AUTH_TYPE,
            ],
            'auth.options' => [
                'test' => [
                    'test_user_id' => 'test_id',
                ],
            ],
        ]);

        $app->get('/login')->bind('login');
        $app->get('/some/resource', function () {
            return 'success';
        })->before(AuthMiddleware::authRequired());

        $this->app = $app;
    }

    public function testAuthRequiredAllowed()
    {
        $response = $this->app->handle(Request::create('/some/resource', 'GET', [], [
            'auth_type' => TestAuthenticator::AUTH_TYPE,
        ]));

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('success', $response->getContent());
    }

    public function testAuthRequiredNotSignedIn()
    {
        $response = $this->app->handle(Request::create('/some/resource', 'GET'));

        $this->assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertEquals('/login?return_url=' . urlencode('/some/resource'), $response->headers->get('location'));
    }

    public function testAuthRequiredWrongCredential()
    {
        $this->app['auth.authenticator'] = new MockAuthenticator(false); // Force a validation to fail

        $response = $this->app->handle(Request::create('/some/resource', 'GET'));

        $this->assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertEquals('/login?return_url=' . urlencode('/some/resource'), $response->headers->get('location'));
    }
}
