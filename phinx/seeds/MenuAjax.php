<?php
declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class MenuAjax extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'menu_id' => 3,
                'ajax_url' => '/example/resource1/ajax1',
            ],
            [
                'menu_id' => 3,
                'ajax_url' => '/example/resource1/ajax2',
            ],
            [
                'menu_id' => 3,
                'ajax_url' => '/example/resource1/ajax3',
            ],
        ];

        $posts = $this->table('tb_admin2_menu_ajax');
        $posts->insert($data)->save();
    }
}
