<?php
namespace Ridibooks\Platform\Cms\Auth;

use Illuminate\Database\Capsule\Manager as DB;
use Ridibooks\Exception\MsgException;
use Ridibooks\Platform\Cms\Auth\Dto\AdminMenuAjaxDto;
use Ridibooks\Platform\Cms\Model\AdminMenu;
use Ridibooks\Platform\Cms\Model\AdminMenuAjax;
use Ridibooks\Platform\Common\ValidationUtils;

class MenuService
{
	public static function getMenuList($is_use = null)
	{
		$query = AdminMenu::query();
		if (!is_null($is_use)) {
			$query->where('is_use', $is_use);
		}
		return $query->orderBy('menu_order')->get()->toArray();
	}

	/**
	 * @param $menu_ids
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public static function getMenus($menu_ids)
	{
		return AdminMenu::findMany($menu_ids)->toArray();
	}

	/**메뉴 등록한다.
	 * @param \Ridibooks\Platform\Cms\Auth\Dto\AdminMenuDto $menuDto
	 */
	public function insertMenu($menuDto)
	{
		DB::connection()->transaction(function () use ($menuDto) {
			$this->_validateMenu((array)$menuDto);

			if ($menuDto->menu_order == null) { //메뉴 순서값이 없을 경우 메뉴 순서값을 max+1 해준다.
				$menuDto->menu_order = AdminMenu::max('menu_order') + 1;
			}

			ValidationUtils::checkNumberField($menuDto->menu_order, "메뉴 순서는 숫자만 입력 가능합니다.");

			// push down every menu below
			AdminMenu::where('menu_order', '>=', $menuDto->menu_order)
				->increment('menu_order');

			// then insert
			AdminMenu::create((array)$menuDto);
		});
	}

	/**메뉴 수정한다.
	 * @param \Ridibooks\Platform\Cms\Auth\Dto\AdminMenuDto $menuDto
	 */
	public function updateMenu($menuDto)
	{
		DB::connection()->transaction(function () use ($menuDto) {
			$max_order = AdminMenu::max('menu_order');

			foreach ($menuDto->menu_list as $menu) {
				$this->_validateMenu($menu);

				/** @var AdminMenu $adminMenu */
				$adminMenu = AdminMenu::find($menu['id']);
				$old_menu_order = $adminMenu->menu_order;
				$new_menu_order = $menu['menu_order'];

				if ($new_menu_order == null) { //입력받은 메뉴 순서값 없을 경우 메뉴 순서값을 max+1 해준다.
					$menu['menu_order'] = $max_order + 1;
				} else {
					ValidationUtils::checkNumberField($new_menu_order, "메뉴 순서는 숫자만 입력 가능합니다.");

					if (AdminMenu::where('menu_order', $new_menu_order)->first()) { //입력받은 메뉴 순서값이 이미 존재하고 있을 경우 메뉴 순서를 재 정렬할 필요가 있다.
						if ($old_menu_order > $new_menu_order) { //밑에 있는 메뉴를 위로 올릴때
							AdminMenu::where('menu_order', '<', $old_menu_order)
								->where('menu_order', '>=', $new_menu_order)
								->increment('menu_order');
						} elseif ($old_menu_order < $new_menu_order) { //위에 있는 메뉴를 아래로 내릴때
							AdminMenu::where('menu_order', '>', $old_menu_order)
								->where('menu_order', '<=', $new_menu_order)
								->decrement('menu_order');
						}
					}
				}

				$adminMenu->fill($menu);
				$adminMenu->save();
			}
		});
	}

	public function getMenuAjaxList($menu_id)
	{
		return AdminMenu::find($menu_id)->ajaxMenus->toArray();
	}

	public function insertMenuAjax($menuAjaxDto)
	{
		$this->_validateMenuAjax((array)$menuAjaxDto);
		AdminMenuAjax::create((array)$menuAjaxDto);
	}

	public function updateMenuAjax($menuAjaxDto)
	{
		$this->assertAjaxMenuArray($menuAjaxDto);

		DB::connection()->transaction(function () use ($menuAjaxDto) {
			foreach ($menuAjaxDto->menu_ajax_list as $menu_ajax) {
				$this->_validateMenuAjax($menu_ajax);
				AdminMenuAjax::find($menu_ajax['id'])->update(['ajax_url' => $menu_ajax['ajax_url']]);
			}
		});
	}

	public function deleteMenuAjax($menuAjaxDto)
	{
		$this->assertAjaxMenuArray($menuAjaxDto);

		DB::connection()->transaction(function () use ($menuAjaxDto) {
			foreach ($menuAjaxDto->menu_ajax_list as $menu_ajax) {
				$this->_validateMenuAjax($menu_ajax);
				AdminMenuAjax::destroy($menu_ajax['id']);
			}
		});
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
