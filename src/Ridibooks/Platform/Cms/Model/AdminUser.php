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
			'Ridibooks\Platform\Cms\Model\AdminTag',
			'tb_admin2_user_tag',
			'user_id',
			'tag_id'
		);
	}
}
