<?php
namespace Ridibooks\Cms\Thrift;

use Ridibooks\Cms\Service\AdminTagService;
use Ridibooks\Cms\Thrift\AdminTag\AdminTagServiceIf;

class AdminTagThrift implements AdminTagServiceIf
{
    /** @var AdminTagService $server */
    private $server = null;

    public function __construct(AdminTagService $server)
    {
        $this->server = $server;
    }

    public function getAdminIdsFromTags(array $tag_ids)
    {
        return $this->server->getAdminIdsFromTags($tag_ids);
    }

    public function getAdminTagMenus($tag_id)
    {
        return $this->server->getAdminTagMenus($tag_id);
    }

    public function getMappedAdminMenuHashes($check_url, $tag_id)
    {
        return $this->server->getMappedAdminMenuHashes($check_url, $tag_id);
    }

    public function getAdminTag($tag_id)
    {
        return $this->server->getAdminTag($tag_id);
    }

    public function getAdminTags(array $tag_ids)
    {
        return $this->server->getAdminTags($tag_ids);
    }
}
