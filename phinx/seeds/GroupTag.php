<?php 
declare(strict_types=1); 
 
use Phinx\Seed\AbstractSeed; 
 
class GroupTag extends AbstractSeed 
{ 
    public function run() 
    { 
        $data = [ 
            [ 
                'group_id' => 1, 
                'tag_id' => 2, 
            ], 
        ]; 
 
        $posts = $this->table('tb_admin2_group_tag'); 
        $posts->insert($data)->save(); 
    } 
} 
