<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service;

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
}
