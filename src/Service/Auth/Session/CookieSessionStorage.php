<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth\Session;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CookieSessionStorage implements SessionStorageInterface
{
    private $cookie_options;
    private $cookie_default;

    private $origin = [];
    private $modified = [];

    public function __construct(array $cookie_options, array $cookie_default = [])
    {
        $this->cookie_options = $cookie_options;
        $this->cookie_default = $cookie_default;

        foreach ($cookie_options as $key_name => $option) {
            $this->origin[$key_name] = null;
        }
    }

    public function get(string $key_name): ?string
    {
        $values = array_merge($this->origin, $this->modified);
        return $values[$key_name] ?? null;
    }

    public function set(string $key_name, ?string $value)
    {
        if (array_key_exists($key_name, $this->origin)) {
            $this->modified[$key_name] = $value;
        }
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
        foreach ($this->cookie_options as $key_name => $option) {
            $cookies[$key_name] = $request->cookies->get($option['key']);
        }

        $this->origin = array_merge($this->origin, $cookies);
    }

    public function writeCookie(Request $request, Response $response)
    {
        foreach ($this->modified as $key_name => $value) {
            $option = array_merge($this->cookie_options[$key_name], $this->cookie_default);

            $key = $option['key'];
            $path = $option['path'] ?? '/';
            $domain = $option['domain'] ?? null;
            $secure = $option['secure'] ?? false;
            $http_only = $option['http_only'] ?? true;

            if (empty($option['lifetime'])) {
                $expire = 0;
            } else {
                $expire = time() + $option['lifetime'];
            }

            if (empty($value)) {
                $response->headers->clearCookie($key, $path, $domain, $secure, $http_only);
            } else {
                $cookie = new Cookie($key, $value, $expire, $path, $domain, $secure);
                $response->headers->setCookie($cookie);
            }
        }
    }
}
