<?php
declare(strict_types=1);

use Ridibooks\Cms\Service\AdminMenuTree;
use PHPUnit\Framework\TestCase;

class AdminMenuTreeTest extends TestCase
{
    public function testBuildTrees()
    {
        $menus = [
            ['id' => 1, 'menu_deep' => 0, 'menu_url' => '#'],
            ['id' => 2, 'menu_deep' => 0, 'menu_url' => '#'],
            ['id' => 3, 'menu_deep' => 1, 'menu_url' => '#'],
            ['id' => 4, 'menu_deep' => 1, 'menu_url' => '#'],
            ['id' => 5, 'menu_deep' => 2, 'menu_url' => '#'],
            ['id' => 6, 'menu_deep' => 1, 'menu_url' => '#'],
            ['id' => 7, 'menu_deep' => 0, 'menu_url' => '#'],
        ];
        $trees = AdminMenuTree::buildTrees($menus);
        $this->assertEquals([
            new AdminMenuTree(['id' => 1, 'menu_deep' => 0, 'menu_url' => '#']),
            new AdminMenuTree(['id' => 2, 'menu_deep' => 0, 'menu_url' => '#'], [
                new AdminMenuTree(['id' => 3, 'menu_deep' => 1, 'menu_url' => '#']),
                new AdminMenuTree(['id' => 4, 'menu_deep' => 1, 'menu_url' => '#'], [
                    new AdminMenuTree(['id' => 5, 'menu_deep' => 2, 'menu_url' => '#']),
                ]),
                new AdminMenuTree(['id' => 6, 'menu_deep' => 1, 'menu_url' => '#']),
            ]),
            new AdminMenuTree(['id' => 7, 'menu_deep' => 0, 'menu_url' => '#']),
        ], $trees);
    }

    public function testFlattenTrees()
    {
        $trees = [
            new AdminMenuTree(['id' => 1, 'menu_deep' => 0, 'menu_url' => '#']),
            new AdminMenuTree(['id' => 2, 'menu_deep' => 0, 'menu_url' => '#'], [
                new AdminMenuTree(['id' => 3, 'menu_deep' => 1, 'menu_url' => '#']),
                new AdminMenuTree(['id' => 4, 'menu_deep' => 1, 'menu_url' => '#'], [
                    new AdminMenuTree(['id' => 5, 'menu_deep' => 2, 'menu_url' => '#']),
                ]),
                new AdminMenuTree(['id' => 6, 'menu_deep' => 1, 'menu_url' => '#']),
            ]),
            new AdminMenuTree(['id' => 7, 'menu_deep' => 0, 'menu_url' => '#']),
        ];
        $menus = AdminMenuTree::flattenTrees($trees);
        $this->assertEquals([
            ['id' => 1, 'menu_deep' => 0, 'menu_url' => '#'],
            ['id' => 2, 'menu_deep' => 0, 'menu_url' => '#'],
            ['id' => 3, 'menu_deep' => 1, 'menu_url' => '#'],
            ['id' => 4, 'menu_deep' => 1, 'menu_url' => '#'],
            ['id' => 5, 'menu_deep' => 2, 'menu_url' => '#'],
            ['id' => 6, 'menu_deep' => 1, 'menu_url' => '#'],
            ['id' => 7, 'menu_deep' => 0, 'menu_url' => '#'],
        ], $menus);
    }

    public function testFilterTreesPostOrder() {
        $trees = [
            new AdminMenuTree(['id' => 1, 'is_show' => true]),
            new AdminMenuTree(['id' => 2, 'is_show' => false]),
            new AdminMenuTree(['id' => 3, 'is_show' => true]),
            new AdminMenuTree(['id' => 4, 'is_show' => false]),
            new AdminMenuTree(['id' => 5, 'is_show' => true]),
        ];
        $filtered_trees = AdminMenuTree::filterTreesPostOrder($trees, function ($node) {
            return $node->getMenu()['is_show'];
        });
        $this->assertEquals([
            new AdminMenuTree(['id' => 1, 'is_show' => true]),
            new AdminMenuTree(['id' => 3, 'is_show' => true]),
            new AdminMenuTree(['id' => 5, 'is_show' => true]),
        ], $filtered_trees);

        // Test whether if the children are filtered first and the result is passed to match function
        $trees = [
            new AdminMenuTree(['id' => 1, 'menu_url' => '#', 'is_show' => true], [
                new AdminMenuTree(['id' => 2, 'menu_url' => '#', 'is_show' => true], [
                    new AdminMenuTree(['id' => 3, 'menu_url' => '/', 'is_show' => false]),
                ]),
            ]),
            new AdminMenuTree(['id' => 4, 'menu_url' => '#', 'is_show' => true], [
                new AdminMenuTree(['id' => 5, 'menu_url' => '#', 'is_show' => true], [
                    new AdminMenuTree(['id' => 6, 'menu_url' => '/', 'is_show' => true]),
                ]),
            ]),
        ];
        $filtered_trees = AdminMenuTree::filterTreesPostOrder($trees, function ($node) {
            $menu = $node->getMenu();

            if ($menu['menu_url'] === '#') {
                return !empty($node->getChildren());
            }

            return $menu['is_show'];
        });
        $this->assertEquals([
            new AdminMenuTree(['id' => 4, 'menu_url' => '#', 'is_show' => true], [
                new AdminMenuTree(['id' => 5, 'menu_url' => '#', 'is_show' => true], [
                    new AdminMenuTree(['id' => 6, 'menu_url' => '/', 'is_show' => true]),
                ]),
            ]),
        ], $filtered_trees);
    }
}
