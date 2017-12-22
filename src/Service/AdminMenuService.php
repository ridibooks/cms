<?php
namespace Ridibooks\Cms\Service;

use Ridibooks\Cms\Model\AdminMenu;
use Ridibooks\Cms\Model\AdminMenuAjax;
use Ridibooks\Cms\Thrift\AdminMenu\AdminMenu as ThriftAdminMenu;
use Ridibooks\Cms\Thrift\AdminMenu\AdminMenuAjax as ThriftAdminMenuAjax;
use Ridibooks\Cms\Thrift\AdminMenu\AdminMenuServiceIf;

class AdminMenuService implements AdminMenuServiceIf
{
    public function getMenuList($is_use = null)
    {
        $menus_query = AdminMenu::query()->orderBy('menu_order');
        if (!is_null($is_use)) {
            $menus_query = $menus_query->where('is_use', $is_use);
        }

        $menus = $menus_query->get();
        return $menus->map(function ($menu) {
            return new ThriftAdminMenu($menu->toArray());
        })->all();
    }

    public function getRootMenus($column = null)
    {
        $menus = AdminMenu::query()
            ->where('menu_deep', 0)
            ->orderBy('menu_order')
            ->get();

        return $menus->map(function ($menu) use ($column) {
            return isset($column) ? $menu->{$column} : $menu->toArray();
        })->all();
    }

    public function getAllMenuList()
    {
        $menus = AdminMenu::query()
            ->orderBy('menu_order')->get();

        return $menus->map(function ($menu) {
            return new ThriftAdminMenu($menu->toArray());
        })->all();
    }

    public function getAllMenuAjax()
    {
        $menus = AdminMenuAjax::all();
        return $menus->map(function ($menu) {
            return new ThriftAdminMenuAjax($menu->toArray());
        })->all();
    }

    public function getMenus(array $menu_ids)
    {
        $menus = AdminMenu::findMany($menu_ids);
        return $menus->map(function ($menu) {
            return new ThriftAdminMenu($menu->toArray());
        })->all();
    }

    public function getMenuAjaxList(array $menu_ids)
    {
        $menus = AdminMenu::findMany($menu_ids);
        return $menus->map(function ($menu) {
            return $menu->ajaxMenus->toArray();
        })->collapse()->all();
    }

    public function getAdminIdsByMenuId($menu_id)
    {
        /** @var AdminMenu $menu */
        $menu = AdminMenu::find($menu_id);
        if (!$menu) {
            return [];
        }

        return $menu->users->pluck('id')->all();
    }

    public function getAllUserIds($menu_id)
    {
        /** @var AdminMenu $menu */
        $menu = AdminMenu::find($menu_id);
        if (!$menu) {
            return [];
        }

        // 1: menu.tags.users
        $tags_users = $menu->tags
            ->map(function ($tag) {
                return $tag->users->where('is_use', 1)->pluck('id');
            })
            ->collapse()
            ->all();

        // 2: menu:users
        $menu_users = $menu->users->where('is_use', 1)->pluck('id')->all();

        $user_ids = array_unique(array_merge($tags_users, $menu_users));
        return $user_ids;
    }
}
