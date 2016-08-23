<?php

namespace Ridibooks\Platform\Cms\Auth;

use Ridibooks\Platform\Cms\Auth\Dto\AdminUserDto;

class LoginService
{
	/**
	 * @var AdminUserService
	 */
	private $adminUserService;

	public function __construct()
	{
		$this->adminUserService = new AdminUserService();
	}

	/**
	 * @param string $id
	 * @param string $passwd
	 * @throws \Exception
	 */
	public function doLoginAction($id, $passwd)
	{
		$adminUserDto = new AdminUserDto($this->adminUserService->getAdminUser($id));
		$this->validatePassword($passwd, $adminUserDto->passwd);
		$this->validateUserInfo($adminUserDto);
		$this->setSessions($id);
	}

	/**
	 * @param string $inputPassword
	 * @param string $storedPassword
	 * @throws \Exception
	 */
	private function validatePassword($inputPassword, $storedPassword)
	{
		if (!PasswordService::isPasswordMatchToHashed($inputPassword, $storedPassword)) {
			throw new \Exception('잘못된 계정정보입니다.');
		}
	}

	/**
	 * @param AdminUserDto $adminUserDto
	 * @throws \Exception
	 */
	private function validateUserInfo($adminUserDto)
	{
		if (!$adminUserDto->is_use) {
			throw new \Exception('사용하지 않는 계정입니다.');
		}
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
		return in_array(php_sapi_name(), ['apache2filter', 'apache2handler']);
	}
}
