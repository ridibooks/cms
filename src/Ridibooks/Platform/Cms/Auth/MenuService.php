<?php
namespace Ridibooks\Platform\Cms\Auth;

use Ridibooks\Exception\MsgException;
use Ridibooks\Platform\Cms\Auth\Dto\AdminMenuAjaxDto;
use Ridibooks\Platform\Cms\Auth\Model\AdminMenu;
use Ridibooks\Platform\Cms\Auth\Model\AdminMenuAjax;
use Ridibooks\Platform\Cms\Auth\Model\AdminMenuAjaxs;
use Ridibooks\Platform\Cms\Auth\Model\AdminMenus;
use Ridibooks\Platform\Common\Base\AdminBaseService;
use Ridibooks\Platform\Common\ValidationUtils;

/**
 * 메뉴 관리 Service
 * Class MenuService
 * @package Ridibooks\Platform\Cms\Auth
 */
class MenuService extends AdminBaseService
{
	private $adminMenus;
	private $adminMenu;
	private $adminMenuAjaxs;
	private $adminMenuAjax;

	public function __construct()
	{
		$this->adminMenu = new AdminMenu();
		$this->adminMenus = new AdminMenus();
		$this->adminMenuAjax = new AdminMenuAjax();
		$this->adminMenuAjaxs = new AdminMenuAjaxs();
	}

	/**메뉴 리스트 가져온다.
	 * @param int|null $is_use
	 * @param string $search_text
	 * @return array
	 */
	public function getMenuList($is_use = null, $search_text = null)
	{
		return $this->adminMenus->getAdminMenuList($is_use, $search_text);
	}


	/**메뉴 등록한다.
	 * @param \Ridibooks\Platform\Cms\Auth\Dto\AdminMenuDto $menuDto
	 */
	public function insertMenu($menuDto)
	{
		$this->startTransaction();

		$this->_validateMenu((array)$menuDto);
		//메뉴의 최대 순서값 가져온다.
		$max_order = $this->adminMenus->getMaxMenuOrder();

		if ($menuDto->menu_order == null) { //메뉴 순서값이 없을 경우 메뉴 순서값을 max+1 해준다.
			$menuDto->menu_order = $max_order + 1;
		}

		ValidationUtils::checkNumberField($menuDto->menu_order, "메뉴 순서는 숫자만 입력 가능합니다.");

		//입력받은 메뉴 순서값이 이미 존재 하고 있을 경우 해당 순서 이하의 모든 메뉴를 한칸씩 내린다.
		if ($this->adminMenus->getMenuOrderCount($menuDto->menu_order) > 0) {
			$this->adminMenu->updateMenuOrder($menuDto->menu_order);
		}
		$this->adminMenu->insertMenu($menuDto);

		$this->endTransaction();
	}

	/**메뉴 수정한다.
	 * @param \Ridibooks\Platform\Cms\Auth\Dto\AdminMenuDto $menuDto
	 */
	public function updateMenu($menuDto)
	{
		$this->startTransaction();

		$max_order = $this->adminMenus->getMaxMenuOrder();

		foreach ($menuDto->menu_list as $menu) {
			$this->_validateMenu($menu);
			$old_menu_order = $this->adminMenus->getAdminMenuOrder($menu['id']);
			$new_menu_order = $menu['menu_order'];

			if ($new_menu_order == null) { //입력받은 메뉴 순서값 없을 경우 메뉴 순서값을 max+1 해준다.
				$menu['menu_order'] = $max_order + 1;
			} else {
				ValidationUtils::checkNumberField($new_menu_order, "메뉴 순서는 숫자만 입력 가능합니다.");

				if ($this->adminMenus->getMenuOrderCount($new_menu_order) > 0) { //입력받은 메뉴 순서값이 이미 존재하고 있을 경우 메뉴 순서를 재 정렬할 필요가 있다.
					if ($old_menu_order > $new_menu_order) { //밑에 있는 메뉴를 위로 올릴때
						$this->adminMenu->updateMenuOrderUpper($old_menu_order, $new_menu_order);
					} elseif ($old_menu_order < $new_menu_order) { //위에 있는 메뉴를 아래로 내릴때
						$this->adminMenu->updateMenuOrderLower($old_menu_order, $new_menu_order);
					}
				}
			}
			$this->adminMenu->updateMenu($menu);
		}
		$this->endTransaction();
	}

	/**메뉴 Ajax 리스트 가져온다.
	 * @param $menu_id
	 * @return array
	 */
	public function getMenuAjaxList($menu_id)
	{
		return $this->adminMenuAjaxs->getAdminMenuAjaxList($menu_id);
	}

	/**Ajax 메뉴 등록한다.
	 * @param AdminMenuAjaxDto $menuAjaxDto
	 */
	public function insertMenuAjax($menuAjaxDto)
	{
		$this->startTransaction();

		$this->_validateMenuAjax((array)$menuAjaxDto);
		$this->adminMenuAjax->insertAdminMenuAjax($menuAjaxDto);

		$this->endTransaction();
	}

	/**Ajax 메뉴 수정한다.
	 * @param AdminMenuAjaxDto $menuAjaxDto
	 * @throws MsgException
	 */
	public function updateMenuAjax($menuAjaxDto)
	{
		$this->assertAjaxMenuArray($menuAjaxDto);

		$this->startTransaction();

		foreach ($menuAjaxDto->menu_ajax_list as $menu_ajax) {
			$this->_validateMenuAjax($menu_ajax);
			$this->adminMenuAjax->updateAdminMenuAjax($menu_ajax);
		}

		$this->endTransaction();
	}

	/**Ajax 메뉴 삭제한다.
	 * @param $menuAjaxDto
	 */
	public function deleteMenuAjax($menuAjaxDto)
	{
		$this->assertAjaxMenuArray($menuAjaxDto);

		$this->startTransaction();

		foreach ($menuAjaxDto->menu_ajax_list as $menu_ajax) {
			$this->_validateMenuAjax($menu_ajax);
			$this->adminMenuAjax->deleteAdminMenuAjax($menu_ajax);
		}

		$this->endTransaction();
	}

	/**
	 * @param AdminMenuAjaxDto $menu_ajax_dto
	 * @throws MsgException
	 */
	private function assertAjaxMenuArray($menu_ajax_dto)
	{
		if (count($menu_ajax_dto->menu_ajax_list) === 0) {
			throw new MsgException('수정할 Ajax 메뉴 URL이 없습니다.');
		}
	}

	/**메뉴 입력값 검사
	 * @param array $menuArray
	 */
	private function _validateMenu($menuArray)
	{
		ValidationUtils::checkNullField($menuArray['menu_title'], '메뉴 제목을 입력하여 주십시오.');
		ValidationUtils::checkNullField($menuArray['menu_url'], '메뉴 URL을 입력하여 주십시오.');
		ValidationUtils::checkNumberField($menuArray['menu_deep'], '메뉴 깊이는 숫자만 입력 가능합니다.');
	}

	/**메뉴 Ajax 입력값 검사
	 * @param array $menuAjaxArray
	 */
	private function _validateMenuAjax($menuAjaxArray)
	{
		ValidationUtils::checkNullField(
			$menuAjaxArray['menu_id'],
			'잘못된 메뉴 ID 입니다.' . ' / ' . $menuAjaxArray['menu_id']
		);
		ValidationUtils::checkNullField($menuAjaxArray['ajax_url'], '메뉴 Ajax URL을 입력하여 주십시오.');
	}
}
