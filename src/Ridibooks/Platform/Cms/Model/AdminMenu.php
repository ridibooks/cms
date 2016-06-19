<?php
namespace Ridibooks\Platform\Cms\Model;

use Illuminate\Database\Eloquent\Model;

class AdminMenu extends Model
{
	protected $table = 'tb_admin2_menu';

	public $timestamps = false;

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
}
