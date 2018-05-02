<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth\Authenticator;

use Ridibooks\Cms\Service\Auth\Storage\AuthCookieStorage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseAuthenticator implements AuthenticatorInterface
{
    const KEY_AUTH = 'auth_type';

    protected $storage;

    public function __construct(AuthCookieStorage $storage)
    {
        $this->storage = $storage;
    }

    public function getAuthType(): ?string
    {
        return $this->storage->get(self::KEY_AUTH);
    }

    public function setAuthType(?string $auth)
    {
        $this->storage->set(self::KEY_AUTH, $auth);
    }

    public function readCookieList(): array
    {
        return [];
    }

    public function readCookie(Request $request)
    {
        $cookie_key_list = $this->readCookieList();
        $cookie_key_list[] = self::KEY_AUTH;
        $this->storage->readCookie($request, $cookie_key_list);
    }

    public function writeCookie(Request $request, Response $response)
    {
        $this->storage->writeCookie($response);
    }

    abstract public function createCredential(Request $request);

    abstract public function validateCredential($credentials);

    public function removeCredential()
    {
        $this->storage->clearAll();
    }

    abstract public function getUserId($credentials): string;
}
