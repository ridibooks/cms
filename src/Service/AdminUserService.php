<?php
namespace Ridibooks\Cms\Service;

use Ridibooks\Cms\Auth\PasswordService;
use Ridibooks\Cms\Model\AdminUser;
use Ridibooks\Cms\Thrift\AdminUser\AdminUser as ThriftAdminUser;
use Ridibooks\Cms\Thrift\AdminUser\AdminUserServiceIf;

class AdminUserService implements AdminUserServiceIf
{
    /**
     * 사용 가능한 모든 Admin 계정정보 가져온다.
     */
    public function getAllAdminUserArray()
    {
        $users = AdminUser::select(['id', 'name'])->where('is_use', 1)->get();
        return $users->map(function ($user) {
            return new ThriftAdminUser($user->toArray());
        })->all();
    }

    public function getUser($id)
    {
        /** @var AdminUser $user */
        $user = AdminUser::find($id);
        if (!$user) {
            return new ThriftAdminUser();
        }
        return new ThriftAdminUser($user->toArray());
    }

    public function getAdminUserTag($user_id)
    {
        /** @var AdminUser $user */
        $user = AdminUser::find($user_id);
        if (!$user) {
            return [];
        }

        return $user->tags->pluck('id')->all();
    }

    public function getAdminUserMenu($user_id, $column = 'id')
    {
        /** @var AdminUser $user */
        $user = AdminUser::find($user_id);
        if (!$user) {
            return [];
        }

        return $user->menus->pluck($column)->all();
    }

    public function getAdminUserMenuAjax($user_id, $column = 'id')
    {
        /** @var AdminUser $user */
        $user = AdminUser::with('menus.ajaxMenus')->find($user_id);
        if (!$user) {
            return [];
        }

        return $user->menus->map(function ($menu) use ($column) {
            return $menu->ajaxMenus->pluck($column);
        })->collapse()->all();
    }

    public function getAllMenuIds($user_id)
    {
        return $this->getAllMenus($user_id, 'id');
    }

    public function getAllMenus($user_id, $column = null)
    {
        return AdminUser::selectUserMenus($user_id, $column);
    }

    public function getAllMenuAjaxList($user_id, $column = null)
    {
        return AdminUser::selectUserAjaxList($user_id, $column);
    }

    public function updateMyInfo($name, $team, $is_use, $passwd = '')
    {
        /** @var AdminUser $admin */
        $me = AdminUser::find(LoginService::GetAdminID());
        if (!$me) {
            return false;
        }

        $filler = [
            'name' => $name,
            'team' => $team,
            'is_use' => $is_use
        ];

        if (!empty($passwd)) {
            $filler['passwd'] = PasswordService::getPasswordAsHashed($passwd);
        }

        $me->fill($filler);
        $me->save();

        return true;
    }

    public function updatePassword($user_id, $plain_password)
    {
        $me = AdminUser::find($user_id);
        if (!$me) {
            return false;
        }

        $me->passwd = PasswordService::getPasswordAsHashed($plain_password);
        $me->save();

        return true;
    }

    public function addNewUser($user_id, $name, $team)
    {
        AdminUser::create([
            'id' => $user_id,
            'passwd' => '',
            'name' => $name,
            'team' => $team,
            'is_use' => 1,
        ]);
    }
}
