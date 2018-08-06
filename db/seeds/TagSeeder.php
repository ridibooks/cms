<?php
declare(strict_types=1);

class TagSeeder extends BaseSeeder
{
    const TABLE_NAME = 'tb_admin2_tag';

    public function run()
    {
        if (!$this->isTableEmpty(self::TABLE_NAME)) {
            return;
        }

        $data = [
            [
                'name' => '권한 관리',
                'display_name' => '권한 관리',
                'is_use' => 1,
                'creator' => 'admin',
                'reg_date' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => '테스트',
                'display_name' => '테스트',
                'is_use' => 1,
                'creator' => 'admin',
                'reg_date' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => '안쓰는 태그',
                'display_name' => '안쓰는 태그',
                'is_use' => 0,
                'creator' => 'admin',
                'reg_date' => date('Y-m-d H:i:s'),
            ],
        ];

        $posts = $this->table(self::TABLE_NAME);
        $posts->insert($data)->save();
    }
}
