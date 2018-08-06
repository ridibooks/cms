<?php
declare(strict_types=1);

class MenuAjaxSeeder extends BaseSeeder
{
    const TABLE_NAME = 'tb_admin2_menu_ajax';

    public function getDependencies()
    {
        return [
            'MenuSeeder',
        ];
    }

    public function run()
    {
        if (!$this->isTableEmpty(self::TABLE_NAME)) {
            return;
        }

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

        $posts = $this->table(self::TABLE_NAME);
        $posts->insert($data)->save();
    }
}
