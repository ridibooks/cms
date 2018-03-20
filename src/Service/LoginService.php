<?php

namespace Ridibooks\Cms\Service;

use Ridibooks\Cms\Lib\AzureOAuth2Service;
use Ridibooks\Cms\Thrift\ThriftService;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class LoginService
{
    const TOKEN_COOKIE_NAME = 'cms-token';
    const REFRESH_COOKIE_NAME = 'cms-refresh';
    const ADMIN_ID_COOKIE_NAME = 'admin-id';
    const REFRESH_TOKEN_EXPIRES_SEC = 60 * 60 * 24 * 7; // 7 days

    public static function login($user_id, $user_name)
    {
        $user_service = new AdminUserService();
        $user = $user_service->getUser($user_id);
        $user = ThriftService::convertUserToArray($user);
        if (!$user || !$user['id']) {
            $user_service->addNewUser($user_id, $user_name, '');
        } elseif ($user['is_use'] != '1') {
            throw new \Exception('사용이 금지된 계정입니다. 관리자에게 문의하세요.');
        }
    }

    public static function handleTestLogin($return_url, $test_id): Response
    {
        return self::createLoginResponse($return_url, $test_id, 'test');
    }

    public static function handleAzureLogin(string $return_url, string $code, $azure_config): Response
    {
        $tokens = AzureOAuth2Service::getTokens($code, $azure_config);
        $resource = AzureOAuth2Service::introspectToken($tokens['access'], $azure_config);
        if (isset($resource['error']) || isset($resource['message'])) {
            throw new \Exception("[requestResource]\n {$resource['error']}: {$resource['message']}");
        }

        self::login($resource['user_id'], $resource['user_name']);

        return self::createLoginResponse($return_url, $resource['user_id'], $tokens['access'], $tokens['refresh'], $tokens['expires_on']);
    }

    public static function handleLogout(string $redirect_url): Response
    {
        return self::createLogoutResponse($redirect_url);
    }

    private static function createLoginResponse(string $return_url, string $login_id, string $token, string $refresh, int $expires_on): Response
    {
        $refresh_token_expires_on = time() + self::REFRESH_TOKEN_EXPIRES_SEC;
        $secure = empty($_ENV['DEBUG']) ? true : false;
        $login_id_cookie = new Cookie(self::ADMIN_ID_COOKIE_NAME, $login_id, $expires_on, '/', null, $secure, true);
        $token_cookie = new Cookie(self::TOKEN_COOKIE_NAME, $token, $expires_on, '/', null, $secure, true);
        $refresh_cookie = new Cookie(self::REFRESH_COOKIE_NAME, $refresh, $refresh_token_expires_on, '/token-refresh', null, $secure, true);

        $response = RedirectResponse::create($return_url);
        $response->headers->setCookie($login_id_cookie);
        $response->headers->setCookie($token_cookie);
        $response->headers->setCookie($refresh_cookie);
        return $response;
    }

    private static function createLogoutResponse(string $redirect_url): Response
    {
        $response = RedirectResponse::create($redirect_url);
        $response->headers->clearCookie(self::ADMIN_ID_COOKIE_NAME);
        $response->headers->clearCookie(self::TOKEN_COOKIE_NAME);
        return $response;
    }

    public static function GetAdminID(): string
    {
        return $_COOKIE[self::ADMIN_ID_COOKIE_NAME];
    }
}
