<?php

namespace Ridibooks\Cms\Service\Auth\OAuth2;

use TheNetworg\OAuth2\Client\Provider\Azure;
use TheNetworg\OAuth2\Client\Token\AccessToken;

class AzureClient implements OAuth2ClientInterface
{
    /** @var Azure $azure */
    private $azure;

    public function __construct(array $options)
    {
        $this->azure = new Azure($options);
        $this->azure->tenant = $options['tenent'];
        $this->azure->resource = $options['resource'];
    }

    public function getAuthorizationUrl(string $scope = null, string $state = null): string
    {
        return $this->azure->getAuthorizationUrl([
            'scope' => $scope,
            'state' => $state,
        ]);
    }

    public function getTokenWithAuthorizationGrant(string $code): OAuth2Credential
    {
        /** @var AccessToken $access_token */
        $access_token = $this->azure->getAccessToken('authorization_code', [
            'code' => $code,
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
            'refresh_token' => $refresh_token
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

    public function getResourceOwner(string $access_token)
    {
        $token_claims = $this->azure->validateAccessToken($access_token);

        $email = $token_claims['unique_name'];
        return str_replace('@ridi.com', '', $email);
    }
}
