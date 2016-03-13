<?php

namespace Ridibooks\Platform\Cms\Auth\Model;

use Ridibooks\Platform\Cms\Auth\Dto\AdminUserTagDto;
use Ridibooks\Platform\Common\Base\AdminBaseModel;

/**tb_admin_user_tag의 insert, update, delete용 model
 * Class AdminUserTag
 * @package Ridibooks\Platform\Cms\Auth\Model
 */
class AdminUserTag extends AdminBaseModel
{
	/**어드민 계정에 태그를 매핑한다.
	 * @param AdminUserTagDto $adminUserTagDto
	 */
	public function insertAdminUserTag($adminUserTagDto)
	{
		$column_array = array();
		$column_array['user_id'] = $adminUserTagDto->user_id;
		$column_array['tag_id'] = $adminUserTagDto->tag_id;
		$this->db->sqlInsert("tb_admin2_user_tag", $column_array);
	}

	/**어드민 계정에 매핑된 모든 태그를 제거한다.
	 * @param string $user_id
	 */
	public function deleteAdminUserTag($user_id)
	{
		$column_array = array();
		$column_array['user_id'] = $user_id;
		$this->db->sqlDelete('tb_admin2_user_tag', $column_array);
	}
}
