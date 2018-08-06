<?php
declare(strict_types=1);

class UserSeeder extends BaseSeeder
{
    const TABLE_NAME = 'tb_admin2_user';

    public function run()
    {
        if (!$this->isTableEmpty(self::TABLE_NAME)) {
            return;
        }

        $data = [
            [
                'id' => 'admin',
                'name' => '관리자',
                'passwd' => '',
                'team' => '관리자',
                'is_use' => 1,
                'reg_date' => date('Y-m-d H:i:s'),
                'email' => 'admin@ridi.com',
            ],
        ];

        $posts = $this->table(self::TABLE_NAME);
        $posts->insert($data)->save();
    }
}
