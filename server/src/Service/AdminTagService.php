<?php
namespace Ridibooks\Cms\Server\Service;

use Ridibooks\Cms\Thrift\AdminTag\AdminTag as ThriftAdminTag;
use Ridibooks\Cms\Server\Model\AdminTag;

class AdminTagService
{
	/**
	 * 해당 tags 를 가지고 있는 사용중인 어드민 ID를 가져온다.
	 * @param array $tag_ids
	 * @return array
	 */
	public static function getAdminIdsFromTags($tag_ids)
	{
		$tags = AdminTag::with('users')->find($tag_ids)
			->map(function ($tag) {
				return $tag->users->pluck('id');
			})
			->collapse()
			->toArray();
		return new ThriftAdminTag($tags);
	}

	public static function getAdminTagMenus($tag_id)
	{
		if (empty($tag_id)) {
			return [];
		}

		$tags = AdminTag::find($tag_id)->menus->pluck('id')->all();
		return new ThriftAdminTag($tags);
	}

	public function getMappedAdminMenuHashes($check_url, $tag_id)
	{
		$menu_ids = AdminTagService::getAdminTagMenus($tag_id);
		$menus = AdminMenuService::getMenus($menu_ids);
		return AdminAuthService::getHashesFromMenus($check_url, $menus);
	}
}
