<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth\Authenticator;

use Ridibooks\Cms\Service\Auth\Exception\NoCredentialException;
use Ridibooks\Cms\Service\Auth\Session\SessionStorageInterface;
use Ridibooks\Cms\Service\Auth\OAuth2\Exception\InvalidCredentialException;
use Symfony\Component\HttpFoundation\Request;

class CFAuthenticator extends BaseAuthenticator
{
    const AUTH_TYPE = 'cloudflare';
    const KEY_CF_TOKEN = 'CF_Authorization';

    public function __construct(SessionStorageInterface $session)
    {
        parent::__construct(self::AUTH_TYPE, $session);
    }

    /**
     * @throws \Exception
     */
    public function createCredential(Request $request)
    {
        $jwt = $this->session->get(self::KEY_CF_TOKEN);
        if (empty($jwt)) {
            throw new NoCredentialException('No cloudflare token exists');
        }
        $credential = $this->decodeCFToken($jwt, $_ENV["CMS_HOST"] ?? $request->getHost());
        $user = $this->getUserInfo($credential);
        $this->session->set(OAuth2Authenticator::KEY_USER_ID, $user["id"]);
        $this->session->set(OAuth2Authenticator::KEY_ACCESS_TOKEN, 'cloudflare');

        return $credential;
    }

    public function validateCredential($credential)
    {
        // Do nothing
    }

    public function getUserInfo($credential): array
    {
        $id = explode('@', $credential->email)[0];
        
        return ['id' => $id];
    }

    public function removeCredential()
    {
        $this->session->set(OAuth2Authenticator::KEY_USER_ID, null);
        $this->session->set(OAuth2Authenticator::KEY_ACCESS_TOKEN, null);
        $this->session->set(self::KEY_CF_TOKEN, null);
    }

    private function decodeCFToken(string $payload, string $host)
    {
        $validator = new CFJwtValidator();
        $key = $validator->getPublicKey($host);
        $decoded = $validator->decodeJwt($payload, $key);

        return $decoded;
    }
}
