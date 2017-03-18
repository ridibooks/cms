<?php

namespace Ridibooks\Cms\Service;

use Ridibooks\Cms\Thrift\ThriftService;
use Ridibooks\Library\CouchbaseSessionHandler;
use Ridibooks\Platform\Cms\Auth\PasswordService;

class LoginService
{
	const SESSION_TIMEOUT_SEC = 60 * 60 * 24 * 14; // 2주

	/**
	 * @param string $id
	 * @param string $passwd
	 * @throws \Exception
	 */
	public static function doLoginAction($id, $passwd)
	{
		$user_service = new AdminUserService();
		$user = $user_service->getUser($id);
		$user = ThriftService::convertUserToArray($user);
		if (!$user || $user['is_use'] != '1') {
			throw new \Exception('잘못된 계정정보입니다.');
		}

		$passwd_service = new PasswordService();
		if (!$passwd_service->isPasswordMatchToHashed($passwd, $user['passwd'])) {
			throw new \Exception('비밀번호가 맞지 않습니다.');
		}

		if ($passwd_service->needsRehash($user['passwd'])) {
			$user_service->updatePassword($id, $passwd);
		}

		self::setSessions($id);
	}

	public static function doLoginActionWithoutPasswd($id)
	{
		$user_service = new AdminUserService();
		$user = $user_service->getUser($id);
		$user = ThriftService::convertUserToArray($user);
		if (!$user || $user['is_use'] != '1') {
			throw new \Exception('잘못된 계정정보입니다.');
		}

		self::setSessions($id);
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
	private static function setSessions($id)
	{
		//GetAdminID에 사용할 id를미리 set 한다.
		$_SESSION['session_admin_id'] = $id;
		AdminAuthService::initSession();
	}

	public static function resetSession()
	{
		$_SESSION['session_admin_id'] = null;
		$_SESSION['session_user_auth'] = null;
		$_SESSION['session_user_menu'] = null;
		$_SESSION['session_user_tag'] = null;
		$_SESSION['session_user_tagid'] = null;

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

	public static function startSession($cookie_domain = null)
	{
		if (!isset($cookie_domain)) {
			$cookie_domain = $_SERVER['SERVER_NAME'];
		}
		session_set_cookie_params(self::SESSION_TIMEOUT_SEC, '/', $cookie_domain);
		session_start();
	}

	public static function startCouchbaseSession($server_hosts, $cookie_domain = null)
	{
		session_set_save_handler(
			new CouchbaseSessionHandler($server_hosts, 'session', self::SESSION_TIMEOUT_SEC),
			true
		);

		self::startSession($cookie_domain);
	}
}
