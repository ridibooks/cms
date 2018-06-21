<?php

namespace Ridibooks\Cms\Thrift;

use Ridibooks\Cms\Service\AdminMenuService;
use Ridibooks\Cms\Thrift\AdminMenu\AdminMenuServiceIf;

class AdminMenuThrift implements AdminMenuServiceIf
{
    /** @var AdminMenuService $server */
    private $server = null;

    public function __construct(AdminMenuService $server)
    {
        $this->server = $server;
    }

    public function getMenuList($is_use)
    {
        return $this->server->getMenuList($is_use);
    }

    public function getAllMenuList()
    {
        return $this->server->getAllMenuList();
    }

    public function getAllMenuAjax()
    {
        return $this->server->getAllMenuAjax();
    }

    public function getMenus(array $menu_ids)
    {
        return $this->server->getMenus($menu_ids);
    }

    public function getAdminIdsByMenuId($menu_id)
    {
        return $this->server->getAdminIdsByMenuId($menu_id);
    }

    public function getAllUserIds($menu_id)
    {
        return $this->server->getAllUserIds($menu_id);
    }
}
