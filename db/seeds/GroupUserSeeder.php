<?php 
declare(strict_types=1); 

class GroupUserSeeder extends BaseSeeder
{
    const TABLE_NAME = 'tb_admin2_group_user';

    public function getDependencies()
    {
        return [
            'GroupSeeder',
            'UserSeeder',
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
                'group_id' => 1, 
            ], 
        ]; 
 
        $posts = $this->table(self::TABLE_NAME);
        $posts->insert($data)->save(); 
    } 
} 
