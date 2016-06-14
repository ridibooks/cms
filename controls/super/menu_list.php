<?php
use Ridibooks\Platform\Cms\Auth\MenuService;

$menuService = new MenuService();
//메뉴 리스트
$menu_list = $menuService->getMenuList();

return compact(
	'menu_list'
);
