<?php
namespace Ridibooks\Platform\Cms\Dto;

use Ridibooks\Platform\Common\Base\AdminBaseDto;

class AdminMenuAjaxDto extends AdminBaseDto
{
	public $menu_id; //메뉴 ID
	public $ajax_url; //Ajax Url
	public $ajax_auth; //Ajax auth

	public $menu_ajax_list;
}
