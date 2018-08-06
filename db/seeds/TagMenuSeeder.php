<?php
declare(strict_types=1);

class TagMenuSeeder extends BaseSeeder
{
    const TABLE_NAME = 'tb_admin2_tag_menu';

    public function getDependencies()
    {
        return [
            'MenuSeeder',
            'TagSeeder',
        ];
    }

    public function run()
    {
        if (!$this->isTableEmpty(self::TABLE_NAME)) {
            return;
        }

        $data = [
            [
                'tag_id' => 1,
                'menu_id' => 2,
            ],
            [
                'tag_id' => 1,
                'menu_id' => 3,
            ],
            [
                'tag_id' => 1,
                'menu_id' => 4,
            ],
            [
                'tag_id' => 1,
                'menu_id' => 5,
            ],
            [
                'tag_id' => 1,
                'menu_id' => 6,
            ],
            [
                'tag_id' => 2,
                'menu_id' => 7,
            ],
            [
                'tag_id' => 1,
                'menu_id' => 8,
            ],
        ];

        $posts = $this->table(self::TABLE_NAME);
        $posts->insert($data)->save();
    }
}
