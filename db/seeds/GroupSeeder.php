<?php 
declare(strict_types=1);
 
class GroupSeeder extends BaseSeeder
{
    const TABLE_NAME = 'tb_admin2_group';

    public function run() 
    {
        if (!$this->isTableEmpty(self::TABLE_NAME)) {
            return;
        }

        $data = [ 
            [ 
                'name' => 'my_team', 
                'is_use' => 1, 
                'creator' => 'admin', 
            ], 
        ]; 
 
        $posts = $this->table(self::TABLE_NAME);
        $posts->insert($data)->save(); 
    } 
} 
