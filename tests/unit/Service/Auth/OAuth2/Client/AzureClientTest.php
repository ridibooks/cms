<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth\OAuth2\Client;

use PHPUnit\Framework\TestCase;
use Ridibooks\Cms\Service\Auth\OAuth2\Client\AzureClient;

class AzureClientTest extends TestCase
{
    public function testGetAuthorizationUrlUsesRequestDomainIfRedirectPathSet()
    {
        $_SERVER[REQUEST_SCHEME] = 'http';
        $_SERVER[HTTP_HOST] = 'test.domain.com';

        $client = new AzureClient([
            'tenent' => 'tenent',
            'clientId' => 'clientId',
            'clientSecret' => 'clientSecret',
            'redirectUri' => 'domain.com/auth',
            'redirectPath' => '/authpath',
            'resource' => 'resource',
        ]);

        $expected = urlencode('http://test.domain.com/authpath');
        $this->assertRegExp("/redirect_uri=${expected}/", $client->getAuthorizationUrl());
    }
}
