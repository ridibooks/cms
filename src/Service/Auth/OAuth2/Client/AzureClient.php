<?php

namespace Ridibooks\Cms\Service\Auth\OAuth2\Client;

use Ridibooks\Cms\Service\Auth\OAuth2\OAuth2Credential;
use TheNetworg\OAuth2\Client\Provider\Azure;
use TheNetworg\OAuth2\Client\Token\AccessToken;

class AzureClient implements OAuth2ClientInterface
{
    const PROVIDER_NAME = 'azure';

    /** @var Azure $azure */
    private $azure;
    private $redirect_path;
    private $redirect_uri;

    public function __construct(array $options)
    {
        $this->azure = new Azure($options);
        $this->azure->tenant = $options['tenent'];
        $this->azure->resource = $options['resource'];
        $this->redirect_path = $options['redirectPath'] ?? '';
        $this->redirect_uri = $options['redirectUri'] ?? '';
    }

    private function getRedirectUri()
    {
        // Create a dynamic redirect uri based on request domain.
        if (!empty($this->redirect_path)) {
            // TODO(devgrapher): Referring global variables should be avoided.
            $redirect_uri = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $this->redirect_path;
        } else {
            $redirect_uri = $this->redirect_uri;
        }

        return $redirect_uri;
    }

    public function getAuthorizationUrl(string $scope = null, string $state = null): string
    {
        return $this->azure->getAuthorizationUrl([
            'scope' => $scope,
            'state' => $state,
            'redirect_uri' => $this->getRedirectUri(),
        ]);
    }

    public function getTokenWithAuthorizationGrant(string $code): OAuth2Credential
    {
        /** @var AccessToken $access_token */
        $access_token = $this->azure->getAccessToken('authorization_code', [
            'code' => $code,
            'redirect_uri' => $this->getRedirectUri(),
        ]);

        return new OAuth2Credential(
            $access_token->getToken(),
            $access_token->getRefreshToken()
        );
    }

    public function getTokenWithRefreshGrant(string $refresh_token): OAuth2Credential
    {
        /** @var AccessToken $access_token */
        $access_token = $this->azure->getAccessToken('refresh_token', [
            'refresh_token' => $refresh_token,
            'redirect_uri' => $this->getRedirectUri(),
        ]);

        return new OAuth2Credential(
            $access_token->getToken(),
            $access_token->getRefreshToken()
        );
    }

    /**
     * @throws \Exception
     */
    public function validateToken(string $access_token)
    {
        $this->azure->validateAccessToken($access_token);
    }

    /**
     * @throws \RuntimeException
     */
    public function getResourceOwner(string $access_token)
    {
        $token_claims = $this->azure->validateAccessToken($access_token);

        $email = $token_claims['unique_name'];
        return str_replace('@ridi.com', '', $email);
    }
}
