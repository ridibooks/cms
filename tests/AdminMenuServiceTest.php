<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service;

use PHPUnit\Framework\TestCase;
use Ridibooks\Cms\Service\AdminMenuService;

class AdminMenuServiceTest extends TestCase
{
    public function testMenuAjaxList()
    {
        $menu_service = new AdminMenuService();
        $ajax_list = $menu_service->getMenuAjaxList([2]);
        $this->assertNotEmpty($ajax_list);
    }
}
