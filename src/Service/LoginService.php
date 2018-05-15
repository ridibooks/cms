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
    const AUTH_SUBDOMAIN = 'auth.';

    const TEST_TOKEN_EXPIRES_SEC = 60 * 60; // 1 hour

    /**
     * @throws \Exception
     */
    public static function handleTestLogin(string $return_url, string $test_id): Response
    {
        self::addUserIfNotExists($test_id, $test_id);

        $refresh_expires_on = time() + self::REFRESH_TOKEN_EXPIRES_SEC;
        return self::createLoginResponse(
            $return_url,
            'test',
            'test',
            $refresh_expires_on,
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

        $access_expires_on = $tokens['expires_on'];
        return self::createLoginResponse($return_url, $access_token, $refresh_token, $access_expires_on, $resource['user_id']);
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

    private static function createLoginResponse(string $return_url, string $access_token, string $refresh_token,
        int $access_expires_on, string $login_id): Response
    {
        $is_secure = empty($_ENV['TEST_SECURED_DISABLE']) ? true : false;
        $request_domain = $_SERVER['HTTP_HOST'];
        $token_domain = str_replace(self::AUTH_SUBDOMAIN, '', $request_domain);

        $access_cookie = new Cookie(self::TOKEN_COOKIE_NAME, $access_token, $access_expires_on, '/', $token_domain, $is_secure);
        $refresh_cookie = new Cookie(self::REFRESH_COOKIE_NAME, $refresh_token, time() + self::REFRESH_TOKEN_EXPIRES_SEC, '/', $request_domain, $is_secure);
        $login_id_cookie = new Cookie(self::ADMIN_ID_COOKIE_NAME, $login_id, $access_expires_on, '/', $token_domain, $is_secure);

        $response = RedirectResponse::create($return_url);
        $response->headers->setCookie($access_cookie);
        $response->headers->setCookie($refresh_cookie);
        $response->headers->setCookie($login_id_cookie);
        return $response;
    }

    public static function handleLogout(string $redirect_url): Response
    {
        return self::createLogoutResponse($redirect_url);
    }

    private static function createLogoutResponse(string $return_url): Response
    {
        $is_secure = empty($_ENV['TEST_SECURED_DISABLE']) ? true : false;

        $request_domain = $_SERVER['HTTP_HOST'];
        $token_domain = str_replace(self::AUTH_SUBDOMAIN, '', $request_domain);

        $response = RedirectResponse::create($return_url);
        $response->headers->clearCookie(self::ADMIN_ID_COOKIE_NAME, '/', $token_domain, $is_secure);
        $response->headers->clearCookie(self::TOKEN_COOKIE_NAME, '/', $token_domain, $is_secure);
        $response->headers->clearCookie(self::REFRESH_COOKIE_NAME, '/', $request_domain, $is_secure);
        return $response;
    }

    public static function GetAdminID(): string
    {
        return $_COOKIE[self::ADMIN_ID_COOKIE_NAME] ?? '';
    }

    public static function getAccessToken(): string
    {
        return $_COOKIE[self::TOKEN_COOKIE_NAME] ?? '';
    }

    public static function getRefreshToken(): string
    {
        return $_COOKIE[self::REFRESH_COOKIE_NAME] ?? '';
    }

    /**
     * @throws \Exception
     */
    public static function refreshToken(string $return_url, string $refresh_token, AzureOAuth2Service $azure): Response
    {
        if ($refresh_token === 'test') {
            $tokens = [
                'access' => 'test',
                'refresh' => 'test',
                'expires_on' => time() + self::TEST_TOKEN_EXPIRES_SEC,
            ];
        } else {
            $tokens = $azure->refreshToken($refresh_token);
        }

        $access_token = $tokens['access'];
        $refresh_token = $tokens['refresh'];

        $resource = $azure->introspectToken($access_token);
        if (isset($resource['error']) || isset($resource['message'])) {
            throw new \Exception("[requestResource]\n {$resource['error']}: {$resource['message']}");
        }

        self::addUserIfNotExists($resource['user_id'], $resource['user_name']);

        $access_expires_on = $tokens['expires_on'];
        return self::createLoginResponse($return_url, $access_token, $refresh_token, $access_expires_on, $resource['user_id']);
    }

    public static function handleAuthorize(string $return_url, string $login_path, AzureOAuth2Service $azure, $logger): Response
    {
        $access_token = self::getAccessToken();
        if (!empty($access_token)) {
            $token_resource = $azure->introspectToken($access_token);
            if (isset($token_resource['error'])) {
                if ($logger) {
                    $logger->error(sprintf(
                        'Azure introspect error (%s): %s',
                        $token_resource['error'],
                        $token_resource['message'])
                    );
                }
            } else {
                return RedirectResponse::create($return_url);
            }
        }

        // If access token is invalid, try refresh token
        $refresh_token = self::getRefreshToken();
        if (!empty($refresh_token)) {
            return self::refreshToken($return_url, $refresh_token, $azure);
        }

        return RedirectResponse::create($login_path . '?return_url=' . urlencode($return_url));
    }
}
