<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Tests;

use PHPUnit\Framework\TestCase;
use Ridibooks\Cms\Service\AdminUserService;

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

    public function testUpdateOrCreateUser()
    {
        $user_service = new AdminUserService();
        $tester = [
            'id' => 'test',
            'email' => 'test@email.com',
            'name' => 'tester',
        ];
        $user_service->updateOrCreateUser($tester);
        $actual = $user_service->getUser('test');

        $this->assertEquals($tester['id'], $actual->id);
        $this->assertEquals($tester['email'], $actual->email);

        $actual = $user_service->updateOrCreateUser(
            array_merge($tester, ['email' => 'new@email.com'])
        );
        $actual = $user_service->getUser('test');

        $this->assertEquals('new@email.com', $actual->email);
    }
}
