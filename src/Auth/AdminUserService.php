<?php
namespace Ridibooks\Platform\Cms\Auth;

use Ridibooks\Cms\Thrift\ThriftService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AdminUserService
{
	private static $client = null;

	private static function getTClient()
	{
		if (!self::$client) {
			self::$client = ThriftService::getHttpClient('AdminUser');
		}

		return self::$client;
	}

	/**
	 * 사용 가능한 모든 Admin 계정정보 가져온다.
	 * @return array
	 */
	public static function getAllAdminUserArray()
	{
		$users = self::getTClient()->getAllAdminUserArray();
		return ThriftService::convertUserCollectionToArray($users);
	}

	public static function getUser($id)
	{
		$user = self::getTClient()->getUser($id);
		return ThriftService::convertUserToArray($user);
	}

	public static function getAdminUserTag($user_id)
	{
		$tags = self::getTClient()->getAdminUserTag($user_id);
		return ThriftService::convertTagCollectionToArray($tags);
	}

	public static function getAdminUserMenu($user_id)
	{
		return self::getTClient()->getAdminUserMenu($user_id);
	}

	public static function getAllMenuIds($user_id)
	{
		return self::getTClient()->getAllMenuIds($user_id);
	}

	public static function updateMyInfo($name, $team, $is_use, $passwd = '')
	{
		$result = self::getTClient()->updateMyInfo(LoginService::GetAdminID(), $name, $team, $is_use, $passwd);
		if (!$result) {
			throw new HttpException(Response::HTTP_NOT_FOUND);
		}
	}

	public static function updatePassword($user_id, $plain_password)
	{
		$result = self::getTClient()->updatePassword($user_id, $plain_password);
		if (!$result) {
			throw new HttpException(Response::HTTP_NOT_FOUND);
		}
	}
}
