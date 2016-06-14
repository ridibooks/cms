<?php
namespace Ridibooks\Platform\Cms\Auth\Model;

use Ridibooks\Platform\Common\Base\AdminBaseModel;

class AdminUserTags extends AdminBaseModel
{
	/**해당 태그와 매핑된 어드민 유저의 갯수 가져온다.
	 * @param int $tag_id
	 * @return int
	 */
	public function getAdminUserTaggedCount($tag_id)
	{
		return (int)$this->db->sqlData("SELECT COUNT(user_id) FROM tb_admin2_user_tag WHERE tag_id = ?", $tag_id);
	}

	/**해당 어드민의 태그 목록 가져온다.
	 * @param string $user_id
	 * @return array
	 */
	public function getAdminUserTagList($user_id)
	{
		return $this->db->sqlDatas("SELECT tag_id FROM tb_admin2_user_tag WHERE user_id = ?", $user_id);
	}

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

	/**태그들의 기존 계정 ID를 새로운 계정 ID로 모두 변경한다.
	 * @param string $old_id
	 * @param string $new_id
	 */
	public function updateUserOfTags($old_id, $new_id)
	{
		$column_array = [];
		$column_array['user_id'] = $new_id;
		$this->db->sqlUpdate('tb_admin2_user_tag', $column_array, ['user_id' => $old_id]);
	}
}
