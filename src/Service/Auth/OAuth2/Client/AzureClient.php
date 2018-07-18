<?php

namespace Ridibooks\Cms\Service\Auth\OAuth2\Client;

use InvalidArgumentException;
use Ridibooks\Cms\Service\Auth\OAuth2\Exception\OAuth2Exception;
use Ridibooks\Cms\Service\Auth\OAuth2\OAuth2Credential;
use TheNetworg\OAuth2\Client\Provider\Azure;
use TheNetworg\OAuth2\Client\Token\AccessToken;
use UnexpectedValueException;

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
     * @throws OAuth2Exception
     */
    public function validateToken(string $access_token)
    {
        try {
            $token_claims = $this->azure->validateAccessToken($access_token);
        } catch (\RuntimeException
            | InvalidArgumentException
            | UnexpectedValueException
            | \Firebase\JWT\ExpiredException $e) {
            throw new OAuth2Exception($e->getMessage());
        }

        return $token_claims;
    }

    /**
     * @throws \RuntimeException
     * @throws OAuth2Exception
     */
    public function introspectResourceOwner(string $access_token): array
    {
        $token_claims = $this->validateToken($access_token);

        // For some users, azure ID and azure email is differnt. So, request mailNickname explicitly rather than parse id from email.
        // See https://app.asana.com/0/314089093619591/726274713560091
        $token_object = new AccessToken(['access_token' => $access_token, 'expires' => $token_claims['exp']], $this->azure);
        $user = $this->azure->get('me?api-version=2013-11-08', $token_object);
        if (empty($user['mailNickname'])) {
            throw new \RuntimeException('Fail to get user info : ' . var_export($user, true));
        }

        return [
            'id' => $user['mailNickname'],
            'name' => $token_claims['name'],
            'email' => $token_claims['unique_name'],
        ];
    }
}
