<?php
declare(strict_types=1);

class UserTagSeeder extends BaseSeeder
{
    const TABLE_NAME = 'tb_admin2_user_tag';

    public function getDependencies()
    {
        return [
            'UserSeeder',
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
                'user_id' => 'admin',
                'tag_id' => 1,
            ],
        ];

        $posts = $this->table(self::TABLE_NAME);
        $posts->insert($data)->save();
    }
}
