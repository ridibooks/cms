<?php
use Ridibooks\Platform\Cms\Auth\MenuService;

return [
	'title' => '메뉴 관리',
	'menu_list' => MenuService::getMenuList()
];
