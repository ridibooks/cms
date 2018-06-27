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

    public function getUser($user_id)
    {
        return $this->server->getUser($user_id);
    }

    public function getAdminUserTag($user_id)
    {
        return $this->server->getAdminUserTag($user_id);
    }

    public function getAdminUserMenu($user_id)
    {
        return $this->server->getAdminUserMenu($user_id);
    }

    public function getAllMenuIds($user_id)
    {
        return $this->server->getAllMenuIds($user_id);
    }

    public function updateMyInfo($name, $team, $is_use, $passwd)
    {
        // TODO: Fix Thfirt client to send user_id
        return $this->server->updateMyInfo(null, $name, $team, $is_use, $passwd);
    }

    public function updatePassword($user_id, $plain_password)
    {
        return $this->server->updatePassword($user_id, $plain_password);
    }
}
