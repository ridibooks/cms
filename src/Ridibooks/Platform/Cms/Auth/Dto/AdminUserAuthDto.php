<?php

namespace Ridibooks\Platform\Cms\Auth\Dto;

use Ridibooks\Platform\Common\Base\AdminBaseDto;

/**유저 권한 관리 등록 / 수정을 위한 dto class
 * Class AdminUserAuthDto
 * @package Ridibooks\Platform\Cms\Auth\Dto
 * @todo import export 구현
 */
class AdminUserAuthDto extends AdminBaseDto
{
	public $tag_id_array; //태그 id array
	public $menu_id_array; //메뉴 id array
	public $partner_cp_id_array; //제휴 CP id array
	public $operator_cp_id_array; //운영 CP id array
	public $production_cp_id_array; //제작 CP id array
}
