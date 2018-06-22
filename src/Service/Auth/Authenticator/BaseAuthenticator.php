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

    public function __construct(string $auth_type, SessionStorageInterface $session, ?array $options = [])
    {
        $this->auth_type = $auth_type;
        $this->session = $session;
        $this->options = $options;
        if (!isset($this->options['session.policy'])) {
            $this->options['session.policy'] = [
                'service' => [],
                'auth' => [],
            ];
        }
    }

    public function signIn(Request $request): string
    {
        $credential = $this->createCredential($request);
        $this->validateCredential($credential);

        // This is necessary to remember which type of authenticator was used.
        $this->session->set(self::KEY_AUTH_TYPE, $this->auth_type, $this->options['session.policy']['service']);

        return $this->getUserId($credential);
    }

    public function signOut()
    {
        $this->removeCredential();

        $this->session->clear(self::KEY_AUTH_TYPE, $this->options['session.policy']['service']);
    }

    abstract public function createCredential(Request $request);

    abstract public function validateCredential($credentials);

    abstract public function removeCredential();

    abstract public function getUserId($credentials): string;
}
