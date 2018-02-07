<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service;

use PHPUnit\Framework\TestCase;
use Ridibooks\Cms\Service\AdminAuthService;
use Ridibooks\Cms\Service\AdminUserService;

class AdminAuthServiceTest extends TestCase
{
    public function testCheckAuth()
    {
        $auth_list = ['/admin/book/productList'];

        $this->assertTrue(
            AdminAuthService::checkAuth(null, '/admin/book/productList', $auth_list)
        );
        $this->assertFalse(
            AdminAuthService::checkAuth(null, '/admin/book/product', $auth_list)
        );
    }

    public function testCheckAuthWithSubpath()
    {
        $auth_list = ['/admin/book/productList'];
        $this->assertTrue(
            AdminAuthService::checkAuth(null, '/admin/book/productList/', $auth_list)
        );
        $this->assertTrue(
            AdminAuthService::checkAuth(null, '/admin/book/productList/subpath', $auth_list)
        );
        $this->assertTrue(
            AdminAuthService::checkAuth(null, '/admin/book/productList/subpath/subsub', $auth_list)
        );

        // As-is state, but somewhat problematic.
        $this->assertTrue(
            AdminAuthService::checkAuth(null, '/weired/admin/book/productList/subpath/subsub', $auth_list)
        );
    }

    public function testCheckAuth_withHash()
    {
        $auth_list = ['/admin/book/productList#EDIT_세트도서'];

        $this->assertTrue(
            AdminAuthService::checkAuth('EDIT_세트도서', '/admin/book/productList', $auth_list)
        );
        $this->assertFalse(
            AdminAuthService::checkAuth('', '/admin/book/productList', $auth_list)
        );
        $this->assertFalse(
            AdminAuthService::checkAuth('DELETE_세트도서', '/admin/book/productList', $auth_list)
        );
    }

    public function testGetHashesFromMenus()
    {
        $auth_list = ['/admin/book/productList#EDIT_세트도서'];
        $hashs = AdminAuthService::getHashesFromMenus('/admin/book/productList', $auth_list);
        $this->assertEquals(['EDIT_세트도서'], $hashs);
    }

    public function testHideEmptyRootMenus()
    {
        $menus = [
            [ 'menu_deep' => 0, 'menu_url' => '#', 'is_show' => true ],
            [ 'menu_deep' => 0, 'menu_url' => '#', 'is_show' => true ],
            [ 'menu_deep' => 1, 'menu_url' => '/', 'is_show' => true ],
            [ 'menu_deep' => 0, 'menu_url' => '#', 'is_show' => true ],
        ];

        $authService = new AdminAuthService();
        $result = $authService->hideEmptyRootMenus($menus);
        $this->assertEquals([
            [ 'menu_deep' => 0, 'menu_url' => '#', 'is_show' => false],
            [ 'menu_deep' => 0, 'menu_url' => '#', 'is_show' => true],
            [ 'menu_deep' => 1, 'menu_url' => '/', 'is_show' => true],
            [ 'menu_deep' => 0, 'menu_url' => '#', 'is_show' => false],
        ], $result);
    }
}
