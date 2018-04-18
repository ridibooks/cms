<?php
namespace Ridibooks\Cms\Service;

use Ridibooks\Cms\Model\AdminTag;
use Ridibooks\Cms\Thrift\AdminTag\AdminTag as ThriftAdminTag;
use Ridibooks\Cms\Thrift\AdminTag\AdminTagServiceIf;
use Ridibooks\Cms\Thrift\ThriftService;

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

        return AdminTag::find($tag_id)->menus->pluck('id')->all();
    }

    public function getMappedAdminMenuHashes($check_url, $tag_id)
    {
        $menu_ids = self::getAdminTagMenus($tag_id);
        $admin_service = new AdminMenuService();
        $menus = $admin_service->getMenus($menu_ids);
        $menus = ThriftService::convertMenuCollectionToArray($menus);
        return AdminAuthService::getHashesFromMenus($check_url, $menus);
    }

    public function getAdminTag($tag_id): ThriftAdminTag
    {
        /** @var AdminTag $tag */
        $tag = AdminTag::find($tag_id);

        if (empty($tag)) {
            return new ThriftAdminTag();
        }
        return new ThriftAdminTag($tag->toArray());
    }

    public function getAdminTags(array $tag_ids): array
    {
        foreach ($tag_ids as $id) {
            $tags[] = $this->getAdminTag($id);
        }

        return $tags;
    }

    public function findTagByName(string $name): ?int
    {
        $tag = AdminTag::where('name', $name)->first();
        if (empty($tag)) {
            return null;
        }

        return $tag->pluck('id');
    }

    public function findTagsByName(array $tag_names): array
    {
        $tag = AdminTag::whereIn('name', $tag_names)->first();
        if (empty($tag)) {
            return [];
        }


        return $tag->pluck('id')->all();
    }
}
