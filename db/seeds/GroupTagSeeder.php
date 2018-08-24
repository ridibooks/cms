<?php 
declare(strict_types=1);
 
class GroupTagSeeder extends BaseSeeder
{
    const TABLE_NAME = 'tb_admin2_group_tag';

    public function getDependencies()
    {
        return [
            'GroupSeeder',
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
                'group_id' => 1, 
                'tag_id' => 2, 
            ], 
        ]; 
 
        $posts = $this->table(self::TABLE_NAME);
        $posts->insert($data)->save(); 
    } 
} 
