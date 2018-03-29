<?php
namespace Ridibooks\Cms\Service;

use Illuminate\Database\Capsule\Manager as DB;
use Ridibooks\Cms\Auth\PasswordService;
use Ridibooks\Cms\Model\AdminUser;
use Ridibooks\Cms\Thrift\AdminUser\AdminUser as ThriftAdminUser;
use Ridibooks\Cms\Thrift\AdminUser\AdminUserServiceIf;

class AdminUserService implements AdminUserServiceIf
{
    /**
     * 사용 가능한 모든 Admin 계정정보 가져온다.
     */
    public function getAllAdminUserArray(): array
    {
        $users = AdminUser::select(['id', 'name'])->where('is_use', 1)->get();
        return $users->map(function ($user) {
            return new ThriftAdminUser($user->toArray());
        })->all();
    }

    public function getUser($id): ThriftAdminUser
    {
        /** @var AdminUser $user */
        $user = AdminUser::find($id);
        if (!$user) {
            return new ThriftAdminUser();
        }
        return new ThriftAdminUser($user->toArray());
    }

    public function getAdminUserTag($user_id): array
    {
        /** @var AdminUser $user */
        $user = AdminUser::find($user_id);
        if (!$user) {
            return [];
        }

        return $user->tags->pluck('id')->all();
    }

    public function getAdminUserMenu($user_id, $column = 'id'): array
    {
        /** @var AdminUser $user */
        $user = AdminUser::find($user_id);
        if (!$user) {
            return [];
        }

        return $user->menus->pluck($column)->all();
    }

    public function getAdminUserMenuAjax($user_id, $column = 'id'): array
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

    public function getAllMenuIds($user_id): array
    {
        return $this->getAllMenus($user_id, 'id');
    }

    public function getAllMenus($user_id, $column = null): array
    {
        $menuService = new AdminMenuService();
        $rootMenus = $menuService->getRootMenus($column);
        $userMenus = $this->selectUserMenus($user_id, $column);

        $menus = array_merge($rootMenus, $userMenus);
        usort($menus, function ($left, $right) {
            $left_order = $left['menu_order'] ?? 0;
            $right_order = $right['menu_order'] ?? 0;
            return $left_order - $right_order;
        });

        return $menus;
    }

    public function getAllMenuAjaxList($user_id, $column = null): array
    {
        return $this->selectUserAjaxList($user_id, $column);
    }

    public function updateMyInfo($name, $team, $is_use, $passwd = ''): bool
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

    public function updatePassword($user_id, $plain_password): bool
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

    private function selectUserMenus(string $user, ?string $column = null): array
    {
        // menu -> tag -> user
        $user_tag_menus = DB::select('select tb_admin2_menu.*
            from tb_admin2_menu
            join tb_admin2_tag_menu on tb_admin2_tag_menu.menu_id = tb_admin2_menu.id
            join tb_admin2_user_tag on tb_admin2_user_tag.tag_id = tb_admin2_tag_menu.tag_id
            where tb_admin2_user_tag.user_id = :user', ['user' => $user]);

        // menu -> user
        $user_menus = DB::select('select tb_admin2_menu.*
            from tb_admin2_menu
            join tb_admin2_user_menu on tb_admin2_user_menu.menu_id = tb_admin2_menu.id
            where tb_admin2_user_menu.user_id = :user', ['user' => $user]);

        $menus = array_merge($user_tag_menus, $user_menus);
        $menus = self::uniquifyMenus($menus);

        return array_map(function ($menu) use ($column) {
            return isset($column) ? $menu->{$column} : (array) $menu;
        }, $menus);
    }

    private function selectUserAjaxList(string $user, ?string $column = null): array
    {
        // ajax -> menu -> tag -> user
        $user_tag_ajax_list = DB::select('select *
            from tb_admin2_menu_ajax
            join tb_admin2_menu on tb_admin2_menu_ajax.menu_id = tb_admin2_menu.id
            join tb_admin2_tag_menu on tb_admin2_tag_menu.menu_id = tb_admin2_menu.id
            join tb_admin2_user_tag on tb_admin2_user_tag.tag_id = tb_admin2_tag_menu.tag_id
            where tb_admin2_user_tag.user_id = :user', ['user' => $user]);

        // ajax -> menu -> user
        $user_menu_ajax_list = DB::select('select *
            from tb_admin2_menu_ajax
            join tb_admin2_menu on tb_admin2_menu_ajax.menu_id = tb_admin2_menu.id
            join tb_admin2_user_menu on tb_admin2_user_menu.menu_id = tb_admin2_menu.id
            where tb_admin2_user_menu.user_id = :user', ['user' => $user]);

        $ajax_list = array_merge($user_tag_ajax_list, $user_menu_ajax_list);

        return array_map(function ($ajax) use ($column) {
            return isset($column) ? $ajax->{$column} : (array) $ajax;
        }, $ajax_list);
    }

    public function uniquifyMenus(array $menus)
    {
        $unique = [];
        foreach ($menus as $menu) {
            $id = $menu->id;
            if (!isset($unique[$id])) {
                $unique[$id] = $menu;
            }
        }
        return $unique;
    }
}
