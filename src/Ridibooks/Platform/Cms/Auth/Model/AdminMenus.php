<?php

namespace Ridibooks\Platform\Cms\Auth\Model;

use Ridibooks\Platform\Common\Base\AdminBaseModel;

/**tb_admin2_menu 의 조회용 model
 * Class AdminMenus
 * @package Ridibooks\Platform\Cms\Auth\Model
 */
class AdminMenus extends AdminBaseModel
{
	/**메뉴 리스트 가져온다.
	 * @param string $is_use 1 or 0
	 * @param string $search_text
	 * @return array
	 */
	public function getAdminMenuList($is_use = null, $search_text = null)
	{
		$where = array();
		if (!is_null($is_use)) {
			$where['is_use'] = $is_use;
		}
		if (!is_null($search_text)) {
			$where['menu_title'] = sqlLike($search_text);
		}
		return $this->db->sqlDicts("SELECT * FROM tb_admin2_menu ? ORDER BY menu_order", sqlWhereWithClause($where));
	}

	/**메뉴 순서 최대값 가져온다.
	 * @return int max
	 */
	public function getMaxMenuOrder()
	{
		return (int)$this->db->sqlData("SELECT MAX(menu_order) FROM tb_admin2_menu");
	}

	/**입력받은 메뉴 순서에 값이 있는지 체크한다.
	 * @param int $menu_order
	 * @return int
	 */
	public function getMenuOrderCount($menu_order)
	{
		return (int)$this->db->sqlData("SELECT COUNT(menu_order) FROM tb_admin2_menu WHERE menu_order=?", $menu_order);
	}

	/**menu_order 가져온다.
	 * @param string $id
	 * @return int menu_order
	 */
	public function getAdminMenuOrder($id)
	{
		return (int)$this->db->sqlData("SELECT menu_order FROM tb_admin2_menu WHERE id=?", $id);
	}

}
