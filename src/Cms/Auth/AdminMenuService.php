<?php
namespace Ridibooks\Platform\Cms\Auth;

use Ridibooks\Platform\Cms\Model\AdminMenu;
use Ridibooks\Platform\Cms\Model\AdminMenuAjax;

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
