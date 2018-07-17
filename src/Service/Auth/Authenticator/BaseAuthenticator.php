<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth\Authenticator;

use Ridibooks\Cms\Service\Auth\Session\SessionStorageInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class BaseAuthenticator
{
    const KEY_AUTH_TYPE = 'KEY_AUTH_TYPE';

    private $auth_type;

    protected $session;
    protected $options;

    public function __construct(string $auth_type, SessionStorageInterface $session)
    {
        $this->auth_type = $auth_type;
        $this->session = $session;
    }

    public function signIn(Request $request): array
    {
        $credential = $this->createCredential($request);
        $this->validateCredential($credential);

        // This is necessary to remember which type of authenticator was used.
        $this->session->set(self::KEY_AUTH_TYPE, $this->auth_type);

        return $this->getUserInfo($credential);
    }

    public function signOut()
    {
        $this->removeCredential();
    }

    abstract public function createCredential(Request $request);

    abstract public function validateCredential($credentials);

    public function removeCredential()
    {
        $this->session->clearAll();
    }

    abstract public function getUserInfo($credentials): array;
}
