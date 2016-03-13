<?php
namespace Ridibooks\Platform\Cms\Auth\Model;

use Ridibooks\Platform\Common\Base\AdminBaseModel;

/**tb_admin_tag_menu 의 등록 / 수정 을 위한 model
 * Class AdminTagMenu
 * @package Ridibooks\Platform\Cms\Auth\Model
 */
class AdminTagMenu extends AdminBaseModel
{
	/**태그에 메뉴를 매핑한다.
	 * @param array $tag_menu_array
	 * @return mixed
	 */
	public function insertAdminMenuTag($tag_menu_array)
	{
		$insertArray = array(
			'tag_id' => $tag_menu_array['tag_id'],
			'menu_id' => $tag_menu_array['menu_id']
		);
		return $this->db->sqlInsert('tb_admin2_tag_menu', $insertArray);
	}

	/**태그에 매핑된 메뉴를 삭제한다.
	 * @param array $tag_menu_array
	 */
	public function deleteAdminMenuTag($tag_menu_array)
	{
		$deleteArray = array(
			'tag_id' => $tag_menu_array['tag_id'],
			'menu_id' => $tag_menu_array['menu_id']
		);
		$this->db->sqlDelete('tb_admin2_tag_menu', $deleteArray);
	}
}
