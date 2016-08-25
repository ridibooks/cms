<?php
namespace Ridibooks\Platform\Cms\Auth;

use Illuminate\Database\Capsule\Manager as DB;
use Ridibooks\Exception\MsgException;
use Ridibooks\Platform\Cms\Auth\Dto\AdminUserAuthDto;
use Ridibooks\Platform\Cms\Auth\Dto\AdminUserDto;
use Ridibooks\Platform\Cms\Model\AdminMenu;
use Ridibooks\Platform\Cms\Model\AdminUser;
use Ridibooks\Platform\Common\StringUtils;
use Ridibooks\Platform\Common\ValidationUtils;
use Ridibooks\Platform\Publisher\Constants\PublisherManagerTypes;
use Ridibooks\Platform\Publisher\Model\TbPublisherManager;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class AdminUserService
{
	public static function getAdminUserCount($search_text)
	{
		return AdminUser::query()
			->where('id', 'like', '%' . $search_text . '%')
			->orWhere('name', 'like', '%' . $search_text . '%')
			->count();
	}

	public static function getAdminUserList($search_text, $offset, $limit)
	{
		return AdminUser::query()
			->where('id', 'like', '%' . $search_text . '%')
			->orWhere('name', 'like', '%' . $search_text . '%')
			->orderBy('is_use', 'desc')
			->orderBy('id')
			->skip($offset)->take($limit)
			->get();
	}

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

	/** @deprecated */
	public function getAdminUser($id)
	{
		return self::getUser($id);
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

	/**
	 * 해당 메뉴에 대한 권한을 가지고 있는 어드민을 가져온다.
	 * @deprecated keep for external use
	 * @param int $menu_id
	 * @return array
	 */
	public function getAdminIdsByMenuId($menu_id)
	{
		/** @var AdminMenu $menu */
		$menu = AdminMenu::find($menu_id);
		if (!$menu) {
			return [];
		}

		return $menu->users->pluck('id')->all();
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

	/**
	 * 해당 어드민에 매핑되어있는 태그 유무를 위한 갯수 가져온다.
	 * @param $user_id
	 * @param string[] $tag_ids
	 * @return bool
	 */
	public static function checkAdminUserHasTag($user_id, $tag_ids)
	{
		/** @var AdminUser $user */
		$user = AdminUser::withCount([
			'tags' => function ($query) use ($tag_ids) {
				$query->whereIn('id', $tag_ids);
			}
		])->find($user_id);

		return ($user && $user->tags_count > 0);
	}

	public static function insertAdminUser($adminUserDto)
	{
		self::_validateAdminUserInsert($adminUserDto);

		// password encrypt
		$adminUserDto->passwd = PasswordService::getPasswordAsHashed($adminUserDto->passwd);
		AdminUser::create((array)$adminUserDto);
	}

	public static function updateUserInfo(AdminUserDto $adminUserDto)
	{
		if (StringUtils::isEmpty($adminUserDto->new_passwd) === false) {
			if ($adminUserDto->new_passwd != $adminUserDto->chk_passwd) {
				throw new MsgException('변경할 비밀번호가 일치하지 않습니다.');
			}
			$adminUserDto->passwd = $adminUserDto->new_passwd;
		}

		self::_validateAdminUserUpdate($adminUserDto);

		$filler = [
			'name' => $adminUserDto->name,
			'team' => $adminUserDto->team,
			'is_use' => $adminUserDto->is_use
		];

		if (isset($adminUserDto->passwd) && trim($adminUserDto->passwd) !== '') {
			$filler['passwd'] = PasswordService::getPasswordAsHashed($adminUserDto->passwd);
		}

		/** @var AdminUser $admin */
		$admin = AdminUser::find(trim($adminUserDto->id));
		$admin->fill($filler);
		$admin->save();
	}

	public static function deleteUser($user_id)
	{
		AdminUser::destroy($user_id);
	}

	public static function updateUserPermissions($user_id, AdminUserAuthDto $adminUserAuthDto)
	{
		/** @var AdminUser $user */
		$user = AdminUser::find($user_id);
		if (!$user) {
			throw new ResourceNotFoundException();
		}

		DB::connection()->transaction(function () use ($user, $adminUserAuthDto) {
			$user->tags()->sync($adminUserAuthDto->tag_ids);
			$user->menus()->sync($adminUserAuthDto->menu_ids);
			self::_updateManagingCps($user, PublisherManagerTypes::PARTNERSHIP_MANAGER, $adminUserAuthDto->partner_cp_ids);
			self::_updateManagingCps($user, PublisherManagerTypes::OPERATOR_MANAGER, $adminUserAuthDto->operator_cp_ids);
			self::_updateManagingCps($user, PublisherManagerTypes::PRODUCTION_MANAGER, $adminUserAuthDto->production_cp_ids);
		});
	}

	private static function _updateManagingCps(AdminUser $user, $manager_type, array $cp_ids)
	{
		$publisherManager = new TbPublisherManager();

		$publisherManager->deleteAllManager($user->id, $manager_type);

		foreach ($cp_ids as $cp_id) {
			$publisherManager->insertManager(
				$cp_id,
				$manager_type,
				$user->id
			);
		}
	}

	/**Admin 계정 insert validator
	 * @param AdminUserDto $adminUserDto
	 */
	private static function _validateAdminUserInsert($adminUserDto)
	{
		ValidationUtils::checkNullField($adminUserDto->id, '계정 ID를 입력하여 주십시오.');
		ValidationUtils::checkNullField($adminUserDto->passwd, '계정 비밀번호를 입력하여 주십시오.');
		self::_validateAdminUserUpdate($adminUserDto);
	}

	/**Admin 계정 update validator
	 * @param AdminUserDto $adminUserDto
	 */
	private static function _validateAdminUserUpdate($adminUserDto)
	{
		ValidationUtils::checkNullField($adminUserDto->id, '계정ID를 입력하여 주십시오.');
		ValidationUtils::checkNullField($adminUserDto->name, '이름을 입력하여 주십시오.');
		ValidationUtils::checkNullField($adminUserDto->team, '팀을 입력하여 주십시오.');
		ValidationUtils::checkNullField($adminUserDto->is_use, '사용 여부를 선택하여 주십시오.');
	}

	public static function appendEmailAddress($admin_id)
	{
		return $admin_id . "@ridi.com";
	}
}
