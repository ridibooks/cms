<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthCookieStorage
{
    private $origin = [];
    private $modified = [];

    public function get(string $key)
    {
        $values = array_merge($this->origin, $this->modified);
        return $values[$key];
    }

    public function set(string $key, ?string $value)
    {
        $this->modified[$key] = $value;
    }

    public function clearAll()
    {
        foreach ($this->origin as $key => $value) {
            $this->modified[$key] = null;
        }
    }

    public function readCookie(Request $request, array $key_list)
    {
        $cookies = [];
        foreach ($key_list as $key) {
            $cookies[$key] = $request->cookies->get($key);
        }

        $this->origin = array_merge($this->origin, $cookies);
    }

    public function writeCookie(Response $response)
    {
        foreach ($this->modified as $key => $value) {
            if (empty($value)) {
                $response->headers->clearCookie($key);
            } else {
                $cookie = new Cookie($key, $value);
                $response->headers->setCookie($cookie);
            }
        }
    }
}
