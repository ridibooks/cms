<?php

namespace Ridibooks\Cms\Lib;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class AzureOAuth2ServiceTest extends TestCase
{
    private $config = [
        'tenent' => 'ridicorp.com',
        'client_id' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
        'client_secret' => 'ffffffffffffffffffffffffffffffffffffffffffff',
        'resource' => 'https://graph.windows.net',
        'redirect_uri' => 'https://admin.ridibooks.com/login-azure',
        'api_version' => '2013-11-08',
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
        $azure = new AzureOAuth2Service($this->config);

        $endpoint = $azure->getAuthenticationEndPoint();
        $this->assertStringStartsWith('https://login.microsoftonline.com', $endpoint);
        $this->assertRegexp("/{$this->config['tenent']}/", $endpoint);
        $this->assertRegexp("/response_type=code/", $endpoint);
        $this->assertRegexp("/client_id={$this->config['client_id']}/", $endpoint);
        $this->assertRegexp("/resource=" . urlencode($this->config['resource']) . "/", $endpoint);
        $this->assertRegexp("/redirect_uri=" . urlencode($this->config['redirect_uri']) . "/", $endpoint);
    }

    public function testGetTokens()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode($this->token_response)),
        ]);
        $guzzle_handler = HandlerStack::create($mock);

        $azure = new AzureOAuth2Service($this->config, ['handler' => $guzzle_handler]);

        $tokens = $azure->getTokens('1234');
        $this->assertEquals($tokens['access'], $this->token_response['access_token']);
        $this->assertEquals($tokens['refresh'], $this->token_response['refresh_token']);
        $this->assertEquals($tokens['expires_on'], $this->token_response['expires_on']);
    }

    public function testRefreshToken()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode($this->token_response)),
        ]);
        $guzzle_handler = HandlerStack::create($mock);

        $azure = new AzureOAuth2Service($this->config, ['handler' => $guzzle_handler]);

        $tokens = $azure->refreshToken('1234');
        $this->assertEquals($tokens['access'], $this->token_response['access_token']);
        $this->assertEquals($tokens['refresh'], $this->token_response['refresh_token']);
        $this->assertEquals($tokens['expires_on'], $this->token_response['expires_on']);
    }
}
