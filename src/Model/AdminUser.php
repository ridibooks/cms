<?php

namespace Ridibooks\Cms\Model;

use Illuminate\Database\Eloquent\Model;

class AdminUser extends Model
{
    protected $table = 'tb_admin2_user';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'passwd',
        'email',
        'name',
        'team',
        'is_use',
        'reg_date',
        'last_login_at',
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

    public function tags_group_joined()
    {
        return $this->belongsToMany(
            AdminTag::class,
            'v_admin2_user_tag_group_joined',
            'user_id',
            'tag_id'
        );
    }

    public function groups()
    {
        return $this->belongsToMany(
            AdminGroup::class,
            'tb_admin2_group_user',
            'user_id',
            'group_id'
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
}
