<?php

namespace Ridibooks\Cms\Service;

use Ridibooks\Cms\Session\CouchbaseSessionHandler;
use Ridibooks\Cms\Thrift\ThriftService;

class LoginService
{
    const SESSION_TIMEOUT_SEC = 60 * 60 * 12; // 12hours

    public static function doLoginWithAzure($azure_resource)
    {
        $user_service = new AdminUserService();
        $user = $user_service->getUser($azure_resource->mailNickname);
        $user = ThriftService::convertUserToArray($user);
        if (!$user || !$user['id']) {
            $user_service->addNewUser($azure_resource->mailNickname, $azure_resource->displayName, '');
        } elseif ($user['is_use'] != '1') {
            throw new \Exception('사용이 금지된 계정입니다. 관리자에게 문의하세요.');
        }

        self::setSessions($azure_resource->mailNickname);
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

    /**
     * @param string $id
     */
    public static function setSessions($id)
    {
        //GetAdminID에 사용할 id를미리 set 한다.
        $_SESSION['session_admin_id'] = $id;
    }

    public static function resetSession()
    {
        $_SESSION['session_admin_id'] = null;

        @session_destroy();
    }

    /**
     * Cron에서 사용이 예상되면 isSessionableEnviroment() 호출하여 체크 후, 다른 이름을 사용해야한다.
     */
    public static function GetAdminID()
    {
        if (!self::isSessionableEnviroment()) {
            trigger_error('LoginService::GetAdminID() called in not sessionable enviroment, please fix it');
        }
        return isset($_SESSION['session_admin_id']) ? $_SESSION['session_admin_id'] : null;
    }

    public static function isSessionableEnviroment()
    {
        return in_array(php_sapi_name(), ['apache2filter', 'apache2handler', 'cli-server']);
    }

    public static function startSession($session_domain = null)
    {
        if (!isset($session_domain) || $session_domain === '') {
            $session_domain = $_SERVER['SERVER_NAME'];
        }

        session_set_cookie_params(self::SESSION_TIMEOUT_SEC, '/', $session_domain);
        session_start();
    }

    public static function startMemcacheSession($server_hosts, $session_domain = null)
    {
        session_set_cookie_params(self::SESSION_TIMEOUT_SEC, '/', $session_domain);
        ini_set('session.gc_maxlifetime', self::SESSION_TIMEOUT_SEC);
        ini_set('session.save_handler', 'memcache');
        ini_set('session.save_path', $server_hosts);

        self::startSession($session_domain);
    }

    public static function startCouchbaseSession($server_hosts, $session_domain = null)
    {
        session_set_save_handler(
            new CouchbaseSessionHandler($server_hosts, 'session', self::SESSION_TIMEOUT_SEC),
            true
        );

        self::startSession($session_domain);
    }
}
