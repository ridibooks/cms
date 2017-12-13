<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service;

use PHPUnit\Framework\TestCase;
use Ridibooks\Cms\Service\AdminUserService;

class AdminUserServiceTest extends TestCase
{
    public function testAllMenuIds()
    {
        $user_service = new AdminUserService();
        $ids = $user_service->getAllMenuIds('devgrapher');
        $this->assertNotEmpty($ids);
    }

    public function testAllMenus()
    {
        $user_service = new AdminUserService();
        $menus = $user_service->getAllMenus('devgrapher', null);
        $this->assertNotEmpty($menus);
    }

    public function testGetAllMenuAjaxList()
    {
        $user_service = new AdminUserService();
        $ajax_list = $user_service->getAllMenuAjaxList('devgrapher', 'ajax_url');
        $this->assertNotEmpty($ajax_list);
    }
}
