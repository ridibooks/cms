<?php
namespace Ridibooks\Platform\Cms\Auth;

use Illuminate\Database\Capsule\Manager as DB;
use Ridibooks\Exception\MsgException;
use Ridibooks\Platform\Cms\Dto\AdminMenuDto;
use Ridibooks\Platform\Cms\Model\AdminMenu;
use Ridibooks\Platform\Cms\Model\AdminMenuAjax;
use Ridibooks\Platform\Common\ValidationUtils;

class AdminMenuService
{
	public static function getMenuList($is_use = null)
	{
		$query = AdminMenu::query();
		if (!is_null($is_use)) {
			$query->where('is_use', $is_use);
		}
		return $query->orderBy('menu_order')->get()->toArray();
	}

	public static function getAllMenuAjax()
	{
		return AdminMenuAjax::all()->toArray();
	}

	public static function getMenus(array $menu_ids) : array
	{
		return AdminMenu::findMany($menu_ids)->toArray();
	}

	public static function insertMenu(AdminMenuDto $menuDto)
	{
		DB::connection()->transaction(function () use ($menuDto) {
			self::_validateMenu((array)$menuDto);

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

	public static function updateMenu(AdminMenuDto $menuDto)
	{
		DB::connection()->transaction(function () use ($menuDto) {
			self::_validateMenu((array)$menuDto);

			/** @var AdminMenu $adminMenu */
			$adminMenu = AdminMenu::find($menuDto->id);
			$old_menu_order = $adminMenu->menu_order;
			$new_menu_order = $menuDto->menu_order;

			if ($new_menu_order == null) { // 입력받은 메뉴 순서값 없을 경우 메뉴 순서값을 max+1 해준다.
				$max_order = AdminMenu::max('menu_order');
				$menuDto->menu_order = $max_order + 1;
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

			$adminMenu->fill((array)$menuDto);
			$adminMenu->save();
		});
	}

	public static function getMenuAjaxList($menu_id)
	{
		return AdminMenu::find($menu_id)->ajaxMenus->toArray();
	}

	public static function insertMenuAjax($menu_id, $submenu_url)
	{
		self::_validateMenuAjax($menu_id, $submenu_url);

		$submenu = new AdminMenuAjax();
		$submenu->menu_id = $menu_id;
		$submenu->ajax_url = $submenu_url;
		$submenu->save();
	}

	public static function updateMenuAjax($menu_id, $submenu_id, $submenu_url)
	{
		self::_validateMenuAjax($menu_id, $submenu_url);
		AdminMenuAjax::find($submenu_id)->update(['ajax_url' => $submenu_url]);
	}

	public static function deleteMenuAjax($menu_id, $submenu_id)
	{
		/** @var AdminMenuAjax $submenu */
		$submenu = AdminMenuAjax::find($submenu_id);
		if (!$submenu) {
			throw new MsgException('존재하지 않는 서브메뉴입니다.');
		}
		if ($submenu->menu_id != $menu_id) {
			throw new MsgException('잘못된 서브메뉴입니다.');
		}
		$submenu->delete();
	}

	private static function _validateMenu(array $menuArray)
	{
		ValidationUtils::checkNullField($menuArray['menu_title'], '메뉴 제목을 입력하여 주십시오.');
		ValidationUtils::checkNullField($menuArray['menu_url'], '메뉴 URL을 입력하여 주십시오.');
		ValidationUtils::checkNumberField($menuArray['menu_deep'], '메뉴 깊이는 숫자만 입력 가능합니다.');
	}

	private static function _validateMenuAjax($menu_id, $ajax_url)
	{
		ValidationUtils::checkNullField($menu_id, '잘못된 메뉴 ID 입니다.' . ' / ' . $menu_id);
		ValidationUtils::checkNullField($ajax_url, '메뉴 Ajax URL을 입력하여 주십시오.');
	}

	public static function getAdminIdsByMenuId($menu_id)
	{
		/** @var AdminMenu $menu */
		$menu = AdminMenu::find($menu_id);
		if (!$menu) {
			return [];
		}

		return $menu->users->pluck('id')->all();
	}
}
