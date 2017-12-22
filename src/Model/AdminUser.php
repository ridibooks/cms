<?php
namespace Ridibooks\Cms\Model;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class AdminUser extends Model
{
    protected $table = 'tb_admin2_user';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'passwd',
        'name',
        'team',
        'is_use',
        'reg_date',
    ];

    protected $casts = [
        'id' => 'string'
    ];

    public function tags()
    {
        return $this->belongsToMany(
            AdminTag::class,
            'tb_admin2_user_tag',
            'user_id',
            'tag_id'
        );
    }

    public function menus()
    {
        return $this->belongsToMany(
            AdminMenu::class,
            'tb_admin2_user_menu',
            'user_id',
            'menu_id'
        );
    }

    static public function selectUserMenus($user, $column)
    {
        // menu -> tag -> user
        $user_tag_menus = DB::select('select *
            from tb_admin2_menu
            join tb_admin2_tag_menu on tb_admin2_tag_menu.menu_id = tb_admin2_menu.id
            join tb_admin2_user_tag on tb_admin2_user_tag.tag_id = tb_admin2_tag_menu.tag_id
            where tb_admin2_user_tag.user_id = :user', ['user' => $user]);

        // menu -> user
        $user_menus = DB::select('select *
            from tb_admin2_menu
            join tb_admin2_user_menu on tb_admin2_user_menu.menu_id = tb_admin2_menu.id
            where tb_admin2_user_menu.user_id = :user', ['user' => $user]);

        $menus = array_merge($user_tag_menus, $user_menus);

        usort($menus, function ($left, $right) {
            return ($left->menu_order < $right->menu_order) ? -1 : 1;
        });

        return array_map(function ($menu) use ($column) {
            return isset($column) ? $menu->{$column} : (array) $menu;
        }, $menus);
    }

    static public function selectUserAjaxList($user, $column)
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
}
