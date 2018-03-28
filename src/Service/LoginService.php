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

    const TEST_TOKEN_EXPIRES_SEC = 60 * 60; // 1 hour

    public static function handleTestLogin(string $return_url, string $test_id): Response
    {
        $login_expires_on = time() + self::REFRESH_TOKEN_EXPIRES_SEC;
        return self::createLoginResponse(
            $return_url,
            'test',
            'test',
            $login_expires_on,
            $test_id
        );
    }

    /**
     * @throws \Exception
     */
    public static function handleAzureLogin(string $return_url, string $code, AzureOAuth2Service $azure): Response
    {
        $tokens = $azure->getTokens($code);
        $access_token = $tokens['access'];
        $refresh_token = $tokens['refresh'];

        $resource = $azure->introspectToken($access_token);
        if (isset($resource['error']) || isset($resource['message'])) {
            throw new \Exception("[requestResource]\n {$resource['error']}: {$resource['message']}");
        }

        self::addUserIfNotExists($resource['user_id'], $resource['user_name']);

        $login_expires_on = time() + self::REFRESH_TOKEN_EXPIRES_SEC;
        return self::createLoginResponse($return_url, $access_token, $refresh_token, $login_expires_on, $resource['user_id']);
    }

    /**
     * @throws \Exception
     */
    private static function addUserIfNotExists(string $user_id, string $user_name)
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

    public static function createLoginResponse(string $return_url, string $access_token, string $refresh_token,
        int $login_expires_on, ?string $login_id = null): Response
    {
        $response = RedirectResponse::create($return_url);

        $is_secure = empty($_ENV['DEBUG']) ? true : false;
        $access_cookie = new Cookie(self::TOKEN_COOKIE_NAME, $access_token, $login_expires_on, '/', null, $is_secure);
        $refresh_cookie = new Cookie(self::REFRESH_COOKIE_NAME, $refresh_token, $login_expires_on, '/v2/token-refresh', null, $is_secure);
        $response->headers->setCookie($access_cookie);
        $response->headers->setCookie($refresh_cookie);

        if (isset($login_id)) {
            $login_id_cookie = new Cookie(self::ADMIN_ID_COOKIE_NAME, $login_id, $login_expires_on, '/', null, $is_secure);
            $response->headers->setCookie($login_id_cookie);
        }

        return $response;
    }

    public static function handleLogout(string $redirect_url): Response
    {
        return self::createLogoutResponse($redirect_url);
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

    /**
     * @throws \Exception
     */
    public static function refreshToken(string $return_url, $refresh_token, AzureOAuth2Service $azure): Response
    {
        if ($refresh_token === 'test') {
            $tokens = [
                'access' => 'test',
                'refresh' => 'test',
                'expires_on' => self::TEST_TOKEN_EXPIRES_SEC,
            ];
        } else {
            $tokens = $azure->refreshToken($refresh_token);
        }

        $access_token = $tokens['access'];
        $refresh_token = $tokens['refresh'];
        $login_expires_on = time() + self::REFRESH_TOKEN_EXPIRES_SEC;
        return self::createLoginResponse($return_url, $access_token, $refresh_token, $login_expires_on, null);
    }
}
