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
        $user_id = $request->get('user_id');
        $user_password = $request->get('password');
        return [
            'user_id' => $user_id,
            'password' => $user_password,
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
    }
}
