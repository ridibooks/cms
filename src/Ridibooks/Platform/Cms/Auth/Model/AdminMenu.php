<?php

namespace Ridibooks\Platform\Cms\Auth\Model;

use Ridibooks\Platform\Common\Base\AdminBaseModel;

/**tb_admin_menu의 insert, update, delete용 model
 * Class AdminMenu
 * @package Ridibooks\Platform\Cms\Auth\Model
 */
class AdminMenu extends AdminBaseModel
{
	/**메뉴 등록한다.
	 * @param \Ridibooks\Platform\Cms\Auth\Dto\AdminMenuDto $menuDto
	 */
	public function insertMenu($menuDto)
	{
		$column_array = array();
		$column_array['menu_title'] = $menuDto->menu_title;
		$column_array['menu_url'] = $menuDto->menu_url;
		$column_array['menu_order'] = $menuDto->menu_order;
		$column_array['menu_deep'] = $menuDto->menu_deep;
		$column_array['is_newtab'] = $menuDto->is_newtab;
		$column_array['is_use'] = $menuDto->is_use;
		$column_array['is_show'] = $menuDto->is_show;

		$this->db->sqlInsert("tb_admin2_menu", $column_array);
	}

	/**메뉴 수정한다.
	 * @param array $menuArray
	 */
	public function updateMenu($menuArray)
	{
		$this->db->sqlUpdate("tb_admin2_menu", $menuArray, array('id' => $menuArray['id']));
	}

	/**메뉴 순서를 변경한다.
	 * @param int $menu_order
	 */
	public function updateMenuOrder($menu_order)
	{
		$sql = "UPDATE tb_admin2_menu SET menu_order = menu_order + 1 WHERE menu_order >= ?";
		$this->db->sqlDo($sql, $menu_order);
	}

	/**아래에 있는 메뉴를 위로 올린다.
	 * @param int $old_menu_order 기존 menu_order
	 * @param int $new_menu_order 변경할 menu_order
	 */
	public function updateMenuOrderUpper($old_menu_order, $new_menu_order)
	{
		$sql = "UPDATE tb_admin2_menu SET menu_order = menu_order + 1 WHERE menu_order < ? AND menu_order >= ?";
		$this->db->sqlDo($sql, $old_menu_order, $new_menu_order);
	}

	/**위에 있는 메뉴를 아래로 내린다.
	 * @param int $old_menu_order 기존 menu_order
	 * @param int $new_menu_order 변경할 menu_order
	 */
	public function updateMenuOrderLower($old_menu_order, $new_menu_order)
	{
		$sql = "UPDATE tb_admin2_menu SET menu_order = menu_order - 1 WHERE menu_order  > ? AND menu_order <= ?";
		$this->db->sqlDo($sql, $old_menu_order, $new_menu_order);
	}
}
