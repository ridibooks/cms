<?php
use Ridibooks\Platform\Cms\Auth\AdminMenuService;

return [
	'title' => '메뉴 관리',
	'menu_list' => AdminMenuService::getMenuList()
];
