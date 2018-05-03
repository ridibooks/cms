<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth\Authenticator;

use Ridibooks\Cms\Service\Auth\Storage\SessionStorageInterface;
use Symfony\Component\HttpFoundation\Request;

class TestAuthenticator
{
    const KEY_USER_ID = 'test_user_id';

    /** @var string $test_user */
    private $test_user_id;

    public function __construct(string $test_user_id, SessionStorageInterface $session)
    {
        $this->test_user_id = $test_user_id;
    }

    public function readCookieList(): array
    {
        return [
            self::KEY_USER_ID,
        ];
    }

    /**
     * @throws \Exception
     */
    public function createCredential(Request $request)
    {
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

    public function getTestUserId(): string
    {
        return $this->session->get(self::KEY_USER_ID);
    }

    public function setTestUserId(?string $test_user_id)
    {
        $this->session->set(self::KEY_USER_ID, $test_user_id);
    }
}
