<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Tests\Mock;

use Ridibooks\Cms\Service\Auth\Exception\InvalidCredentialException;
use Ridibooks\Cms\Service\Auth\OAuth2\Client\OAuth2ClientInterface;
use Ridibooks\Cms\Service\Auth\OAuth2\OAuth2Credential;

class MockOAuth2Client implements OAuth2ClientInterface
{
    private $allows_validation = false;

    public function __construct(bool $allows_validation)
    {
        $this->allows_validation = $allows_validation;
    }

    public function getAuthorizationUrl(string $scope = null, string $state = null): string
    {
        return self::getMockAuthorizationUrl($scope, $state);
    }

    public function getTokenWithAuthorizationGrant(string $code): OAuth2Credential
    {
        return new OAuth2Credential(
            self::getMockAccessTokenWithAuthorizationGrant($code),
            self::getMockRefreshTokenWithAuthorizationGrant($code)
        );
    }

    public function getTokenWithRefreshGrant(string $refresh_token): OAuth2Credential
    {
        return new OAuth2Credential(
            self::getMockAccessTokenWithRefreshGrant($refresh_token),
            self::getMockRefreshTokenWithRefreshGrant($refresh_token)
        );
    }

    /** @throws InvalidCredentialException */
    public function validateToken(string $access_token)
    {
        if (!$this->allows_validation) {
            throw new InvalidCredentialException('validateToken failed');
        }
    }

    public function introspectResourceOwner(string $access_token): array
    {
        return self::introspectMockResourceOwner($access_token);
    }

    public static function getMockAuthorizationUrl(string $scope = null, string $state = null): string
    {
        return 'authorization url with scope \'' . $scope . '\', and state \'' . $state . '\'';
    }

    public static function getMockAccessTokenWithAuthorizationGrant(string $code)
    {
        return 'access_token from code \'' . $code . '\'';
    }

    public static function getMockRefreshTokenWithAuthorizationGrant(string $code)
    {
        return 'refresh_token from code \'' . $code . '\'';
    }

    public static function getMockAccessTokenWithRefreshGrant(string $refresh_token)
    {
        return 'access_token from refresh_token \'' . $refresh_token . '\'';
    }

    public static function getMockRefreshTokenWithRefreshGrant(string $refresh_token)
    {
        return 'refresh_token from refresh_token \'' . $refresh_token . '\'';
    }

    public static function introspectMockResourceOwner(string $access_token)
    {
        return ['id' => 'test'];
    }
}
