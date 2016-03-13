<?php

namespace Ridibooks\Platform\Cms\Auth\Model;

use Ridibooks\Platform\Common\Base\AdminBaseModel;

/**tb_admin_tag 의 조회용 model
 * Class AdminTags
 * @package Ridibooks\Platform\Cms\Auth\Model
 */
class AdminTags extends AdminBaseModel
{
	/**태그 리스트 가져온다.
	 * @param null $is_use 1 or 0
	 * @return array
	 */
	public function getTagList($is_use = null)
	{
		$where = array();
		if ($is_use != null) {
			$where['is_use'] = $is_use;
		}
		return $this->db->sqlDicts("SELECT * FROM tb_admin2_tag ?", sqlWhereWithClause($where));
	}

	/**해당 태그 이름의 id를 가져온다.
	 * @param string $name
	 * @return string name
	 */
	public function getTagNameId($name)
	{
		return $this->db->sqlData("SELECT id FROM tb_admin2_tag WHERE name=?", $name);
	}
}
