<?php

namespace Ridibooks\Platform\Cms\Auth\Dto;

use Ridibooks\Platform\Common\Base\AdminBaseDto;

/**Admin User Tag 등록 / 수정을 위한 Dto
 * Class AdminUserTagDto
 * @package Ridibooks\Platform\Cms\Auth\Dto
 */
class AdminUserTagDto extends AdminBaseDto
{
	public $user_id; //계정 ID
	public $tag_id; //태그 ID

	public function __construct($user_id, $tag_id)
	{
		$this->user_id = $user_id;
		$this->tag_id = $tag_id;
	}
}
