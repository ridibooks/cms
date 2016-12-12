<?php

namespace Ridibooks\Platform\Cms\Auth;

use Ridibooks\Library\CouchbaseSessionHandler;

class LoginService
{
	const SESSION_TIMEOUT_SEC = 60 * 60 * 24 * 14; // 2주

	/**
	 * @param string $id
	 * @param string $passwd
	 * @throws \Exception
	 */
	public function doLoginAction($id, $passwd)
	{
		$user = AdminUserService::getUser($id);
		if (!$user || $user['is_use'] != '1') {
			throw new \Exception('잘못된 계정정보입니다.');
		}

		if (!PasswordService::isPasswordMatchToHashed($passwd, $user['passwd'])) {
			throw new \Exception('비밀번호가 맞지 않습니다.');
		}

		$this->setSessions($id);
	}

	/**
	 * @param string $id
	 */
	private function setSessions($id)
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
	 * @return null
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

	public static function startSession()
	{
		if (\Config::$COUCHBASE_ENABLE) {
			session_set_save_handler(
				new CouchbaseSessionHandler(implode(',', \Config::$COUCHBASE_SERVER_HOSTS), 'session_cms', self::SESSION_TIMEOUT_SEC),
				true
			);
		}
		session_set_cookie_params(self::SESSION_TIMEOUT_SEC, '/', \Config::$ADMIN_DOMAIN);
		session_start();
	}
}
