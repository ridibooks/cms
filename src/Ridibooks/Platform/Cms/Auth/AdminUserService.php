<?php
namespace Ridibooks\Platform\Cms\Auth;

use Illuminate\Database\Capsule\Manager as DB;
use Ridibooks\Exception\MsgException;
use Ridibooks\Platform\Cms\Auth\Dto\AdminUserAuthDto;
use Ridibooks\Platform\Cms\Auth\Dto\AdminUserDto;
use Ridibooks\Platform\Cms\Model\AdminMenu;
use Ridibooks\Platform\Cms\Model\AdminUser;
use Ridibooks\Platform\Common\Base\AdminBaseService;
use Ridibooks\Platform\Common\StringUtils;
use Ridibooks\Platform\Common\ValidationUtils;
use Ridibooks\Platform\Publisher\Constants\PublisherManagerTypes;
use Ridibooks\Platform\Publisher\Model\TbPublisherManager;

class AdminUserService extends AdminBaseService
{
	private $publisherManager;

	public function __construct()
	{
		$this->publisherManager = new TbPublisherManager();
	}

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

	public function getAdminUser($id)
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

	/**Admin 계정정보 등록한다.
	 * @param AdminUserDto $adminUserDto
	 * @throws
	 */
	public function insertAdminUser($adminUserDto)
	{
		$this->_validateAdminUserInsert($adminUserDto);

		//password encrypt
		$adminUserDto->passwd = PasswordService::getPasswordAsHashed($adminUserDto->passwd);
		AdminUser::create((array)$adminUserDto);
	}

	public function updateAdminUser($adminUserDto)
	{
		$this->_validateAdminUserUpdate($adminUserDto);

		$adminUserDto->id = trim($adminUserDto->id);
		if (isset($adminUserDto->passwd) && trim($adminUserDto->passwd) !== '') {
			$adminUserDto->passwd = PasswordService::getPasswordAsHashed($adminUserDto->passwd);
		}

		/** @var AdminUser $admin */
		$admin = AdminUser::find($adminUserDto->id);
		$admin->fill((array)$adminUserDto);
		$admin->save();
	}

	/**자신의 정보를 수정한다.
	 * @param AdminUserDto $adminUserDto
	 * @throws
	 */
	public function updateUserInfo($adminUserDto)
	{
		if (StringUtils::isEmpty($adminUserDto->new_passwd) === false) {
			if ($adminUserDto->new_passwd != $adminUserDto->chk_passwd) {
				throw new MsgException('변경할 비밀번호가 일치하지 않습니다.');
			}
			$adminUserDto->passwd = $adminUserDto->new_passwd;
		}
		$this->updateAdminUser($adminUserDto);
	}

	public function deleteAdmin($adminUserDto)
	{
		AdminUser::destroy($adminUserDto->id);
	}

	/**어드민 계정에 권한정보를 입력한다.
	 * @param AdminUserAuthDto $adminUserAuthDto
	 */
	public function insertAdminUserAuth($adminUserAuthDto)
	{
		DB::connection()->transaction(function () use ($adminUserAuthDto) {
			$this->_insertAdminUserTag($adminUserAuthDto);
			$this->_insertAdminUserMenu($adminUserAuthDto);
			$this->_insertManager($adminUserAuthDto);
		});
	}

	/**어드민 계정에 태그정보 등록한다.
	 * @param AdminUserAuthDto $adminUserAuthDto
	 */
	private function _insertAdminUserTag($adminUserAuthDto)
	{
		$tagIdArray = explode(",", $adminUserAuthDto->tag_id_array);
		$tagIdArray = array_filter(array_unique($tagIdArray));

		/** @var AdminUser $user */
		$user = AdminUser::find($adminUserAuthDto->id);
		$user->tags()->sync($tagIdArray);
	}

	/**어드민 계정에 메뉴정보 등록한다.
	 * @param AdminUserAuthDto $adminUserAuthDto
	 */
	private function _insertAdminUserMenu($adminUserAuthDto)
	{
		$menuIdArray = explode(",", $adminUserAuthDto->menu_id_array);
		$menuIdArray = array_filter(array_unique($menuIdArray));

		/** @var AdminUser $user */
		$user = AdminUser::find($adminUserAuthDto->id);
		$user->menus()->sync($menuIdArray);
	}

	/**어드민 계정이 담당하는 CP 정보 등록한다.
	 * @param AdminUserAuthDto $adminUserAuthDto
	 */
	private function _insertManager($adminUserAuthDto)
	{
		$cp_ids = explode(",", $adminUserAuthDto->partner_cp_id_array);
		$cp_ids = array_filter(array_unique($cp_ids));
		$this->publisherManager->deleteAllManager($adminUserAuthDto->id, PublisherManagerTypes::PARTNERSHIP_MANAGER);

		foreach ($cp_ids as $cp_id) {
			$this->publisherManager->insertManager(
				$cp_id,
				PublisherManagerTypes::PARTNERSHIP_MANAGER,
				$adminUserAuthDto->id
			);
		}

		$cp_ids = explode(",", $adminUserAuthDto->operator_cp_id_array);
		$cp_ids = array_filter(array_unique($cp_ids));
		$this->publisherManager->deleteAllManager($adminUserAuthDto->id, PublisherManagerTypes::OPERATOR_MANAGER);

		foreach ($cp_ids as $cp_id) {
			$this->publisherManager->insertManager(
				$cp_id,
				PublisherManagerTypes::OPERATOR_MANAGER,
				$adminUserAuthDto->id
			);
		}

		/**제작 CP 등록*/
		$cp_ids = explode(",", $adminUserAuthDto->production_cp_id_array);
		$cp_ids = array_filter(array_unique($cp_ids));
		/**어드민 계정에 매핑된 모든 제작 CP 정보 삭제한다.*/
		$this->publisherManager->deleteAllManager($adminUserAuthDto->id, PublisherManagerTypes::PRODUCTION_MANAGER);

		foreach ($cp_ids as $cp_id) {
			$this->publisherManager->insertManager(
				$cp_id,
				PublisherManagerTypes::PRODUCTION_MANAGER,
				$adminUserAuthDto->id
			);
		}
	}

	/**Admin 계정 insert validator
	 * @param AdminUserDto $adminUserDto
	 */
	private function _validateAdminUserInsert($adminUserDto)
	{
		ValidationUtils::checkNullField($adminUserDto->id, '계정 ID를 입력하여 주십시오.');
		ValidationUtils::checkNullField($adminUserDto->passwd, '계정 비밀번호를 입력하여 주십시오.');
		$this->_validateAdminUserUpdate($adminUserDto);
	}

	/**Admin 계정 update validator
	 * @param AdminUserDto $adminUserDto
	 */
	private function _validateAdminUserUpdate($adminUserDto)
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
