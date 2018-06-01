<?php
declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class Tag extends AbstractSeed
{
    public function run()
    {
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

        $posts = $this->table('tb_admin2_tag');
        $posts->insert($data)->save();
    }
}
