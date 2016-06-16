<?php
namespace Ridibooks\Platform\Cms\Auth\Model;

use Ridibooks\Platform\Common\Base\AdminBaseModel;

class AdminUser extends AdminBaseModel
{
	public function insertAdminUser($adminUserDto)
	{
		$column_array = [];
		$column_array['id'] = $adminUserDto->id;
		$column_array['passwd'] = $adminUserDto->passwd;
		$column_array['name'] = $adminUserDto->name;
		$column_array['team'] = $adminUserDto->team;
		$column_array['is_use'] = $adminUserDto->is_use;
		$this->db->sqlInsert("tb_admin2_user", $column_array);
	}

	/**Admin 계정 정보 수정한다.
	 * @param \Ridibooks\Platform\Cms\Auth\Dto\AdminUserDto $adminUserDto
	 */
	public function updateAdminUser($adminUserDto)
	{
		$column_array = [];
		if (isset($adminUserDto->passwd) && trim($adminUserDto->passwd) !== '') {
			$column_array['passwd'] = $adminUserDto->passwd;
		}
		$column_array['name'] = $adminUserDto->name;
		$column_array['team'] = $adminUserDto->team;
		$column_array['is_use'] = $adminUserDto->is_use;
		$this->db->sqlUpdate('tb_admin2_user', $column_array, ['id' => $adminUserDto->id]);
	}

	/**Admin 계정 ID를 수정한다.
	 * @param string $old_id
	 * @param string $new_id
	 */
	public function updateAdminID($old_id, $new_id)
	{
		$column_array = [];
		$column_array['id'] = $new_id;
		$this->db->sqlUpdate('tb_admin2_user', $column_array, ['id' => $old_id]);
	}

	public function deleteAdmin($admin_id)
	{
		$this->db->sqlDelete('tb_admin2_user', ['id' => $admin_id]);
	}
}
