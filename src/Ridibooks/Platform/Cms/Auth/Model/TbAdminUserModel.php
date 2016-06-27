<?php

namespace Ridibooks\Platform\Cms\Auth\Model;

use Ridibooks\Platform\Common\Base\AdminBaseModel;

/**
 * tb_admin_user 의 조회용 model
 * Class AdminUsers
 * @package Ridibooks\Platform\Cms\Auth\Model
 */
class TbAdminUserModel extends AdminBaseModel
{
	/**
	 * 전체 팀 리스트를 가져온다. - Sunghoon (14.12.11)
	 * @return array
	 */
	public static function getTeamList()
	{
		return self::getDb()->sqlObjects("SELECT DISTINCT team FROM tb_admin2_user");
	}


	/**
	 * 팀 멤버 리스트를 가져온다.
	 * @param array $teams
	 * @return array
	 */
	public static function getTeamMemberList($teams)
	{
		return self::getReadDb()->sqlDicts(
			"SELECT id, name FROM tb_admin2_user WHERE ?",
			sqlWhere(['team' => $teams])
		);
	}
}
