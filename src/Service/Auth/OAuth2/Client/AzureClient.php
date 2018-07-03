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
            'refresh_token' => $refresh_token,
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

        // For some users, azure ID and azure email is differnt. So, request mailNickname explicitly rather than parse id from email.
        // See https://app.asana.com/0/314089093619591/726274713560091
        $token_object = new AccessToken(['access_token' => $access_token, 'expires' => $token_claims['exp']], $this->azure);
        $user = $this->azure->get('me?api-version=2013-11-08', $token_object);

        return $user['mailNickname'];
    }
}
