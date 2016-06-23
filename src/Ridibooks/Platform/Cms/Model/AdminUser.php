<?php
namespace Ridibooks\Platform\Cms\Model;

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
}
