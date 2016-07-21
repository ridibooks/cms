<?php
namespace Ridibooks\Platform\Cms\Auth;

use Ridibooks\Exception\MsgException;
use Ridibooks\Platform\Cms\Auth\Dto\AdminTagMenuDto;
use Ridibooks\Platform\Cms\Model\AdminTag;
use Ridibooks\Platform\Common\ValidationUtils;

class AdminTagService
{
	public function getTagList()
	{
		return AdminTag::all()->toArray();
	}

	public function getMappedAdminMenuListForSelectBox($tag_id)
	{
		$menus = AdminMenuService::getMenuList();

		//태그에 매핑된 메뉴 리스트
		$menu_ids = AdminTagService::getAdminTagMenus($tag_id);

		return array_map(function($menu) use ($menu_ids) {
			if (in_array($menu['id'], $menu_ids)) {
				$menu['selected'] = 'selected';
			}
			return $menu;
		}, $menus);
	}

	public function getMappedAdmins($tag_id)
	{
		return AdminTag::find($tag_id)->users->toArray();
	}

	/**
	 * 해당 tags 를 가지고 있는 사용중인 어드민 ID를 가져온다.
	 * @param array $tag_ids
	 * @return array
	 */
	public static function getAdminIdsFromTags($tag_ids)
	{
		return AdminTag::with('users')->find($tag_ids)
			->map(function ($tag) {
				return $tag->users->pluck('id');
			})
			->collapse()
			->toArray();
	}

	public static function getAdminTagMenus($tag_id)
	{
		if (empty($tag_id)) {
			return [];
		}

		return AdminTag::find($tag_id)->menus->pluck('id')->all();
	}

	public function getMappedAdminMenuHashes($check_url, $tag_id)
	{
		$menu_ids = AdminTagService::getAdminTagMenus($tag_id);

		$menus = AdminMenuService::getMenus($menu_ids);

		return AdminAuthService::getHashesFromMenus($check_url, $menus);
	}

	public static function insertTag($tagDto)
	{
		self::_validateTag((array)$tagDto);

		$tag = new AdminTag();
		$tag->fill((array)$tagDto);
		$tag->creator = LoginService::GetAdminID();
		$tag->save();
	}

	public static function updateTag($tagDto)
	{
		foreach ($tagDto->tag_list as $tag) {
			self::_validateTag($tag);

			if ($tag['is_use'] != 1) {
				$user_count = AdminTag::find($tag['id'])->users()->count();
				if ($user_count > 0) { //해당 태그와 매핑되어있는 사용자가 있으면 사용중지를 할 수 없다.
					throw new MsgException('해당 태그를 사용하고 있는 유저가 있습니다. 사용중인 유저: ' . $user_count);
				}
			}

			/** @var AdminTag $adminTag */
			$adminTag = AdminTag::find($tag['id']);
			$adminTag->fill($tag);
			$adminTag->save();
		}
	}

	public static function deleteTag($id)
	{
		AdminTag::destroy($id);
	}

	/**
	 * @param AdminTagMenuDto $tagMenuDto
	 */
	public function insertTagMenu($tagMenuDto)
	{
		$this->_validateTagMenu((array)$tagMenuDto);

		/** @var AdminTag $tag */
		$tag = AdminTag::find($tagMenuDto->tag_id);
		$tag->menus()->attach($tagMenuDto->menu_id);
	}

	/**
	 * @param AdminTagMenuDto $tagMenuDto
	 */
	public function deleteTagMenu($tagMenuDto)
	{
		/** @var AdminTag $tag */
		$tag = AdminTag::find($tagMenuDto->tag_id);
		$tag->menus()->detach($tagMenuDto->menu_id);
	}

	private static function _validateTag($tagArray)
	{
		ValidationUtils::checkNullField($tagArray['name'], '태그 이름을 입력하여 주십시오.');
	}

	private function _validateTagMenu($tagArray)
	{
		ValidationUtils::checkNullField($tagArray['tag_id'], "태그 ID가 없습니다.");
		ValidationUtils::checkNullField($tagArray['menu_id'], "메뉴 ID가 없습니다.");
	}

	public static function getTagListWithUseCount()
	{
		return AdminTag::withCount('users', 'menus')->get()->toArray();
	}
}
