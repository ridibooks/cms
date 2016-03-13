<?php

namespace Ridibooks\Platform\Cms\Auth\Model;

use Ridibooks\Platform\Common\Base\AdminBaseModel;

/**tb_admin_user_menu의 조회용 model
 * Class AdminUserMenus
 * @package Ridibooks\Platform\Cms\Auth\Model
 */
class AdminUserMenus extends AdminBaseModel
{
	/**해당 어드민에 매핑되어있는 메뉴 가져온다.
	 * @param string $user_id
	 * @return array
	 */
	public function getAdminUserMenuList($user_id)
	{
		return $this->db->sqlDatas("SELECT menu_id FROM tb_admin2_user_menu WHERE user_id = ?", $user_id);
	}

	/**해당 메뉴에 대한 권한을 가지고 있는 어드민을 가져온다.
	 * @param int $menu_id
	 * @return array
	 */
	public function getAdminIdsByMenuId($menu_id)
	{
		return $this->db->sqlDicts(
			"SELECT user_id
			FROM tb_admin2_user_menu
			?",
			sqlWhereWithClause(array('menu_id' => $menu_id))
		);
	}

	/**해당 어드민에 매핑되어있는 메뉴 유무를 위한 갯수 가져온다.
	 * @param string $user_id
	 * @param string $menu_id
	 * @return int
	 */
	public function getAdminUserMenuCount($user_id, $menu_id)
	{
		$whereArray = array();
		$whereArray['user_id'] = $user_id;
		$whereArray['menu_id'] = $menu_id;
		return (int)$this->db->sqlData("SELECT COUNT(*) FROM tb_admin2_user_menu ?", sqlWhereWithClause($whereArray));
	}

	/**메뉴들의 기존 계정 ID를 새로운 계정 ID로 모두 변경한다.
	 * @param string $old_id
	 * @param string $new_id
	 */
	public function updateUserOfMenus($old_id, $new_id)
	{
		$column_array = array();
		$column_array['user_id'] = $new_id;
		$this->db->sqlUpdate('tb_admin2_user_menu', $column_array, array('user_id' => $old_id));
	}
}
