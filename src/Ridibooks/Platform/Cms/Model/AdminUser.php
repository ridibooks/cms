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
}
