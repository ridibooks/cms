<?php
namespace Ridibooks\Platform\Cms\Model;

use Illuminate\Database\Eloquent\Model;

class AdminMenu extends Model
{
	protected $table = 'tb_admin2_menu';

	protected $fillable = [
		'menu_title',
		'menu_url',
		'menu_deep',
		'menu_order',
		'is_use',
		'is_show',
		'reg_date',
		'is_newtab'
	];

	protected $casts = [
		'menu_deep' => 'integer',
		'menu_order' => 'integer',
		'is_use' => 'boolean',
		'is_show' => 'boolean',
		'is_newtab' => 'boolean'
	];

	public function users()
	{
		return $this->belongsToMany(
			AdminUser::class,
			'tb_admin2_user_menu',
			'menu_id',
			'user_id'
		);
	}

	public function ajaxMenus()
	{
		return $this->hasMany(AdminMenuAjax::class, 'menu_id');
	}
}
