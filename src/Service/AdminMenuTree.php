<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service;

class AdminMenuTree
{
    private $menu = null;
    private $children = [];

    public static function buildTrees(array $menus): array {
        $root_node = new AdminMenuTree(['menu_deep' => -1, 'menu_url' => '#']);

        $parent_stack = [$root_node];

        for ($i = 0; $i < count($menus); $i++) {
            $node = new AdminMenuTree($menus[$i]);

            $parent = (function () use ($parent_stack, $node) {
                while (end($parent_stack)->menu['menu_deep'] >= $node->menu['menu_deep']) {
                    array_pop($parent_stack);
                }
                return end($parent_stack);
            })();

            $parent->children[] = $node;

            if (!AdminMenuService::isParentMenu($node->menu)) {
                continue;
            }

            if (!isset($menus[$i + 1])) {
                break;
            }

            if ($menus[$i + 1]['menu_deep'] > $node->menu['menu_deep']) {
                $parent_stack[] = $node;
            }
        }

        return $root_node->children;
    }

    public static function flattenTrees(array $trees): array {
        $menus = [];
        foreach ($trees as $node) {
            $menus = array_merge(
                $menus,
                [$node->menu],
                AdminMenuTree::flattenTrees($node->children)
            );
        }
        return $menus;
    }

    public static function filterTreesPostOrder(array $trees, callable $match): array {
        $filtered_nodes = [];

        foreach ($trees as $node) {
            $filtered_children = AdminMenuTree::filterTreesPostOrder($node->getChildren(), $match);

            $node_with_filtered_children = new AdminMenuTree($node->getMenu(), $filtered_children);

            if ($match($node_with_filtered_children)) {
                $filtered_nodes[] = $node_with_filtered_children;
            }
        }

        return $filtered_nodes;
    }

    public function __construct($menu, $children = []) {
        $this->menu = $menu;
        $this->children = array_merge($this->children, $children);
    }

    public function getMenu()
    {
        return $this->menu;
    }

    public function getChildren(): array
    {
        return $this->children;
    }
}
