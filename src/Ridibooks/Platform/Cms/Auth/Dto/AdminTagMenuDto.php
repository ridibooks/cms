<?php

namespace Ridibooks\Platform\Cms\Auth\Dto;

use Ridibooks\Platform\Common\Base\AdminBaseDto;
use Symfony\Component\HttpFoundation\Request;

/**TagMenu 등록 / 수정을 위한 Dto
 * Class TagMenuDto
 * @package Ridibooks\Platform\Cms\Auth\Dto
 */
class AdminTagMenuDto extends AdminBaseDto
{
	public $tag_id; //tag_id
	public $menu_id; //menu_id
	public $tag_menu_list;

	public function __construct(Request $request)
	{
		$this->command = $request->get('command');
		$this->tag_id = $request->get('tag_id');
		$this->menu_id = $request->get('menu_id');

		if ($request->get('tag_menu_list') != null) {
			$this->tag_menu_list = $request->get('tag_menu_list');
		}
	}
}
