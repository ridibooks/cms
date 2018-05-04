<?php

namespace Ridibooks\Cms\Tests;

use PHPUnit\Framework\TestCase;
use Ridibooks\Cms\Service\Auth\Authenticator\OAuth2Authenticator;
use Ridibooks\Cms\Service\Auth\OAuth2\Client\AzureClient;

class OAuth2AuthenticatorTest extends TestCase
{
    private $config = [
        'tenent' => 'ridicorp.com',
        'clientId' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
        'clientSecret' => 'ffffffffffffffffffffffffffffffffffffffffffff',
        'resource' => 'https://graph.windows.net',
        'redirectUri' => 'https://admin.ridibooks.com/login-azure',
    ];

    private $token_response = [
        "access_token" => "eyJ0eXAiOiJ",
        "token_type" => "Bearer",
        "expires_in" => "3600",
        "expires_on" => "1388444763",
        "resource" => "https://service.contoso.com/",
        "refresh_token" => "AwABAAAAvPM1KaPlrEqdFSBzjqfTGAxA",
        "scope" => "https%3A%2F%2Fgraph.microsoft.com%2Fmail.read",
    ];

    public function testGetAuthorizeEndPoint()
    {
        $session = new MockSession([
            OAuth2Authenticator::KEY_PROVIDER => AzureClient::PROVIDER_NAME,
        ]);

        $client = new AzureClient($this->config);

        $azure = new OAuth2Authenticator($session, [
            AzureClient::PROVIDER_NAME => $client,
        ]);

        $endpoint = $azure->getAuthorizationUrl(null);
        $this->assertStringStartsWith('https://login.microsoftonline.com', $endpoint);
        $this->assertRegexp("/{$this->config['tenent']}/", $endpoint);
        $this->assertRegexp("/response_type=code/", $endpoint);
        $this->assertRegexp("/client_id={$this->config['clientId']}/", $endpoint);
        $this->assertRegexp("/redirect_uri=" . urlencode($this->config['redirect_uri']) . "/", $endpoint);
    }
}
