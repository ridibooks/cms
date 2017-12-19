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
}
