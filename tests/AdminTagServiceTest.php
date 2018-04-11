<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service;

use PHPUnit\Framework\TestCase;

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
}