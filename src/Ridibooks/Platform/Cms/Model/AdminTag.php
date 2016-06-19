<?php
namespace Ridibooks\Platform\Cms\Model;

use Illuminate\Database\Eloquent\Model;

class AdminTag extends Model
{
	protected $table = 'tb_admin2_tag';

	public $timestamps = false;

	protected $fillable = [
		'id',
		'name',
		'is_use',
		'reg_date',
	];

	public function users()
	{
		return $this->belongsToMany(
			'Ridibooks\Platform\Cms\Model\AdminUser',
			'tb_admin2_user_tag',
			'tag_id',
			'user_id'
		);
	}
}
