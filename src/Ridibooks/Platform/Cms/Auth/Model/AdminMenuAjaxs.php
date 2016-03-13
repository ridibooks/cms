<?php

namespace Ridibooks\Platform\Cms\Auth\Model;

use Ridibooks\Platform\Common\Base\AdminBaseModel;

/**tb_admin_menu_ajax 의 조회용 model
 * Class AdminMenuAjaxs
 * @package Ridibooks\Platform\Cms\Auth\Model
 */
class AdminMenuAjaxs extends AdminBaseModel
{
	/**해당 메뉴 내의 ajax url 목록 가져온다.
	 * @param string $menu_id
	 * @return array
	 */
	public function getAdminMenuAjaxList($menu_id = null)
	{
		$where = array();
		if (!is_null($menu_id)) {
			$where['menu_id'] = $menu_id;
		}
		return $this->db->sqlDicts("SELECT * FROM tb_admin2_menu_ajax ? ORDER BY id", sqlWhereWithClause($where));
	}
}
