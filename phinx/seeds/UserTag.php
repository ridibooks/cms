<?php
declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class UserTag extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'user_id' => 'admin',
                'tag_id' => 1,
            ],
        ];

        $posts = $this->table('tb_admin2_user_tag');
        $posts->insert($data)->save();
    }
}
