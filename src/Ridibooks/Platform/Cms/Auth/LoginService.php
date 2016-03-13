<?php

namespace Ridibooks\Platform\Cms\Auth;

use Ridibooks\Platform\Cms\Auth\Dto\AdminUserDto;
use Ridibooks\Platform\Common\Base\AdminBaseService;

class LoginService extends AdminBaseService
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
		if ($storedPassword != crypt($inputPassword, $storedPassword)) { //기존 암호화 방식이 맞지 않을 경우
			if ($storedPassword != hash('sha256', $inputPassword)) { // sha256으로도 매칭이 되지 않으면 비밀번호가 틀렸거나 유저가 없는 경우
				throw new \Exception('잘못된 계정정보입니다.');
			}
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
		session_start();
		//GetAdminID에 사용할 id를미리 set 한다.
		$_SESSION['session_admin_id'] = $id;

		$authService = new AdminAuthService();
		$_SESSION['session_user_auth'] = $authService->getAdminAuth();
		$_SESSION['session_user_menu'] = $authService->getAdminMenu();
		$_SESSION['session_user_tag'] = $authService->getAdminTag();
		$_SESSION['session_user_tagid'] = $authService->getAdminTagId();
	}

	public static function GetAdminID()
	{
		return isset($_SESSION['session_admin_id']) ? $_SESSION['session_admin_id'] : null;
	}
}
