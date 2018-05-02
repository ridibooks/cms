<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth\Authenticator;

use Ridibooks\Cms\Auth\LoginService;
use Ridibooks\Cms\Service\Auth\Exception\NoCredentialException;
use Ridibooks\Cms\Service\Auth\OAuth2\Client\OAuth2ClientInterface;
use Ridibooks\Cms\Service\Auth\OAuth2\Exception\InvalidStateException;
use Ridibooks\Cms\Service\Auth\Storage\AuthCookieStorage;
use Symfony\Component\HttpFoundation\Request;

class OAuth2Authenticator extends BaseAuthenticator
{
    const KEY_PROVIDER = 'oauth2_provider';
    const KEY_ACCESS_TOKEN = 'oauth2_access_token';
    const KEY_REFRESH_TOKEN = 'oauth2_refresh_token';
    const KEY_STATE = 'oauth2_state';
    const KEY_RETURN_URL = 'oauth2_return_url';

    private $clients;

    public function __construct(array $clients, AuthCookieStorage $storage)
    {
        parent::__construct($storage);

        $this->clients = $clients;
    }

    public function readCookieList(): array
    {
        return [
            self::KEY_PROVIDER,
            self::KEY_ACCESS_TOKEN,
            self::KEY_REFRESH_TOKEN,
            self::KEY_STATE,
            self::KEY_RETURN_URL,

            // TODO: Remove this
            LoginService::TOKEN_COOKIE_NAME,
            LoginService::ADMIN_ID_COOKIE_NAME
        ];
    }

    public function getAuthorizationUrl(?string $scope): string
    {
        $state = $this->createRandomState();
        $this->setState($state);

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

        $access_token = $this->getAccessToken();
        if (empty($access_token)) {
            $refresh_token = $this->getRefreshToken();
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
        $expected_state = $this->getState();
        if ($state !== $expected_state) {
            throw new InvalidStateException('state is not matched');
        }

        $this->setState(null);

        $client = $this->getOAuth2Client();
        $credential = $client->getTokenWithAuthorizationGrant($code);
        $this->setAccessToken($credential->access_token);
        $this->setRefreshToken($credential->refresh_token);
        return $credential->access_token;
    }

    private function createCredentialWithRefreshToken(string $refresh_token): string
    {
        $client = $this->getOAuth2Client();
        $credential = $client->getTokenWithRefreshGrant($refresh_token);

        $this->setAccessToken($credential->access_token);
        $this->setRefreshToken($credential->refresh_token);
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
        $this->storage->set(LoginService::ADMIN_ID_COOKIE_NAME, $user_id); // TODO: Remove this
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
        return $this->storage->get(self::KEY_PROVIDER);
    }

    public function setProvider(?string $provider)
    {
        $this->storage->set(self::KEY_PROVIDER, $provider);
    }

    public function getAccessToken(): ?string
    {
        return $this->storage->get(self::KEY_ACCESS_TOKEN);
    }

    public function setAccessToken(?string $access_token)
    {
        $this->storage->set(self::KEY_ACCESS_TOKEN, $access_token);
        $this->storage->set(LoginService::TOKEN_COOKIE_NAME, $access_token); // TODO: Remove this
    }

    public function getRefreshToken(): ?string
    {
        return $this->storage->get(self::KEY_REFRESH_TOKEN);
    }

    public function setRefreshToken(?string $refresh_token)
    {
        $this->storage->set(self::KEY_REFRESH_TOKEN, $refresh_token);
    }

    public function getState(): ?string
    {
        return $this->storage->get(self::KEY_STATE);
    }

    public function setState(?string $state)
    {
        $this->storage->set(self::KEY_STATE, $state);
    }

    public function getReturnUrl(?string $default = null): ?string
    {
        return $this->storage->get(self::KEY_RETURN_URL) ?? $default;
    }

    public function setReturnUrl(?string $return_url)
    {
        $this->storage->set(self::KEY_RETURN_URL, $return_url);
    }
}
