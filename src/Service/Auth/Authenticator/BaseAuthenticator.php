<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth\Authenticator;

use Ridibooks\Cms\Auth\LoginService;
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

        $user = $this->getUserInfo($credential);
        // TODO: Hack to inject admin id to CmsApplication
        LoginService::initialize('', '', $user['id']);

        return $user;
    }

    public function signOut(): ?string
    {
        $this->removeCredential();
        
        return $this->getSignOutUrl();
    }

    abstract public function createCredential(Request $request);

    abstract public function validateCredential($credentials);

    public function removeCredential()
    {
        $this->session->clearAll();
    }

    function getSignOutUrl()
    {
        return null;
    }

    abstract public function getUserInfo($credentials): array;
}
