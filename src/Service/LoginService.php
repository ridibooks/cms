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
    const REFRESH_TOKEN_EXPIRES_SEC = 60 * 60 * 24 * 30; // 30 days

    /**
     * @throws Exception
     */
    public static function login(string $user_id, string $user_name)
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

    public static function handleTestLogin(string $return_url, string $test_id): Response
    {
        $tokens = [
            'access' => 'test',
            'refresh' => 'test',
            'expires_on' => 60 * 60 * 24 * 30, // 30 days
        ];

        return self::createLoginResponse($return_url, $tokens, $test_id);
    }

    /**
     * @throws Exception
     */
    public static function handleAzureLogin(string $return_url, string $code,  AzureOAuth2Service $azure): Response
    {
        $tokens = $azure->getTokens($code);
        $resource = $azure->introspectToken($tokens['access']);
        if (isset($resource['error']) || isset($resource['message'])) {
            throw new \Exception("[requestResource]\n {$resource['error']}: {$resource['message']}");
        }

        self::login($resource['user_id'], $resource['user_name']);

        return self::createLoginResponse($return_url, $tokens, $resource['user_id']);
    }

    public static function createLoginResponse(string $return_url, array $tokens, ?string $login_id = null): Response
    {
        $response = RedirectResponse::create($return_url);
        $cookies = self::createLoginCookies($tokens['access'], $tokens['refresh'], $tokens['expires_on'], $login_id);
        foreach($cookies as $cookie) {
            $response->headers->setCookie($cookie);
        }
        return $response;
    }

    public static function handleLogout(string $redirect_url): Response
    {
        return self::createLogoutResponse($redirect_url);
    }

    private static function createLoginCookies(string $token, string $refresh_token, string $expires_on, ?string $login_id = null): array
    {
        $refresh_token_expires_on = time() + self::REFRESH_TOKEN_EXPIRES_SEC;
        $secure = empty($_ENV['DEBUG']) ? true : false;
        $token_cookie = new Cookie(self::TOKEN_COOKIE_NAME, $token, $expires_on, '/', null, $secure, true);
        $refresh_cookie = new Cookie(self::REFRESH_COOKIE_NAME, $refresh_token, $refresh_token_expires_on, '/v2/token-refresh', null, $secure, true);

        $login_id_cookie = null;
        if (isset($login_id)) {
            $login_id_cookie = new Cookie(self::ADMIN_ID_COOKIE_NAME, $login_id, $expires_on, '/', null, $secure, true);
        }

        return array_filter([$token_cookie, $refresh_cookie, $login_id_cookie]);
    }

    private static function createLogoutResponse(string $return_url): Response
    {
        $response = RedirectResponse::create($return_url);
        $response->headers->clearCookie(self::ADMIN_ID_COOKIE_NAME);
        $response->headers->clearCookie(self::TOKEN_COOKIE_NAME);
        $response->headers->clearCookie(self::REFRESH_COOKIE_NAME);
        return $response;
    }

    public static function GetAdminID(): string
    {
        return $_COOKIE[self::ADMIN_ID_COOKIE_NAME];
    }

    public static function refreshToken(string $return_url, $refresh_token, AzureOAuth2Service $azure): Response
    {
        $tokens = $azure->refreshToken($refresh_token);

        return self::createLoginResponse($return_url, $tokens);
    }
}
