<?php

namespace Ridibooks\Platform\Cms\Auth\Dto;

use Ridibooks\Platform\Common\Base\AdminBaseDto;

/**Admin Menu Ajax 등록 / 수정을 위한 Dto
 * Class MenuAjaxDto
 * @package Ridibooks\Platform\Cms\Auth\Dto
 */
class AdminMenuAjaxDto extends AdminBaseDto
{
	public $menu_id; //메뉴 ID
	public $ajax_url; //Ajax Url
	public $ajax_auth; //Ajax auth

	public $menu_ajax_list;
}
