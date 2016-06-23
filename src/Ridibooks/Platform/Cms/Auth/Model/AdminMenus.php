<?php

namespace Ridibooks\Platform\Cms\Auth\Model;

use Ridibooks\Platform\Common\Base\AdminBaseModel;

class AdminMenus extends AdminBaseModel
{
	/**
	 * 메뉴 리스트 가져온다.
	 * @param int|null $is_use 1 or 0
	 * @param string $search_text
	 * @return array
	 */
	public function getAdminMenuList($is_use = null, $search_text = null)
	{
		$where = [];
		if (!is_null($is_use)) {
			$where['is_use'] = $is_use;
		}
		if (!is_null($search_text)) {
			$where['menu_title'] = sqlLike($search_text);
		}
		return $this->db->sqlDicts("SELECT * FROM tb_admin2_menu ? ORDER BY menu_order", sqlWhereWithClause($where));
	}
}
