<?php
namespace Ridibooks\Platform\Cms\Model;

use Illuminate\Database\Eloquent\Model;

class AdminTag extends Model
{
	protected $table = 'tb_admin2_tag';

	protected $fillable = [
		'id',
		'name',
		'is_use',
		'creator'
	];

	protected $casts = [
		'is_use' => 'boolean',
		'users_count' => 'integer',
		'menus_count' => 'integer'
	];

	public function users()
	{
		return $this->belongsToMany(
			AdminUser::class,
			'tb_admin2_user_tag',
			'tag_id',
			'user_id'
		);
	}

	public function menus()
	{
		return $this->belongsToMany(
			AdminMenu::class,
			'tb_admin2_tag_menu',
			'tag_id',
			'menu_id'
		);
	}
}
