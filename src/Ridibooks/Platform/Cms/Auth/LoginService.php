<?php

namespace Ridibooks\Platform\Cms\Auth;

use Ridibooks\Platform\Cms\Auth\Dto\AdminUserDto;
use Ridibooks\Platform\Common\PasswordService;

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

	public static function GetAdminID()
	{
		return isset($_SESSION['session_admin_id']) ? $_SESSION['session_admin_id'] : null;
	}
}
