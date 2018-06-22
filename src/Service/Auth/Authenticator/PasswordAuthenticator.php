<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth\Authenticator;

use Ridibooks\Cms\Service\Auth\Session\SessionStorageInterface;
use Symfony\Component\HttpFoundation\Request;

class PasswordAuthenticator extends BaseAuthenticator
{
    const AUTH_TYPE = 'password';

    public function __construct(SessionStorageInterface $session, ?array $options = [])
    {
        parent::__construct(self::AUTH_TYPE, $session, $options);
    }

    public function createCredential(Request $request)
    {
        // TODO: Remove default param
        $user_id = $request->get('user_id', 'test');
        $user_password = $request->get('user_password', 'test');

        // TODO: Should be removed (backward compatibility)
        $this->session->set(OAuth2Authenticator::KEY_USER_ID, $user_id, $this->options['session.policy']['service']);
        $this->session->set(OAuth2Authenticator::KEY_ACCESS_TOKEN, 'password', $this->options['session.policy']['service']);

        return [
            'user_id' => $user_id,
            'user_password' => $user_password,
        ];
    }

    public function validateCredential($credential)
    {
        // TODO: check user_id and password pair
        $credential['user_id'];
        $credential['user_password'];
    }

    public function getUserId($credentials): string
    {
        return $credentials['user_id'];
    }

    public function removeCredential()
    {
        // TODO: Should be removed (backward compatibility)
        $this->session->clear(OAuth2Authenticator::KEY_USER_ID, $this->options['session.policy']['service']);
        $this->session->clear(OAuth2Authenticator::KEY_ACCESS_TOKEN, $this->options['session.policy']['service']);
    }
}
