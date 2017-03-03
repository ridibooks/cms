<?php
namespace Ridibooks\CmsServer\Service;

use Ridibooks\CmsServer\Model\AdminUser;

use Ridibooks\CmsServer\Thrift\AdminUser\AdminUser as ThriftAdminUser;
use Ridibooks\CmsServer\Thrift\AdminTag\AdminTag as ThriftAdminTag;

class AdminUserService
{
	/**
	 * 사용 가능한 모든 Admin 계정정보 가져온다.
	 * @return array
	 */
	public static function getAllAdminUserArray()
	{
		$users = AdminUser::select(['id', 'name'])->where('is_use', 1)->get();
		return $users->map(function ($user) {
			return new ThriftAdminMenu($user->toArray());
		})->all();
	}

	public static function getUser($id)
	{
		/** @var AdminUser $user */
		$user = AdminUser::find($id);
		if (!$user) {
			return null;
		}
		return new ThriftAdminUser($user->toArray());
	}

	public static function getAdminUserTag($user_id)
	{
		/** @var AdminUser $user */
		$user = AdminUser::find($user_id);
		if (!$user) {
			return [];
		}

		$tags = $user->tags->pluck('id');
		return $tags->map(function ($tag) {
			return new ThriftAdminTag($tag->toArray());
		})->all();
	}

	public static function getAdminUserMenu($user_id)
	{
		/** @var AdminUser $user */
		$user = AdminUser::find($user_id);
		if (!$user) {
			return [];
		}

		return $user->menus->pluck('id')->all();
	}

	public static function getAllMenuIds($user_id)
	{
		$user = AdminUser::with('tags.menus')->find($user_id);
		if (!$user) {
			return [];
		}

		// 1: user.tags.menus
		$tags_menus = $user->tags
			->map(function ($tag) {
				return $tag->menus->pluck('id');
			})
			->collapse()
			->all();

		// 2: user.menus
		$user_menus = self::getAdminUserMenu($user_id);

		// uniq(1 + 2)
		$menu_ids = array_unique(array_merge($tags_menus, $user_menus));

		return $menu_ids;
	}

	public static function updateMyInfo($user_id, $name, $team, $is_use, $passwd = '')
	{
		error_log('updateMyInfo ' . $user_id . ' ' . $name . ' ' . $team . ' ' . $is_use . ' ' . $passwd);

		/** @var AdminUser $admin */
		$me = AdminUser::find($user_id);
		if (!$me) {
			return false;
		}

		$filler = [
			'name' => $name,
			'team' => $team,
			'is_use' => $is_use
		];

		if (!empty($passwd)) {
			$filler['passwd'] = PasswordService::getPasswordAsHashed($passwd);
		}

		$me->fill($filler);
		$me->save();

		return true;
	}

	public static function updatePassword($user_id, $plain_password)
	{
		error_log('updatePassword ' . $user_id . ' ' . $plain_password);
		$me = AdminUser::find($user_id);
		if (!$me) {
			return false;
		}

		$me->passwd = PasswordService::getPasswordAsHashed($plain_password);
		$me->save();

		return true;
	}
}
