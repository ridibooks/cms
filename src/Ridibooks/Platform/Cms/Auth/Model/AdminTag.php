<?php

namespace Ridibooks\Platform\Cms\Auth\Model;

use Ridibooks\Platform\Common\Base\AdminBaseModel;

/**tb_admin_tag의 insert, update, delete용 model
 * Class AdminTag
 * @package Ridibooks\Platform\Cms\Auth\Model
 */
class AdminTag extends AdminBaseModel
{
	/**태그 등록한다.
	 * @param \Ridibooks\Platform\Cms\Auth\Dto\AdminTagDto $tagDto
	 */
	public function insertTag($tagDto)
	{
		$column_array = array();
		$column_array['name'] = $tagDto->name;
		$column_array['is_use'] = $tagDto->is_use;
		$this->db->sqlInsert("tb_admin2_tag", $column_array);
	}

	/**태그 수정한다.
	 * @param array $tagArray
	 */
	public function updateTag($tagArray)
	{
		$this->db->sqlUpdate("tb_admin2_tag", $tagArray, array('id' => $tagArray['id']));
	}
}
