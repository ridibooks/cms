<?php

namespace Ridibooks\Platform\Cms\Auth\Dto;

use Ridibooks\Platform\Common\Base\AdminBaseDto;
use Symfony\Component\HttpFoundation\Request;

class AdminUserAuthDto extends AdminBaseDto
{
	public $tag_ids;
	public $menu_ids;

	public function importFromRequest($request)
	{
		parent::importFromRequest($request);

		$this->tag_ids = static::parseAsIntArray($this->tag_ids);
		$this->menu_ids = static::parseAsIntArray($this->menu_ids);
	}

	private static function parseAsIntArray($string)
	{
		$array = array_map('intval', explode(",", $string));
		return array_filter(array_unique($array));
	}
}
