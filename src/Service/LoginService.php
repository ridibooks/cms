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
    const ADMIN_ID_COOKIE_NAME = 'admin-id';
    const TOKEN_EXPIRES_SEC = 60 * 60 * 12; // 12hours

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
        $response = RedirectResponse::create($return_url);
        self::setLoginIdCookie($response, $test_id, false);
        self::setTokenCookie($response, 'test', false);

        return $response;
    }

    public static function handleAzureLogin($return_url, $code, $azure_config): Response
    {
        $token = AzureOAuth2Service::getAccessToken($code, $azure_config);
        $resource = AzureOAuth2Service::introspectToken($token, $azure_config);
        if (isset($resource['error']) || isset($resource['message'])) {
            throw new \Exception("[requestResource]\n {$resource['error']}: {$resource['message']}");
        }

        self::login($resource['user_id'], $resource['user_name']);

        $response = RedirectResponse::create($return_url);
        self::setLoginIdCookie($response, $resource['user_id'], true);
        self::setTokenCookie($response, $token, true);

        return $response;
    }

    public static function getLoginPageUrl($login_endpoint, $callback_path, $return_path)
    {
        $scheme = isset($_SERVER['HTTPS']) ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        if ($callback_path[0] != '/') {
            $callback_path = '/' . $callback_path;
        }
        if ($return_path[0] != '/') {
            $return_path = '/' . $return_path;
        }
        $callback_path = $scheme . '://' . $host . $callback_path;
        return $login_endpoint . '?callback=' . $callback_path . '&return_url=' . $return_path;
    }

    private static function setTokenCookie(Response $response, $token, $secure)
    {
        $response->headers->setCookie(
            new Cookie(self::TOKEN_COOKIE_NAME, $token, time() + self::TOKEN_EXPIRES_SEC, '/', null, $secure)
        );
    }

    private static function setLoginIdCookie(Response $response, $login_id, $secure)
    {
        $response->headers->setCookie(
            new Cookie(self::ADMIN_ID_COOKIE_NAME, $login_id, time() + self::TOKEN_EXPIRES_SEC, '/', null, $secure)
        );
    }

    public static function clearLoginCookies(Response $response)
    {
        $response->headers->clearCookie(self::ADMIN_ID_COOKIE_NAME);
        $response->headers->clearCookie(self::TOKEN_COOKIE_NAME);
        return $response;
    }

    public static function GetAdminID()
    {
        return $_COOKIE[self::ADMIN_ID_COOKIE_NAME];
    }
}
