<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth\Storage;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CookieSessionStorage implements SessionStorageInterface
{
    private $cookie_keys;

    private $origin = [];
    private $modified = [];

    public function __construct(array $cookie_keys)
    {
        $this->cookie_keys = $cookie_keys;
    }

    public function get(string $key_name): ?string
    {
        $values = array_merge($this->origin, $this->modified);
        return $values[$key_name];
    }

    public function set(string $key_name, ?string $value)
    {
        $this->modified[$key_name] = $value;
    }

    public function clearAll()
    {
        foreach ($this->origin as $key_name => $value) {
            $this->modified[$key_name] = null;
        }
    }

    public function readCookie(Request $request)
    {
        $cookies = [];
        foreach ($this->cookie_keys as $key_name => $key) {
            $cookies[$key_name] = $request->cookies->get($key);
        }

        $this->origin = array_merge($this->origin, $cookies);
    }

    public function writeCookie(Request $request, Response $response)
    {
        foreach ($this->modified as $key_name => $value) {
            $key = $this->cookie_keys[$key_name];
            if (empty($value)) {
                $response->headers->clearCookie($key);
            } else {
                $cookie = new Cookie($key, $value);
                $response->headers->setCookie($cookie);
            }
        }
    }
}
