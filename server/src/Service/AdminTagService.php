<?php
namespace Ridibooks\Cms\Server\Service;

use Ridibooks\Cms\Thrift\AdminTag\AdminTag as ThriftAdminTag;
use Ridibooks\Cms\Server\Model\AdminTag;
use Ridibooks\Cms\Thrift\AdminTag\AdminTagServiceIf;

class AdminTagService implements AdminTagServiceIf
{
	/**
	 * 해당 tags 를 가지고 있는 사용중인 어드민 ID를 가져온다.
	 * @param array $tag_ids
	 * @return array
	 */
	public function getAdminIdsFromTags(array $tag_ids)
	{
		return AdminTag::with('users')->find($tag_ids)
			->map(function ($tag) {
				return $tag->users->pluck('id');
			})
			->collapse()
			->toArray();
	}

	public function getAdminTagMenus($tag_id)
	{
		if (empty($tag_id)) {
			return [];
		}

		$tags = AdminTag::find($tag_id)->menus->pluck('id')->all();
		return new ThriftAdminTag($tags);
	}

	public function getMappedAdminMenuHashes($check_url, $tag_id)
	{
		$menu_ids = self::getAdminTagMenus($tag_id);
		$admin_service = new AdminMenuService;
		$menus = $admin_service->getMenus($menu_ids);
		return AdminAuthService::getHashesFromMenus($check_url, $menus);
	}
}
