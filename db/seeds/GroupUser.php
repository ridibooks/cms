<?php 
declare(strict_types=1); 
 
use Phinx\Seed\AbstractSeed; 
 
class GroupUser extends AbstractSeed 
{ 
    public function run() 
    { 
        $data = [ 
            [ 
                'user_id' => 'admin', 
                'group_id' => 1, 
            ], 
        ]; 
 
        $posts = $this->table('tb_admin2_group_user'); 
        $posts->insert($data)->save(); 
    } 
} 
