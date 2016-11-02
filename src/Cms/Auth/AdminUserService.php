<?php
namespace Ridibooks\Platform\Cms\Auth;

use Ridibooks\Platform\Cms\Model\AdminUser;
use Ridibooks\Platform\Common\StringUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AdminUserService
{
	/**
	 * 사용 가능한 모든 Admin 계정정보 가져온다.
	 * @return array
	 */
	public static function getAllAdminUserArray()
	{
		return AdminUser::select(['id', 'name'])->where('is_use', 1)->get()->toArray();
	}

	public static function getUser($id)
	{
		/** @var AdminUser $user */
		$user = AdminUser::find($id);
		if (!$user) {
			return null;
		}
		return $user->toArray();
	}

	public static function getAdminUserTag($user_id)
	{
		/** @var AdminUser $user */
		$user = AdminUser::find($user_id);
		if (!$user) {
			return [];
		}

		return $user->tags->pluck('id')->all();
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

	public static function updateMyInfo($name, $team, $is_use, $passwd = '')
	{
		/** @var AdminUser $admin */
		$me = AdminUser::find(LoginService::GetAdminID());
		if (!$me) {
			throw new HttpException(Response::HTTP_NOT_FOUND);
		}

		$filler = [
			'name' => $name,
			'team' => $team,
			'is_use' => $is_use
		];

		if (!StringUtils::isEmpty($passwd) !== '') {
			$filler['passwd'] = PasswordService::getPasswordAsHashed($passwd);
		}

		$me->fill($filler);
		$me->save();
	}
}
