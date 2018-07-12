<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Tests;

use PHPUnit\Framework\TestCase;
use Ridibooks\Cms\Service\AdminTagService;

class AdminTagServiceTest extends TestCase
{
    public function testGetAdminTag()
    {
        $tag_service = new AdminTagService();
        $tag = $tag_service->getAdminTag(1);

        $this->assertNotEmpty($tag->id);
    }

    public function testGetAdminTags()
    {
        $tag_service = new AdminTagService();
        $tags = $tag_service->getAdminTags([1, 2]);

        $this->assertEquals(2, count($tags));
        $this->assertNotEmpty($tags[0]->name);
        $this->assertNotEmpty($tags[1]->name);
    }

    public function testGetMappedAdminMenuHashes()
    {
        $tag_service = new AdminTagService();
        $hashes = $tag_service->getMappedAdminMenuHashes('/super/users', '1');

        $this->assertEquals(['EDIT'], array_values($hashes));
    }

    public function testFindTagsByName()
    {
        $tag_service = new AdminTagService();
        $tags = $tag_service->findTagsByName(['권한 관리', '테스트']);

        $this->assertEquals([1, 2], array_values($tags));
    }
}
