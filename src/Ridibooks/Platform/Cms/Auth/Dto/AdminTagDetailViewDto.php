<?php

namespace Ridibooks\Platform\Cms\Auth\Dto;

use Ridibooks\Platform\Common\Base\AdminBaseDto;

/**Tag 등록 / 수정을 위한 Dto
 * Class TagDto
 * @package Ridibooks\Platform\Cms\Auth\Dto
 */
class AdminTagDetailViewDto extends AdminBaseDto
{
	public $id;
	public $name;
	public $is_use;
	public $count_menu;
	public $count_user;

	public static function importFromDatabaseRow($tag_row, $user_count, $menu_count)
	{
		$return = new self;
		$return->id = $tag_row['id'];
		$return->name = $tag_row['name'];
		$return->is_use = $tag_row['is_use'];
		$return->count_user = $user_count;
		$return->count_menu = $menu_count;
		return $return;
	}
}
