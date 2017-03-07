<?php
namespace Ridibooks\Platform\Cms\Auth;

use Ridibooks\Cms\Thrift\ThriftService;

class AdminMenuService
{
	private static $client = null;

	private static function getTClient()
	{
		if (!self::$client) {
			self::$client = ThriftService::getHttpClient('AdminMenu');
		}

		return self::$client;
	}

	public static function getMenuList($is_use = null)
	{
		if (!is_null($is_use)) {
			$menus = self::getTClient()->getMenuList($is_use);
		} else {
			$menus = self::getTClient()->getAllMenuList();
		}
		return ThriftService::convertMenuCollectionToArray($menus);
	}

	public static function getAllMenuAjax()
	{
		$menus = self::getTClient()->getAllMenuAjax();
		return ThriftService::convertMenuAjaxCollectionToArray($menus);
	}

	public static function getMenus(array $menu_ids): array
	{
		$menus = self::getTClient()->getMenus($menu_ids);
		return ThriftService::convertMenuCollectionToArray($menus);
	}

	public static function getAdminIdsByMenuId($menu_id)
	{
		return self::getTClient()->getAdminIdsByMenuId($menu_id);
	}
}
