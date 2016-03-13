<?php
namespace Ridibooks\Platform\Cms\Auth\Dto;

use Ridibooks\Platform\Common\Base\AdminBaseDto;

/**메뉴 등록 / 수정을 위한 Dto
 * Class MenuDto
 * @package Ridibooks\Platform\Cms\Auth\Dto
 */
class AdminMenuDto extends AdminBaseDto
{
	public $menu_title; // 메뉴 제목
	public $menu_url; //메뉴 url
	public $menu_order; //메뉴 순서
	public $menu_deep; //메뉴 깊이
	public $is_newtab; //새탭 여부
	public $is_use; //사용 여부
	public $is_show; //노출 여부

	public $menu_list;

	/**
	 * @param mixed $param
	 */
	public function __construct($param)
	{
		parent::__construct($param);
		$this->menu_deep = $this->menu_deep ? $this->menu_deep : 0;
	}
}
