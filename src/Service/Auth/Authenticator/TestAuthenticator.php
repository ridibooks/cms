<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth\Authenticator;

use Ridibooks\Cms\Service\Auth\Session\SessionStorageInterface;
use Symfony\Component\HttpFoundation\Request;

class TestAuthenticator extends BaseAuthenticator
{
    const AUTH_TYPE = 'test';

    /** @var string $test_user */
    private $test_user_id;

    public function __construct(SessionStorageInterface $session, string $test_user_id)
    {
        parent::__construct(self::AUTH_TYPE, $session);
        $this->test_user_id = $test_user_id;
        if (empty($this->test_user_id)) {
            $this->test_user_id = 'admin';
        }
    }

    /**
     * @throws \Exception
     */
    public function createCredential(Request $request)
    {
        // TODO: Should be removed (backward compatibility)
        $this->session->set(OAuth2Authenticator::KEY_USER_ID, $this->test_user_id);
        $this->session->set(OAuth2Authenticator::KEY_ACCESS_TOKEN, 'test');

        return $this->test_user_id;
    }

    public function validateCredential($test_user_id)
    {
        // Do nothing
    }

    public function getUserInfo($test_user_id): array
    {
        return ['id' => $test_user_id];
    }

    public function removeCredential()
    {
        // TODO: Should be removed (backward compatibility)
        $this->session->set(OAuth2Authenticator::KEY_USER_ID, null);
        $this->session->set(OAuth2Authenticator::KEY_ACCESS_TOKEN, null);
    }
}
