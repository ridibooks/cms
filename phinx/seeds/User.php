<?php
declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class User extends AbstractSeed
{
    public function run()
    {
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

        $posts = $this->table('tb_admin2_user');
        $posts->insert($data)->save();
    }
}
