<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth\Authenticator;

use Ridibooks\Cms\Auth\LoginService;
use Ridibooks\Cms\Service\Auth\Exception\NoCredentialException;
use Ridibooks\Cms\Service\Auth\OAuth2\Client\OAuth2ClientInterface;
use Ridibooks\Cms\Service\Auth\OAuth2\Exception\InvalidStateException;
use Ridibooks\Cms\Service\Auth\Storage\SessionStorageInterface;
use Symfony\Component\HttpFoundation\Request;

class OAuth2Authenticator extends BaseAuthenticator
{
    const AUTH_TYPE = 'oauth2';

    const KEY_PROVIDER = 'KEY_PROVIDER';
    const KEY_ACCESS_TOKEN = 'KEY_ACCESS_TOKEN';
    const KEY_REFRESH_TOKEN = 'KEY_REFRESH_TOKEN';
    const KEY_STATE = 'KEY_STATE';
    const KEY_RETURN_URL = 'KEY_RETURN_URL';

    private $clients;

    public function __construct(SessionStorageInterface $session, array $clients)
    {
        parent::__construct(self::AUTH_TYPE, $session);

        $this->clients = $clients;
    }

    public function getAuthorizationUrl(?string $scope): string
    {
        $state = $this->createRandomState();
        $this->session->set(self::KEY_STATE, $state);

        $client = $this->getOAuth2Client();
        return $client->getAuthorizationUrl($scope, $state);
    }

    /**
     * @throws \Exception
     */
    public function createCredential(Request $request)
    {
        $code = $request->get('code');
        $state = $request->get('state');
        if (!empty($code)) {
            return $this->createCredentialWithAuthorizationCode($code, $state);
        }

        $access_token = $this->session->get(self::KEY_ACCESS_TOKEN);
        if (empty($access_token)) {
            $refresh_token = $this->session->get(self::KEY_REFRESH_TOKEN);
            if (empty($refresh_token)) {
                throw new NoCredentialException('no token exist');
            } else {
                $access_token = $this->createCredentialWithRefreshToken($refresh_token);
            }
        }

        return $access_token;
    }

    private function createCredentialWithAuthorizationCode(string $code, string $state): string
    {
        $expected_state = $this->session->get(self::KEY_STATE);
        if ($state !== $expected_state) {
            throw new InvalidStateException('state is not matched');
        }

        $this->session->set(self::KEY_STATE, null);

        $client = $this->getOAuth2Client();
        $credential = $client->getTokenWithAuthorizationGrant($code);
        $this->session->set(self::KEY_ACCESS_TOKEN, $credential->access_token);
        $this->session->set(self::KEY_REFRESH_TOKEN, $credential->refresh_token);

        $this->session->set(LoginService::TOKEN_COOKIE_NAME, $credential->access_token); // TODO: Remove this

        return $credential->access_token;
    }

    private function createCredentialWithRefreshToken(string $refresh_token): string
    {
        $client = $this->getOAuth2Client();
        $credential = $client->getTokenWithRefreshGrant($refresh_token);

        $this->session->set(self::KEY_ACCESS_TOKEN, $credential->access_token);
        $this->session->set(self::KEY_REFRESH_TOKEN, $credential->refresh_token);
        return $credential->access_token;
    }

    public function validateCredential($access_token)
    {
        $client = $this->getOAuth2Client();
        $client->validateToken($access_token);
    }

    public function getUserId($access_token): string
    {
        $client = $this->getOAuth2Client();
        $user_id = $client->getResourceOwner($access_token);

        $this->session->set(LoginService::ADMIN_ID_COOKIE_NAME, $user_id); // TODO: Remove this

        return $user_id;
    }

    private function getOAuth2Client(): OAuth2ClientInterface
    {
        $provider = $this->getProvider();
        return $this->clients[$provider];
    }

    private function createRandomState()
    {
        return bin2hex(random_bytes(16));
    }

    public function getProvider(): ?string
    {
        return $this->session->get(self::KEY_PROVIDER);
    }

    public function setProvider(?string $provider)
    {
        $this->session->set(self::KEY_PROVIDER, $provider);
    }

    public function getReturnUrl(?string $default = null): ?string
    {
        return $this->session->get(self::KEY_RETURN_URL) ?? $default;
    }

    public function setReturnUrl(?string $return_url)
    {
        $this->session->set(self::KEY_RETURN_URL, $return_url);
    }
}
