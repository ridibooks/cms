<?php
namespace Ridibooks\Platform\Cms\Model;

use Illuminate\Database\Eloquent\Model;

class AdminMenuAjax extends Model
{
	protected $table = 'tb_admin2_menu_ajax';

	public $timestamps = false;

	protected $fillable = [
		'menu_id',
		'ajax_url'
	];

	protected $casts = [
		'menu_id' => 'integer',
	];
}
