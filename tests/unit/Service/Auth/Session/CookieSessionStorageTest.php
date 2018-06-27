<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Tests\Service\Auth\Session;

use PHPUnit\Framework\TestCase;
use Ridibooks\Cms\Service\Auth\Session\CookieSessionStorage;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CookieSessionStorageTest extends TestCase
{
    /** @var CookieSessionStorage $session */
    private $session;

    public function setUp()
    {
        $session = new CookieSessionStorage([
            'KEY_SET' => [
                'key' => 'key_set',
                'domain' => 'manual.domain.com',
                'lifetime' => 0,
            ],
            'KEY_NOT_SET' => [
                'key' => 'key_not_set'
            ],
        ], [
            'domain' => 'default.domain.com',
            'path' => '/default/path',
            'secure' => true,
        ]);

        $session->readCookie(Request::create('/default/path', 'GET', [], [
            'key_set' => 'some_value',
        ]));

        $this->session = $session;
    }

    public function testSessionOptions()
    {
        $this->session->set('KEY_SET', 'some_new_value');

        $request = Request::create('/default/path');
        $response = Response::create('some response');
        $this->session->writeCookie($request, $response);

        /** @var Cookie $cookie */
        $cookies = $response->headers->getCookies();
        $cookie = $cookies[0];

        // Assert using values in the option
        $this->assertEquals('key_set', $cookie->getname());
        $this->assertEquals('manual.domain.com', $cookie->getDomain());
        $this->assertEquals(0, $cookie->getExpiresTime());

        // Assert using default values
        $this->assertEquals('/default/path', $cookie->getPath());
        $this->assertEquals(true, $cookie->isSecure());

        $this->assertEquals('some_new_value', $cookie->getValue());
    }

    public function testGet()
    {
        $this->assertEquals('some_value', $this->session->get('KEY_SET'));
        $this->assertNull($this->session->get('KEY_NOT_SET'));
        $this->assertNull($this->session->get('KEY_NOT_AVAILABLE'));
    }

    public function testSet()
    {
        $this->session->set('KEY_SET', 'some_new_value');
        $this->assertEquals('some_new_value', $this->session->get('KEY_SET'));

        $this->session->set('KEY_NOT_SET', 'some_value2');
        $this->assertEquals('some_value2', $this->session->get('KEY_NOT_SET'));

        $this->session->get('KEY_NOT_AVAILABLE', 'some_value3');
        $this->assertNull($this->session->get('KEY_NOT_AVAILABLE'));
    }

    public function testClearAll()
    {
        $this->session->clearAll();
        $this->assertNull($this->session->get('KEY_SET'));
        $this->assertNull($this->session->get('KEY_NOT_SET'));
        $this->assertNull($this->session->get('KEY_NOT_AVAILABLE'));
    }

    public function testReadCookie()
    {
        $this->session->readCookie(Request::create('/default/path', 'GET', [], [
            'key_set' => 'some_special_value',
            'key_not_available' => 'some_value_ignored',
        ]));

        $this->assertEquals('some_special_value', $this->session->get('KEY_SET'));
        $this->assertNull($this->session->get('KEY_NOT_SET'));
    }

    public function testWriteCookie()
    {
        $this->session->set('KEY_SET', 'some_new_value');

        $request = Request::create('/default/path');
        $response = Response::create('some response');
        $this->session->writeCookie($request, $response);

        $cookies = $response->headers->getCookies();
        /** @var Cookie $cookie */
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === 'key_set') {
                $this->assertEquals('some_new_value', $cookie->getValue());
                $this->assertEquals('manual.domain.com', $cookie->getDomain());
                $this->assertEquals('/default/path', $cookie->getPath());
                $this->assertEquals(0, $cookie->getExpiresTime());
            }
        }
    }
}
