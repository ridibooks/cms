<?php


use Phinx\Seed\AbstractSeed;

class Tag extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'name' => '관리자그룹',
                'is_use' => 1,
                'creator' => 'admin',
                'reg_date' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => '퍼포먼스팀',
                'is_use' => 1,
                'creator' => 'admin',
                'reg_date' => date('Y-m-d H:i:s'),
            ],
        ];

        $posts = $this->table('tb_admin2_tag');
        $posts->insert($data)->save();
    }
}
