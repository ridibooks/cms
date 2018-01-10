<?php
declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class Menu extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'menu_title' => '예제',
                'menu_url' => '#',
                'menu_order' => 0,
                'menu_deep' => 0,
                'is_use' => 1,
                'is_show' => 1,
                'is_newtab' => 0,
                'reg_date' => date('Y-m-d H:i:s'),
            ],
            [
                'menu_title' => '메뉴',
                'menu_url' => '/example/home',
                'menu_order' => 1,
                'menu_deep' => 1,
                'is_use' => 1,
                'is_show' => 1,
                'is_newtab' => 0,
                'reg_date' => date('Y-m-d H:i:s'),
            ],
            [
                'menu_title' => '링크1',
                'menu_url' => '/example/resource1',
                'menu_order' => 2,
                'menu_deep' => 1,
                'is_use' => 1,
                'is_show' => 1,
                'is_newtab' => 0,
                'reg_date' => date('Y-m-d H:i:s'),
            ],
            [
                'menu_title' => '링크2',
                'menu_url' => '/example/resource2',
                'menu_order' => 3,
                'menu_deep' => 1,
                'is_use' => 1,
                'is_show' => 1,
                'is_newtab' => 0,
                'reg_date' => date('Y-m-d H:i:s'),
            ],
        ];

        $posts = $this->table('tb_admin2_menu');
        $posts->insert($data)->save();
    }
}
