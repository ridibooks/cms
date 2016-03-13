<?php

namespace Ridibooks\Platform\Cms\Auth\Model;

use Ridibooks\Platform\Common\Base\AdminBaseModel;

/**tb_admin_menu_ajax의 insert, update, delete용 model
 * Class AdminMenuAjax
 * @package Ridibooks\Platform\Cms\Auth\Model
 */
class AdminMenuAjax extends AdminBaseModel
{
	/**메뉴 Ajax 등록한다.
	 * @param \Ridibooks\Platform\Cms\Auth\Dto\AdminMenuAjaxDto $menuAjaxDto
	 */
	public function insertAdminMenuAjax($menuAjaxDto)
	{
		$column_array = array();
		$column_array['menu_id'] = $menuAjaxDto->menu_id;
		$column_array['ajax_url'] = $menuAjaxDto->ajax_url;
		$this->db->sqlInsert("tb_admin2_menu_ajax", $column_array);
	}

	/**메뉴 Ajax 수정한다.
	 * @param array $menuAjaxArray
	 */
	public function updateAdminMenuAjax($menuAjaxArray)
	{
		$this->db->sqlUpdate("tb_admin2_menu_ajax", $menuAjaxArray,
			array('id' => $menuAjaxArray['id'], 'menu_id' => $menuAjaxArray['menu_id']));
	}

	/**메뉴 Ajax 삭제한다.
	 * @param array $menuAjaxArray
	 */
	public function deleteAdminMenuAjax($menuAjaxArray)
	{
		$this->db->sqlDelete("tb_admin2_menu_ajax",
			array('id' => $menuAjaxArray['id'], 'menu_id' => $menuAjaxArray['menu_id']));
	}
}
