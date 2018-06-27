<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Tests;

use PHPUnit\Framework\TestCase;
use Ridibooks\Cms\Service\AdminUserService;

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

    public function testGetAllMenusIncludesTagsOverGroup()
    {
        $user_service = new AdminUserService();
        $menus = $user_service->getAllMenus('admin', 'menu_title');
        $this->assertContains('Group 테스트', $menus);
    }

    public function testGetAllMenuAjaxList()
    {
        $user_service = new AdminUserService();
        $ajax_list = $user_service->getAllMenuAjaxList('admin', 'ajax_url');
        $this->assertNotEmpty($ajax_list);
    }

    public function testGetAdminUserAllTagIncludesTagsFromUserGroup()
    {
        $user_service = new AdminUserService();
        $this->assertEquals([1], $user_service->getAdminUserTag('admin'));
        $this->assertEquals([1, 2], $user_service->getAdminUserAllTag('admin'));
    }
}
