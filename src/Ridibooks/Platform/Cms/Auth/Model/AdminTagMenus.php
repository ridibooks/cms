<?php

namespace Ridibooks\Platform\Cms\Auth\Model;

use Ridibooks\Platform\Common\Base\AdminBaseModel;

/**tb_admin_tag_menu 의 조회용 model
 * Class AdminTagMenus
 * @package Ridibooks\Platform\Cms\Auth\Model
 */
class AdminTagMenus extends AdminBaseModel
{
	/**해당 태그에 매핑된 메뉴 리스트 가져온다.
	 * @param $tag_id
	 * @return array
	 */
	public function getAdminMenuTagList($tag_id)
	{
		return $this->db->sqlDicts("SELECT * FROM tb_admin2_tag_menu WHERE tag_id = ?", $tag_id);
	}

	/**해당 태그에 메뉴가 매핑되어있는지 여부를 count 한다.
	 * @param int $tag_id
	 * @param int $menu_id
	 * @return int
	 */
	public function getAdminMenuTagCount($tag_id, $menu_id)
	{
		$whereArray = [];
		$whereArray['tag_id'] = $tag_id;
		$whereArray['menu_id'] = $menu_id;
		return (int)$this->db->sqlData(
			"SELECT COUNT(tag_id) FROM tb_admin2_tag_menu ?",
			sqlWhereWithClause($whereArray)
		);
	}

	/**해당 태그에 메뉴가 매핑되어있는지 여부를 count 한다.
	 * @param int $tag_id
	 * @return int
	 */
	public function getAdminMenuTagCountByTagId($tag_id)
	{
		$whereArray = [];
		$whereArray['tag_id'] = $tag_id;
		return (int)$this->db->sqlData(
			"SELECT COUNT(tag_id) FROM tb_admin2_tag_menu ?",
			sqlWhereWithClause($whereArray)
		);
	}
}
