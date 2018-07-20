<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth\Authenticator;

use Ridibooks\Cms\Service\Auth\Session\SessionStorageInterface;
use Symfony\Component\HttpFoundation\Request;

class PasswordAuthenticator extends BaseAuthenticator
{
    const AUTH_TYPE = 'password';

    public function __construct(SessionStorageInterface $session)
    {
        parent::__construct(self::AUTH_TYPE, $session);
    }

    public function createCredential(Request $request)
    {
        // TODO: Remove default param
        $user_id = $request->get('user_id', 'test');
        $user_password = $request->get('user_password', 'test');

        // TODO: Should be removed (backward compatibility)
        $this->session->set(OAuth2Authenticator::KEY_USER_ID, $user_id);
        $this->session->set(OAuth2Authenticator::KEY_ACCESS_TOKEN, 'password');

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

    public function getUserInfo($credentials): array
    {
        return ['id' => $credentials['user_id']];
    }

    public function removeCredential()
    {
        // TODO: Should be removed (backward compatibility)
        $this->session->set(OAuth2Authenticator::KEY_USER_ID, null);
        $this->session->set(OAuth2Authenticator::KEY_ACCESS_TOKEN, null);
    }
}
