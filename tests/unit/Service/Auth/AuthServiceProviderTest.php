<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Tests\Service\Auth;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Ridibooks\Cms\Service\Auth\AuthenticationServiceProvider;
use Ridibooks\Cms\Service\Auth\Authenticator\BaseAuthenticator;
use Ridibooks\Cms\Service\Auth\Authenticator\OAuth2Authenticator;
use Ridibooks\Cms\Service\Auth\Authenticator\TestAuthenticator;
use Ridibooks\Cms\Service\Auth\OAuth2\Client\AzureClient;

class AuthServiceProviderTest extends TestCase
{
    public function testRegister()
    {
        $app = new Container();

        $app->register(new AuthenticationServiceProvider(), [
            'auth.enabled' => [
                OAuth2Authenticator::AUTH_TYPE,
                TestAuthenticator::AUTH_TYPE,
            ],
            'auth.options' => [
                // oauth2 authenticator
                'oauth2' => [
                ],

                // password authenticator
                'password' => [
                ],

                // test authenticator
                'test' => [
                    'test_user_id' => $_ENV['TEST_ID'] ?? 'admin',
                ],
            ],
            'auth.oauth2.clients' => [
                AzureClient::PROVIDER_NAME => new AzureClient([
                    'tenent' => $_ENV['AZURE_TENENT'] ?? '',
                    'clientId' => $_ENV['AZURE_CLIENT_ID'] ?? '',
                    'clientSecret' => $_ENV['AZURE_CLIENT_SECRET'] ?? '',
                    'redirectUri' => $_ENV['AZURE_REDIRECT_URI'] ?? '',
                    'resource' => $_ENV['AZURE_RESOURCE'] ?? '',
                ]),
            ],
        ]);

        $this->assertArrayHasKey('auth.options', $app);
        $this->assertArrayHasKey('auth.enabled', $app);

        $this->assertArrayHasKey('auth.session', $app);
        $this->assertInstanceOf('\Ridibooks\Cms\Service\Auth\Session\CookieSessionStorage', $app['auth.session']);

        $this->assertArrayHasKey('auth.authenticator', $app);
        $this->assertNull($app['auth.authenticator']);
        $app['auth.session']->set(BaseAuthenticator::KEY_AUTH_TYPE, OAuth2Authenticator::AUTH_TYPE);
        $this->assertInstanceOf('\Ridibooks\Cms\Service\Auth\Authenticator\OAuth2Authenticator', $app['auth.authenticator']);
        $app['auth.session']->set(BaseAuthenticator::KEY_AUTH_TYPE, TestAuthenticator::AUTH_TYPE);
        $this->assertInstanceOf('\Ridibooks\Cms\Service\Auth\Authenticator\TestAuthenticator', $app['auth.authenticator']);

        $this->assertArrayHasKey('auth.oauth2.clients', $app);
        foreach ($app['auth.oauth2.clients'] as $provider => $client) {
            $this->assertInstanceOf('\Ridibooks\Cms\Service\Auth\OAuth2\Client\OAuth2ClientInterface', $client);
        }

        $this->assertArrayHasKey('auth.cookie.oauth2', $app);
        $this->assertArrayHasKey('auth.authenticator.oauth2', $app);
        $this->assertInstanceOf('\Ridibooks\Cms\Service\Auth\Authenticator\OAuth2Authenticator', $app['auth.authenticator.oauth2']);

        $this->assertArrayHasKey('auth.authenticator.test', $app);
        $this->assertInstanceOf('\Ridibooks\Cms\Service\Auth\Authenticator\TestAuthenticator', $app['auth.authenticator.test']);
    }
}
