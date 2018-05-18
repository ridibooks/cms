<?php

namespace Ridibooks\Cms\Thrift;

use Ridibooks\Cms\Service\AdminUserService;
use Ridibooks\Cms\Thrift\AdminUser\AdminUserServiceIf;

class AdminUserThrift implements AdminUserServiceIf
{
    /** @var AdminUserService $server */
    private $server = null;

    public function __construct(AdminUserService $server)
    {
        $this->server = $server;
    }

    public function getAllAdminUserArray()
    {
        return $this->server->getAllAdminUserArray();
    }

    public function getUser($userId)
    {
        return $this->server->getUser($userId);
    }

    public function getAdminUserTag($userId)
    {
        return $this->server->getAdminUserTag($userId);
    }

    public function getAdminUserMenu($userId)
    {
        return $this->server->getAdminUserMenu($userId);
    }

    public function getAllMenuIds($userId)
    {
        return $this->server->getAllMenuIds($userId);
    }

    public function updateMyInfo($name, $team, $isUse, $passwd)
    {
        return $this->server->updateMyInfo($name, $team, $isUse, $passwd);
    }

    public function updatePassword($userId, $plainPassword)
    {
        return $this->server->updatePassword($userId, $plainPassword);
    }
}
