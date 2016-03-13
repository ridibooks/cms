<?php

namespace Ridibooks\Platform\Cms\Auth\Model;

use Ridibooks\Platform\Common\Base\AdminBaseModel;

/**tb_admin_user_menu 의 등록 / 수정 을 위한 model
 * Class AdminUserMenu
 * @package Ridibooks\Platform\Cms\Auth\Model
 */
class AdminUserMenu extends AdminBaseModel
{
	/**어드민 계정에 태그를 매핑한다.
	 * @param \Ridibooks\Platform\Cms\Auth\Dto\AdminUserMenuDto $adminUserMenuDto
	 */
	public function insertAdminUserMenu($adminUserMenuDto)
	{
		$column_array = array();
		$column_array['user_id'] = $adminUserMenuDto->user_id;
		$column_array['menu_id'] = $adminUserMenuDto->menu_id;
		$this->db->sqlInsert("tb_admin2_user_menu", $column_array);
	}

	/**어드민 계정에 매핑된 태그를 제거한다.
	 * @param string $user_id 어드민 유저 ID
	 */
	public function deleteAdminUserMenu($user_id)
	{
		$column_array = array();
		$column_array['user_id'] = $user_id;
		$this->db->sqlDelete('tb_admin2_user_menu', $column_array);
	}
}
