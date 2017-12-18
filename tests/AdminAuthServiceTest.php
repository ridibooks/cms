<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service;

use PHPUnit\Framework\TestCase;
use Ridibooks\Cms\Service\AdminAuthService;
use Ridibooks\Cms\Service\AdminUserService;

class AdminAuthServiceTest extends TestCase
{
    public function testAuthorizeRequest()
    {
        $this->assertTrue(
            AdminAuthService::authorizeRequest('devgrapher', '/super/logs')
        );

        $this->assertFalse(
            AdminAuthService::authorizeRequest('devgrapher', '/super/false')
        );

        // ajax test
        $this->assertTrue(
            AdminAuthService::authorizeRequest('devgrapher', '/super/tag_action.ajax')
        );
        $this->assertFalse(
            AdminAuthService::authorizeRequest('devgrapher', '/super/false.ajax')
        );
    }

    public function testHasHashAuth()
    {
        $auth_list = ['/admin/book/productList'];

        $this->assertTrue(
            AdminAuthService::hasHashAuth(null, '/admin/book/productList', $auth_list)
        );
    }

    public function testHasHashAuth_withHash()
    {
        $auth_list = ['/admin/book/productList#EDIT_세트도서'];

        $this->assertTrue(
            AdminAuthService::hasHashAuth('EDIT_세트도서', '/admin/book/productList', $auth_list)
        );
        $this->assertFalse(
            AdminAuthService::hasHashAuth('', '/admin/book/productList', $auth_list)
        );
        $this->assertFalse(
            AdminAuthService::hasHashAuth('DELETE_세트도서', '/admin/book/productList', $auth_list)
        );
    }

    public function testGetHashesFromMenus()
    {
        $auth_list = ['/admin/book/productList#EDIT_세트도서'];
        $hashs = AdminAuthService::getHashesFromMenus('/admin/book/productList', $auth_list);
        $this->assertEquals(['EDIT_세트도서'], $hashs);
    }
}
