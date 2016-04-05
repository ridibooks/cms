<?php

namespace Ridibooks\Platform\Cms\Auth\Dto;

use Ridibooks\Platform\Common\Base\AdminBaseDto;
use Symfony\Component\HttpFoundation\Request;

/**Tag 등록 / 수정을 위한 Dto
 * Class TagDto
 * @package Ridibooks\Platform\Cms\Auth\Dto
 */
class AdminTagDto extends AdminBaseDto
{
	public $name; //태그 이름
	public $is_use; //사용 여부
	public $tag_list;

	public function __construct(Request $request)
	{
		$this->id = $request->get('id');
		$this->command = $request->get('command');
		$this->name = $request->get('name');
		$this->is_use = $request->get('is_use');

		$this->tag_list = $request->get('tag_list', []);
	}
}
