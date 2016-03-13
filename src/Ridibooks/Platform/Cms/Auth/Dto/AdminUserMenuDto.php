<?php

namespace Ridibooks\Platform\Cms\Auth\Dto;

use Ridibooks\Platform\Common\Base\AdminBaseDto;

/**Admin User Menu 등록 / 수정을 위한 Dto
 * Class AdminUserMenuDto
 * @package Ridibooks\Platform\Cms\Auth\Dto
 */
class AdminUserMenuDto extends AdminBaseDto
{
	public $user_id; //계정 ID
	public $menu_id; //메뉴 ID

	public function __construct($user_id, $menu_id)
	{
		$this->user_id = $user_id;
		$this->menu_id = $menu_id;
	}
}
