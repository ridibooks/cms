<?php
namespace Ridibooks\Platform\Cms\Dto;

use Ridibooks\Platform\Common\Base\AdminBaseDto;

class AdminUserDto extends AdminBaseDto
{
	public $name; //이름
	public $team; //팀
	public $is_use; //사용여부
	public $passwd; //계정 비밀번호
	public $new_passwd; //새로 입력한 비밀번호
	public $chk_passwd; //새로 입력한 비밀번호 확인
	public $last_id; //변경전 아이디
}
