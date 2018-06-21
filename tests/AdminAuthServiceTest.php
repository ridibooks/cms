<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Tests;

use PHPUnit\Framework\TestCase;
use Ridibooks\Cms\Service\AdminAuthService;
use Ridibooks\Cms\Service\AdminMenuService;
use Ridibooks\Cms\Service\AdminMenuTree;
use Ridibooks\Cms\Service\AdminTagService;
use Ridibooks\Cms\Service\AdminUserService;
use Ridibooks\Cms\Thrift\AdminUser\AdminUser;
use Ridibooks\Cms\Thrift\Errors\UnauthorizedException;

class AdminAuthServiceTest extends TestCase
{
    private $auth_service;

    protected function setUp()
    {
        $this->auth_service = new AdminAuthService();
        $this->auth_service['user_service'] = $this->createMock(AdminUserService::class);
        $this->auth_service['menu_service'] = $this->createMock(AdminMenuService::class);
        $this->auth_service['tag_service'] = $this->createMock(AdminTagService::class);
    }

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

    public function testRemoveForbiddenMenus()
    {
        $auth_service = new AdminAuthService();

        $menu_trees = [
            new AdminMenuTree(['id' => 1, 'is_use' => false, 'is_show' => false]),
            new AdminMenuTree(['id' => 2, 'is_use' => false, 'is_show' => true]),
            new AdminMenuTree(['id' => 3, 'is_use' => true, 'is_show' => false]),
            new AdminMenuTree(['id' => 4, 'is_use' => true, 'is_show' => true]),
        ];
        $result = $auth_service->removeForbiddenMenus($menu_trees);
        $this->assertEquals([
            new AdminMenuTree(['id' => 4, 'is_use' => true, 'is_show' => true]),
        ], $result);

        $menu_trees = [
            new AdminMenuTree(['id' => 1, 'is_use' => false, 'is_show' => false], [
                new AdminMenuTree(['id' => 2, 'is_use' => true, 'is_show' => true]),
            ]),
            new AdminMenuTree(['id' => 3, 'is_use' => true, 'is_show' => true], [
                new AdminMenuTree(['id' => 4, 'is_use' => true, 'is_show' => true]),
            ]),
        ];
        $result = $auth_service->removeForbiddenMenus($menu_trees);
        $this->assertEquals([
            new AdminMenuTree(['id' => 3, 'is_use' => true, 'is_show' => true], [
                new AdminMenuTree(['id' => 4, 'is_use' => true, 'is_show' => true]),
            ]),
        ], $result);
    }

    public function testRemoveEmptyParentMenus()
    {
        $auth_service = new AdminAuthService();

        // Test empty root menus
        $menu_trees = [
            new AdminMenuTree(['id' => 1, 'menu_url' => '#']),
            new AdminMenuTree(['id' => 2, 'menu_url' => '#'], [
                new AdminMenuTree(['id' => 3, 'menu_url' => '/']),
            ]),
            new AdminMenuTree(['id' => 4, 'menu_url' => '#']),
        ];
        $result = $auth_service->removeEmptyParentMenus($menu_trees);
        $this->assertEquals([
            new AdminMenuTree(['id' => 2, 'menu_url' => '#'], [
                new AdminMenuTree(['id' => 3, 'menu_url' => '/']),
            ]),
        ], $result);

        // Test empty parent menus
        $menu_trees = [
            new AdminMenuTree(['id' => 1, 'menu_url' => '#'], [
                new AdminMenuTree(['id' => 2, 'menu_url' => '#']),
                new AdminMenuTree(['id' => 3, 'menu_url' => '#'], [
                    new AdminMenuTree(['id' => 4, 'menu_url' => '#']),
                ]),
            ]),
        ];
        $result = $auth_service->removeEmptyParentMenus($menu_trees);
        $this->assertEquals([], $result);

        // Test non-empty parent menus
        $menu_trees = [
            new AdminMenuTree(['id' => 1, 'menu_url' => '#'], [
                new AdminMenuTree(['id' => 2, 'menu_url' => '#']),
                new AdminMenuTree(['id' => 3, 'menu_url' => '#'], [
                    new AdminMenuTree(['id' => 4, 'menu_url' => '/']),
                ]),
            ]),
        ];
        $result = $auth_service->removeEmptyParentMenus($menu_trees);
        $this->assertEquals([
            new AdminMenuTree(['id' => 1, 'menu_url' => '#'], [
                new AdminMenuTree(['id' => 3, 'menu_url' => '#'], [
                    new AdminMenuTree(['id' => 4, 'menu_url' => '/']),
                ]),
            ]),
        ], $result);
    }

    public function testAuthorizeSkipTokenValidationWhenTestIDSet()
    {
        $_ENV['TEST_ID'] = 'admin';
        $auth_service = $this->getMockBuilder(AdminAuthService::class)
            ->setMethods(['checkAuth', 'introspectToken'])
            ->getMock();

        $auth_service->expects($this->never())
            ->method('introspectToken');

        $this->expectException(UnauthorizedException::class);
        $auth_service->authorize('test', [], '/test');
    }

    public function testAuthorizeByTag()
    {
        $this->auth_service['user_service']->method('getUser')
            ->willReturn(new AdminUser(['id' => 'test', 'is_use' => true]));
        $this->auth_service['user_service']->method('getAdminUserAllTag')
            ->willReturn([1, 2]);
        $this->auth_service['tag_service']->method('findTagsByName')
            ->will($this->onConsecutiveCalls(
                [2, 3],
                [3, 4] // fail case
            ));

        $this->assertNull($this->auth_service->authorizeByTag('test', ['test']));

        // Fail case
        $this->expectException(UnauthorizedException::class);
        $this->auth_service->authorizeByTag('test', ['test']);
    }

    public function testAuthorizeFailWithInvalidUser()
    {
        $this->auth_service['user_service']->method('getUser')
            ->willReturn(new AdminUser(['id' => 'test', 'is_use' => false]));

        $this->expectException(UnauthorizedException::class);
        $this->assertNull($this->auth_service->authorize('test', [], '/test'));

        $this->auth_service['user_service']->method('getUser')
            ->willReturn(new AdminUser([]));

        $this->expectException(UnauthorizedException::class);
        $this->assertNull($this->auth_service->authorize('test', [], '/test'));
    }
}
