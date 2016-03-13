<?php
namespace Ridibooks\Platform\Cms\Auth;

use Ridibooks\Exception\MsgException;
use Ridibooks\Platform\Cms\Auth\Model\AdminMenus;
use Ridibooks\Platform\Cms\Auth\Model\AdminTag;
use Ridibooks\Platform\Cms\Auth\Model\AdminTagMenu;
use Ridibooks\Platform\Cms\Auth\Model\AdminTagMenus;
use Ridibooks\Platform\Cms\Auth\Model\AdminTags;
use Ridibooks\Platform\Cms\Auth\Model\AdminUserTags;
use Ridibooks\Platform\Common\Base\AdminBaseService;
use Ridibooks\Platform\Common\ValidationUtils;

/**Tag Service
 * Class TagService
 * @package Ridibooks\Platform\Cms\Auth
 */
class AdminTagService extends AdminBaseService
{

	private $adminTags;
	private $adminTag;
	private $adminUserTags;
	private $adminTagMenus;
	private $adminTagMenu;
	private $adminMenus;

	public function __construct()
	{
		$this->adminTag = new AdminTag();
		$this->adminTags = new AdminTags();
		$this->adminUserTags = new AdminUserTags();
		$this->adminTagMenus = new AdminTagMenus();
		$this->adminTagMenu = new AdminTagMenu();
		$this->adminMenus = new AdminMenus();
	}

	/**태그 리스트 가져온다.
	 * @return array
	 */
	public function getTagList()
	{
		return $this->adminTags->getTagList();
	}

	/**태그에 매핑된 메뉴 리스트 가져온다.
	 * @param $tag_id
	 * @return array
	 * @throws
	 */
	public function getMappedAdminMenuListForSelectBox($tag_id)
	{
		//메뉴 리스트
		$menu_list = $this->adminMenus->getAdminMenuList();
		//태그에 매핑된 메뉴 리스트
		$menu_tag_list = $this->adminTagMenus->getAdminMenuTagList($tag_id);

		$mapped_menu_tag_list = array();
		foreach ($menu_list as $menu) {
			foreach ($menu_tag_list as $menu_tag) {
				if ($menu['id'] == $menu_tag['menu_id']) {
					$menu['selected'] = 'selected';
				}
			}
			array_push($mapped_menu_tag_list, $menu);
		}
		return $mapped_menu_tag_list;
	}

	/**태그에 매핑된 메뉴 리스트 가져온다.
	 * @param $tag_id
	 * @return array
	 * @throws
	 */
	public function getMappedAdminMenuList($tag_id)
	{
		//메뉴 리스트
		$menu_list = $this->adminMenus->getAdminMenuList();
		//태그에 매핑된 메뉴 리스트
		$menu_tag_list = $this->adminTagMenus->getAdminMenuTagList($tag_id);

		$mapped_menu_tag_list = array();
		foreach ($menu_list as $menu) {
			foreach ($menu_tag_list as $menu_tag) {
				if ($menu['id'] == $menu_tag['menu_id']) {
					array_push($mapped_menu_tag_list, $menu);
				}
			}
		}
		return $mapped_menu_tag_list;
	}

	public function getMappedAdminMenuHashes($check_url, $tag_id)
	{
		$menus = $this->getMappedAdminMenuList($tag_id);
		$ret = AdminAuthService::getHashesFromMenus($check_url, $menus);
		return $ret;
	}

	/**태그 등록한다.
	 * @param \Ridibooks\Platform\Cms\Auth\Dto\AdminTagDto $tagDto
	 */
	public function insertTag($tagDto)
	{
		$this->startTransaction();

		$this->_validateTag((array)$tagDto);
		$this->adminTag->insertTag($tagDto);

		$this->endTransaction();
	}

	/**태그 수정한다.
	 * @param \Ridibooks\Platform\Cms\Auth\Dto\AdminTagDto $tagDto
	 * @throws \Ridibooks\Exception\MsgException
	 */
	public function updateTag($tagDto)
	{
		$this->startTransaction();
		foreach ($tagDto->tag_list as $tag) {
			$this->_validateTag($tag);

			if ($tag['is_use'] != 1) {
				$user_count = $this->adminUserTags->getAdminUserTaggedCount($tag['id']);
				if ($user_count > 0) { //해당 태그와 매핑되어있는 사용자가 있으면 사용중지를 할 수 없다.
					throw new MsgException('해당 태그를 사용하고 있는 유저가 있습니다. 사용중인 유저: ' . $user_count);
				}
			}
			$this->adminTag->updateTag($tag);
		}
		$this->endTransaction();
	}

	/**태그에 메뉴를 매핑시킨다.
	 * @param \Ridibooks\Platform\Cms\Auth\Dto\AdminTagMenuDto $tagMenuDto
	 */
	public function insertTagMenu($tagMenuDto)
	{
		$this->startTransaction();
		$this->_validateTagMenu((array)$tagMenuDto);

		if ($this->adminTagMenus->getAdminMenuTagCount(
				$tagMenuDto->tag_id,
				$tagMenuDto->menu_id
			) == 0
		) { //매핑시키려는 메뉴가 태그에 매핑 되어있지 않은 경우
			$this->adminTagMenu->insertAdminMenuTag((array)$tagMenuDto);
		}
		$this->endTransaction();
	}

	/**태그에 등록된 메뉴 삭제한다.
	 * @param \Ridibooks\Platform\Cms\Auth\Dto\AdminTagMenuDto $tagMenuDto
	 */
	public function deleteTagMenu($tagMenuDto)
	{
		$this->startTransaction();
		$this->adminTagMenu->deleteAdminMenuTag((array)$tagMenuDto);
		$this->endTransaction();
	}

	/**태그 입력값 검사
	 * @param array $tagArray
	 * @throws \Ridibooks\Exception\MsgException
	 */
	private function _validateTag($tagArray)
	{
		ValidationUtils::checkNullField($tagArray['name'], '태그 이름을 입력하여 주십시오.');
		$tag_id = $this->adminTags->getTagNameId($tagArray['name']);

		if ($tag_id && $tag_id != $tagArray['id']) { //DB에 태그 이름이 존재하고 그 이름이 자신과 다르다면
			throw new MsgException("동일한 태그 이름이 있습니다.");
		}
	}

	/**태그 메뉴 입력값 검사
	 * @param $tagArray
	 */
	private function _validateTagMenu($tagArray)
	{
		ValidationUtils::checkNullField($tagArray['tag_id'], "태그 ID가 없습니다.");
		ValidationUtils::checkNullField($tagArray['menu_id'], "메뉴 ID가 없습니다.");
	}
}
