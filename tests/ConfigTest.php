<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testGetAzureRedirectUri()
    {
        $_SERVER['REQUEST_SCHEME'] = 'http';
        $_SERVER['HTTP_HOST'] = 'test.domain.com';
        $_ENV['AZURE_REDIRECT_URI'] = 'https://domain.com/authpath';

        $config = require __DIR__ . '/../config/config.php';

        $this->assertEquals('https://domain.com/authpath', $_ENV['AZURE_REDIRECT_URI']);
    }

    public function testBuildAzureRedirectUrlIfRedirectPathSet()
    {
        $_SERVER['REQUEST_SCHEME'] = 'http';
        $_SERVER['HTTP_HOST'] = 'test.domain.com';
        $_ENV['AZURE_REDIRECT_PATH'] = '/authpath';
        $_ENV['AZURE_REDIRECT_URI'] = null;

        $this->assertEmpty($_ENV['AZURE_REDIRECT_URI']);

        $config = require __DIR__ . '/../config/config.php';

        $this->assertEquals('http://test.domain.com/authpath', $_ENV['AZURE_REDIRECT_URI']);
    }
}
