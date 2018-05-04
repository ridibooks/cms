<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth\Authenticator;

use Ridibooks\Cms\Auth\LoginService;
use Ridibooks\Cms\Service\Auth\Session\SessionStorageInterface;
use Symfony\Component\HttpFoundation\Request;

class TestAuthenticator extends BaseAuthenticator
{
    const AUTH_TYPE = 'test';

    const KEY_USER_ID = 'KEY_USER_ID';

    /** @var string $test_user */
    private $test_user_id;

    public function __construct(SessionStorageInterface $session, string $test_user_id)
    {
        parent::__construct(self::AUTH_TYPE, $session);
        $this->test_user_id = $test_user_id;
    }

    /**
     * @throws \Exception
     */
    public function createCredential(Request $request)
    {
        // TODO: Remove this
        $this->session->set(LoginService::ADMIN_ID_COOKIE_NAME, $this->test_user_id);
        $this->session->set(LoginService::TOKEN_COOKIE_NAME, 'test');
        return $this->test_user_id;
    }

    public function validateCredential($test_user_id)
    {
        // Do nothing
    }

    public function getUserId($test_user_id): string
    {
        return $test_user_id;
    }
}
