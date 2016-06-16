<?php

namespace Ridibooks\Platform\Cms\Auth;

use Ridibooks\Exception\MsgException;
use Ridibooks\Platform\Cms\Auth\Dto\AdminUserAuthDto;
use Ridibooks\Platform\Cms\Auth\Dto\AdminUserDto;
use Ridibooks\Platform\Cms\Auth\Dto\AdminUserMenuDto;
use Ridibooks\Platform\Cms\Auth\Dto\AdminUserTagDto;
use Ridibooks\Platform\Cms\Auth\Model\AdminMenus;
use Ridibooks\Platform\Cms\Auth\Model\AdminTags;
use Ridibooks\Platform\Cms\Auth\Model\AdminUser;
use Ridibooks\Platform\Cms\Auth\Model\AdminUserMenu;
use Ridibooks\Platform\Cms\Auth\Model\AdminUserMenus;
use Ridibooks\Platform\Cms\Auth\Model\AdminUserTag;
use Ridibooks\Platform\Cms\Auth\Model\AdminUserTags;
use Ridibooks\Platform\Cms\Auth\Model\TbAdminUserModel;
use Ridibooks\Platform\Common\Base\AdminBaseService;
use Ridibooks\Platform\Common\StringUtils;
use Ridibooks\Platform\Common\ValidationUtils;
use Ridibooks\Platform\Publisher\Constants\PublisherManagerTypes;
use Ridibooks\Platform\Publisher\Model\TbPublisherManager;

/**Admin 유저 관리 Service
 * Class AdminUserService
 * @package Ridibooks\Platform\Cms\Auth
 */
class AdminUserService extends AdminBaseService
{
	private $adminUser;
	private $adminUserTags;
	private $adminUserTag;
	private $adminTags;
	private $adminUserMenus;
	private $adminUserMenu;
	private $adminMenus;
	private $publisherManager;

	public function __construct()
	{
		$this->adminUser = new AdminUser();
		$this->adminUserTags = new AdminUserTags();
		$this->adminUserTag = new AdminUserTag();
		$this->adminUserMenus = new AdminUserMenus();
		$this->adminUserMenu = new AdminUserMenu();
		$this->adminTags = new AdminTags();
		$this->adminMenus = new AdminMenus();
		$this->publisherManager = new TbPublisherManager();
	}

	/**Admin 계정정보 리스트 갯수 가져온다.
	 * @param string $search_text
	 * @return int
	 */
	public function getAdminUserCount($search_text)
	{
		return TbAdminUserModel::getAdminUserCount($search_text);
	}

	/**Admin 계정정보 리스트 가져온다.
	 * @param string $search_text
	 * @param \Ridibooks\Platform\Common\PagingUtil $pagingDto
	 * @return array
	 */
	public function getAdminUserList($search_text, $pagingDto)
	{
		return TbAdminUserModel::getAdminUserList($search_text, $pagingDto->start, $pagingDto->limit);
	}

	/**사용 가능한 모든 Admin 계정정보 가져온다.
	 * @return array
	 */
	public function getAllAdminUserArray()
	{
		return TbAdminUserModel::getAllAdminUserArray();
	}

	public function getAllAdminUserListByDictionary()
	{
		$dict = array();
		$items = $this->getAllAdminUserArray();
		foreach ($items as $item) {
			$dict[$item->id] = $item;
		}
		return $dict;
	}

	/**Admin 계정정보 가져온다.
	 * @param string $id
	 * @return array
	 */
	public function getAdminUser($id)
	{
		return TbAdminUserModel::getAdminUser($id);
	}

	/**Admin Tag 정보 가져온다.
	 * (select2를 위해 implode 처리함)
	 * @param string $user_id 어드민 유저 ID
	 * @return string 태그 array를 , 로 묶음
	 */
	public function getAdminUserTag($user_id)
	{
		return implode(",", $this->adminUserTags->getAdminUserTagList($user_id));
	}

	/**Admin Menu 정보 가져온다.
	 * (select2를 위해 implode 처리함)
	 * @param string $user_id 어드민 유저 ID
	 * @return string 메뉴 array를 , 로 묶음
	 */
	public function getAdminUserMenu($user_id)
	{
		return implode(",", $this->adminUserMenus->getAdminUserMenuList($user_id));

	}

	/**해당 메뉴에 대한 권한을 가지고 있는 어드민을 가져온다.
	 * @param int $menu_id
	 * @return array
	 */
	public function getAdminIdsByMenuId($menu_id)
	{
		return $this->adminUserMenus->getAdminIdsByMenuId($menu_id);
	}

	/**
	 * 해당 tag를 가지고 있는 사용중인 어드민 ID를 가져온다.
	 * @param $tag_id
	 * @return array
	 */
	public function getValidAdminIdFromUserTag($tag_id)
	{
		$admin_id_rows = $this->adminUserTags->getAdminIdFromUserTag($tag_id);
		$admin_ids = array();
		foreach ($admin_id_rows as $admin_id_row) {
			$adminUserDto = new AdminUserDto(TbAdminUserModel::getAdminUser($admin_id_row));
			if (!!$adminUserDto->is_use) {
				$admin_ids[] = $adminUserDto->id;
			}
		}

		return $admin_ids;
	}

