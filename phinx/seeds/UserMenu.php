<?php
declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class UserMenu extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'user_id' => 'admin',
                'menu_id' => 3,
            ],
        ];

        $posts = $this->table('tb_admin2_user_menu');
        $posts->insert($data)->save();
    }
}
