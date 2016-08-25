<?php

namespace Ridibooks\Platform\Cms\Auth\Dto;

use Ridibooks\Platform\Common\Base\AdminBaseDto;
use Symfony\Component\HttpFoundation\Request;

/**유저 권한 관리 등록 / 수정을 위한 dto class
 * Class AdminUserAuthDto
 * @package Ridibooks\Platform\Cms\Auth\Dto
 * @todo import export 구현
 */
class AdminUserAuthDto extends AdminBaseDto
{
	public $tag_ids; //태그 id array
	public $menu_ids; //메뉴 id array
	public $partner_cp_ids; //제휴 CP id array
	public $operator_cp_ids; //운영 CP id array
	public $production_cp_ids; //제작 CP id array

	public function importFromRequest($request)
	{
		parent::importFromRequest($request);

		$this->tag_ids = static::parseAsIntArray($this->tag_ids);
		$this->menu_ids = static::parseAsIntArray($this->menu_ids);
		$this->partner_cp_ids = static::parseAsIntArray($this->partner_cp_ids);
		$this->operator_cp_ids = static::parseAsIntArray($this->operator_cp_ids);
		$this->production_cp_ids = static::parseAsIntArray($this->production_cp_ids);
	}

	private static function parseAsIntArray($string)
	{
		$array = array_map('intval', explode(",", $string));
		return array_filter(array_unique($array));
	}
}
