<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth\Session;

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

        foreach ($cookie_keys as $key_name => $key) {
            $this->origin[$key_name] = null;
        }
    }

    public function get(string $key_name): ?string
    {
        $modified_values = array_map(function($properties) {
            return $properties['value'];
        }, $this->modified);

        $values = array_merge($this->origin, $modified_values);
        return $values[$key_name] ?? null;
    }

    public function set(string $key_name, ?string $value, ?array $options = [])
    {
        if (array_key_exists($key_name, $this->origin)) {
            $properties = [
                'value' => $value,
                'domain' => $options['domain'] ?? null,
                'path' => $options['path'] ?? '/',
                'expires_on' => $options['expires_on'] ?? 0,
                'secure' => $options['secure'] ?? false,
            ];
            $this->modified[$key_name] = $properties;
        }
    }

    public function clear(string $key_name, ?array $options = [])
    {
        self::set($key_name, null, [
            'domain' => $options['domain'] ?? null,
            'path' => $options['path'] ?? '/',
            'expires_on' => 1,
            'secure' => $options['secure'] ?? false,
        ]);
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
        foreach ($this->modified as $key_name => $properties) {
            $key = $this->cookie_keys[$key_name];
            if (empty($properties)) {
                $response->headers->clearCookie($key);
            } else {
                $cookie = new Cookie(
                    $key,
                    $properties['value'],
                    $properties['expires_on'] ?? 0,
                    $properties['path'] ?? '/',
                    $properties['domain'],
                    $properties['secure'] ?? false
                    );
                $response->headers->setCookie($cookie);
            }
        }
    }
}
