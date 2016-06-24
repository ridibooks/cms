<?php

namespace Ridibooks\Platform\Cms\Auth\Model;

use Ridibooks\Platform\Cms\Auth\AdminUserService;
use Ridibooks\Platform\Common\Base\AdminBaseModel;

/**
 * tb_admin_user 의 조회용 model
 * Class AdminUsers
 * @package Ridibooks\Platform\Cms\Auth\Model
 */
class TbAdminUserModel extends AdminBaseModel
{
	/**
	 * 해당 Id의 Admin 계정정보 갯수 가져온다.
	 * @param string $id
	 * @return int id count
	 */
	public static function getAdminUserIdCount($id)
	{
		return self::getDb()->sqlData(
			"SELECT COUNT(*) FROM tb_admin2_user WHERE ?",
			sqlWhere(['id' => $id])
		);
	}

	/**
	 * 사용되고 있는 모든 Admin 계정ID, 이름 가져온다.
	 * @return array
	 */
	public static function getAllAdminUserArray()
	{
		return self::getDb()->sqlObjects(
			"SELECT id, name FROM tb_admin2_user WHERE ?",
			sqlWhere(['is_use' => 1])
		);
	}

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

	/**
	 * TODO Service단으로 옮길것
	 * @param array $teams
	 * @return array
	 */
	public static function getTeamMemberMaillingList($teams)
	{
		$admin_mails = [];
		$admin_user_array = TbAdminUserModel::getTeamMemberList($teams);
		foreach ($admin_user_array as $admin_user) {
			$admin_mails[] = AdminUserService::appendEmailAddress($admin_user['id']);
		}

		return $admin_mails;
	}
}
