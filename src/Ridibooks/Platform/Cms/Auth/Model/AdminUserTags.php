<?php
namespace Ridibooks\Platform\Cms\Auth\Model;

use Ridibooks\Platform\Common\Base\AdminBaseModel;

/**
 * @deprecated AdminUserService 로 옮길 것
 */
class AdminUserTags extends AdminBaseModel
{
	/**해당 어드민에 매핑되어있는 태그 유무를 위한 갯수 가져온다.
	 * @param string $user_id
	 * @param string $tag_id
	 * @return int
	 */
	public function checkAdminUserHasTag($user_id, $tag_id)
	{
		$whereArray = [];
		$whereArray['user_id'] = $user_id;
		$whereArray['tag_id'] = $tag_id;

		return $this->db->sqlCount("tb_admin2_user_tag", $whereArray) > 0;
	}

	/**해당 태그를 가진 유저 id를 가져온다.
	 * @param $tag_id
	 * @return array
	 */
	public function getAdminIdFromUserTag($tag_id)
	{
		$whereArray = [];
		$whereArray['tag_id'] = $tag_id;

		return $this->db->sqlDatas("SELECT user_id FROM tb_admin2_user_tag ?", sqlWhereWithClause($whereArray));
	}
}
