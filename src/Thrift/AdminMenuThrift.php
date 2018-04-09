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

    public function getMenuList($isUse)
    {
        return $this->server->getMenuList($isUse);
    }

    public function getAllMenuList()
    {
        return $this->server->getAllMenuList();
    }

    public function getAllMenuAjax()
    {
        return $this->server->getAllMenuAjax();
    }

    public function getMenus(array $menuIds)
    {
        return $this->server->getMenus($menuIds);
    }

    public function getAdminIdsByMenuId($menuId)
    {
        return $this->server->getAdminIdsByMenuId($menuId);
    }

    public function getAllUserIds($menuId)
    {
        return $this->server->getAllUserIds($menuId);
    }
}
