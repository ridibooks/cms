<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service;

use Ridibooks\Cms\Thrift\Errors\UnauthorizedException;
use PHPUnit\Framework\TestCase;

class AdminAuthServiceTest extends TestCase
{
    public function testCheckAuth()
    {
        $service = new AdminAuthService();

        $this->assertTrue(
            $service->checkAuth(['GET', 'POST', 'PUT', 'DELETE'], '/super/users', 'admin')
        );
        $this->assertTrue(
            $service->checkAuth(['GET', 'POST', 'PUT', 'DELETE'], '/super/users/', 'admin')
        );
        $this->assertTrue(
            $service->checkAuth(['GET', 'POST', 'PUT', 'DELETE'], '/super/users?some_param=some_value', 'admin')
        );
        $this->assertFalse(
            $service->checkAuth(['GET', 'POST', 'PUT', 'DELETE'], '/super/unauthorized/path', 'admin')
        );
    }

    public function testCheckAuthWithSubpath()
    {
        $service = new AdminAuthService();

        $this->assertTrue(
            $service->checkAuth(['GET', 'POST', 'PUT', 'DELETE'], '/super/users/subpath', 'admin')
        );
        $this->assertTrue(
            $service->checkAuth(['GET', 'POST', 'PUT', 'DELETE'], '/super/users/subpath/', 'admin')
        );
        $this->assertTrue(
            $service->checkAuth(['GET', 'POST', 'PUT', 'DELETE'], '/super/users/subpath?some_param=some_value', 'admin')
        );
        $this->assertTrue(
            $service->checkAuth(['GET', 'POST', 'PUT', 'DELETE'], '/super/users/subpath/subsub', 'admin')
        );

        // As-is state, but somewhat problematic.
        $this->assertTrue(
            $service->checkAuth(['GET', 'POST', 'PUT', 'DELETE'], '/weired/super/users/subpath/subsub', 'admin')
        );
    }

    public function testCheckAuthWithHash()
    {
        $service = new AdminAuthService();

        $this->assertTrue(
            $service->hasHashAuth('EDIT', '/super/users', 'admin')
        );
        $this->assertFalse(
            $service->hasHashAuth('', '/super/users', 'admin')
        );
        $this->assertFalse(
            $service->hasHashAuth('DELETE', '/super/users', 'admin')
        );
    }

    public function testGetHashesFromMenus()
    {
        $service = new AdminAuthService();

        $auth_list = ['/admin/book/productList#EDIT_세트도서'];
        $hashs = $service->getHashesFromMenus('/admin/book/productList', $auth_list);
        $this->assertEquals(['EDIT_세트도서'], $hashs);
    }

    public function testHideEmptyRootMenus()
    {
        $menus = [
            ['menu_deep' => 0, 'menu_url' => '#', 'is_show' => true],
            ['menu_deep' => 0, 'menu_url' => '#', 'is_show' => true],
            ['menu_deep' => 1, 'menu_url' => '/', 'is_show' => true],
            ['menu_deep' => 0, 'menu_url' => '#', 'is_show' => true],
        ];

        $authService = new AdminAuthService();
        $result = $authService->hideEmptyRootMenus($menus);
        $this->assertEquals([
            ['menu_deep' => 0, 'menu_url' => '#', 'is_show' => false],
            ['menu_deep' => 0, 'menu_url' => '#', 'is_show' => true],
            ['menu_deep' => 1, 'menu_url' => '/', 'is_show' => true],
            ['menu_deep' => 0, 'menu_url' => '#', 'is_show' => false],
        ], $result);
    }

    public function testAuthorizeSkipTokenValidationWhenTestIDSet()
    {
        $_ENV['TEST_ID'] = 'admin';
        $authService = $this->getMockBuilder(AdminAuthService::class)
            ->setMethods(['checkAuth', 'introspectToken'])
            ->getMock();

        $authService->expects($this->never())
            ->method('introspectToken');

        $this->expectException(UnauthorizedException::class);
        $this->assertNull($authService->authorize('test', [], '/test'));
    }
}
