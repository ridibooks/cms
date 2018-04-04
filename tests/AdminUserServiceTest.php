<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service;

use PHPUnit\Framework\TestCase;

// TODO(devgrapher): temparary test cases. Model classes are statically bound inside. Hard to inject mocks.
class AdminUserServiceTest extends TestCase
{
    public function testAllMenuIds()
    {
        $user_service = new AdminUserService();
        $ids = $user_service->getAllMenuIds('admin');
        $this->assertNotEmpty($ids);
    }

    public function testAllMenus()
    {
        $user_service = new AdminUserService();
        $menus = $user_service->getAllMenus('admin', null);
        $this->assertNotEmpty($menus);
    }

    public function testGetAllMenuAjaxList()
    {
        $user_service = new AdminUserService();
        $ajax_list = $user_service->getAllMenuAjaxList('admin', 'ajax_url');
        $this->assertNotEmpty($ajax_list);
    }
}