	/** 전체 팀 리스트를 가져온다.
	 * @return array
	 */
	public function getWholeTeamList()
	{
		return TbAdminUserModel::getTeamList();
	}

	/** 해당 팀에 속한 멤버의 리스트를 가져온다.
	 * @param $team
	 * @return array
	 */
	public function getTeamMemberList($team)
	{
		return TbAdminUserModel::getTeamMemberList($team);
	}

	/**Admin 계정정보 등록한다.
	 * @param AdminUserDto $adminUserDto
	 * @throws
	 */
	public function insertAdminUser($adminUserDto)
	{
		$this->startTransaction();

		$this->_validateAdminUserInsert($adminUserDto);
		//password encrypt
		$adminUserDto->passwd = PasswordService::getPasswordAsHashed($adminUserDto->passwd);

		if (TbAdminUserModel::getAdminUserIdCount($adminUserDto->id) > 0) { //ID로 카운트 하여 값이 0 이상일 경우
			throw new MsgException('동일한 ID가 있습니다.');
		}
		$this->adminUser->insertAdminUser($adminUserDto);

		$this->endTransaction();
	}

	/**Admin 계정정보 수정한다.
	 * @param AdminUserDto $adminUserDto
	 * @throws \Exception
	 */
	public function updateAdminUser($adminUserDto)
	{
		$this->startTransaction();

		$this->_validateAdminUserUpdate($adminUserDto);

		if (isset($adminUserDto->passwd) && trim($adminUserDto->passwd) !== '') {
			$adminUserDto->passwd = PasswordService::getPasswordAsHashed($adminUserDto->passwd);
		}
		$adminUserDto->id = trim($adminUserDto->id);
		$adminUserDto->last_id = trim($adminUserDto->last_id);

		if (strlen($adminUserDto->last_id) && $adminUserDto->id != $adminUserDto->last_id) {
			$old_id = $adminUserDto->last_id;
			$new_id = $adminUserDto->id;

			$this->adminUser->updateAdminID($old_id, $new_id);
			$this->adminUserTags->updateUserOfTags($old_id, $new_id);
			$this->adminUserMenus->updateUserOfMenus($old_id, $new_id);
		}

		$this->adminUser->updateAdminUser($adminUserDto);

		$this->endTransaction();
	}

	/**자신의 정보를 수정한다.
	 * @param AdminUserDto $adminUserDto
	 * @throws
	 */
	public function updateUserInfo($adminUserDto)
	{
		$this->startTransaction();

		if (StringUtils::isEmpty($adminUserDto->new_passwd) === false) {
			if ($adminUserDto->new_passwd != $adminUserDto->chk_passwd) {
				throw new MsgException('변경할 비밀번호가 일치하지 않습니다.');
			}
			$adminUserDto->passwd = $adminUserDto->new_passwd;
		}
		$this->updateAdminUser($adminUserDto);

		$this->endTransaction();
	}

	public function deleteAdmin($adminUserDto)
	{
		$this->adminUser->deleteAdmin($adminUserDto->id);
	}

	/**어드민 계정에 권한정보를 입력한다.
	 * @param AdminUserAuthDto $adminUserAuthDto
	 */
	public function insertAdminUserAuth($adminUserAuthDto)
	{
		$this->startTransaction();

		$this->_insertAdminUserTag($adminUserAuthDto);
		$this->_insertAdminUserMenu($adminUserAuthDto);
		$this->_insertManager($adminUserAuthDto);

		$this->endTransaction();
	}

	/**어드민 계정에 태그정보 등록한다.
	 * @param AdminUserAuthDto $adminUserAuthDto
	 */
	private function _insertAdminUserTag($adminUserAuthDto)
	{
		$tagIdArray = explode(",", $adminUserAuthDto->tag_id_array);
		$tagIdArray = array_filter(array_unique($tagIdArray));

		/**어드민 계정에 매핑된 모든 태그를 지운다.*/
		$this->adminUserTag->deleteAdminUserTag($adminUserAuthDto->id);

		foreach ($tagIdArray as $tag_id) {
			$this->adminUserTag->insertAdminUserTag(new AdminUserTagDto($adminUserAuthDto->id, $tag_id));
		}
	}

	/**어드민 계정에 메뉴정보 등록한다.
	 * @param AdminUserAuthDto $adminUserAuthDto
	 */
	private function _insertAdminUserMenu($adminUserAuthDto)
	{
		$menuIdArray = explode(",", $adminUserAuthDto->menu_id_array);
		$menuIdArray = array_filter(array_unique($menuIdArray));
		/**어드민 계정에 매핑된 모든 메뉴를 지운다.*/
		$this->adminUserMenu->deleteAdminUserMenu($adminUserAuthDto->id);

		foreach ($menuIdArray as $menu_id) {
			$this->adminUserMenu->insertAdminUserMenu(new AdminUserMenuDto($adminUserAuthDto->id, $menu_id));
		}
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
