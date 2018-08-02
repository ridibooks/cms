<?php

use Phinx\Migration\AbstractMigration;

class AddGroup extends AbstractMigration
{
    public function change()
    {
        $this->table('tb_admin2_group')
            ->addColumn('name', 'string', ['length' => 32, 'collation' => 'utf8_unicode_ci'])
            ->addColumn('is_use', 'boolean')
            ->addColumn('creator', 'string', ['length' => 32, 'collation' => 'utf8_unicode_ci'])
            ->addIndex('name', [ 'unique' => true ])
            ->addTimestamps()
            ->create();

        $this->table('tb_admin2_group_user')
            ->addColumn('group_id', 'integer')
            ->addColumn('user_id', 'string', ['length' => 32, 'collation' => 'utf8_unicode_ci'])
            ->addForeignKey('group_id', 'tb_admin2_group', 'id') 
            ->addForeignKey('user_id', 'tb_admin2_user', 'id') 
            ->create();

        $this->table('tb_admin2_group_tag')
            ->addColumn('group_id', 'integer')
            ->addColumn('tag_id', 'integer')
            ->addForeignKey('group_id', 'tb_admin2_group', 'id') 
            ->addForeignKey('tag_id', 'tb_admin2_tag', 'id') 
            ->create();
    }
}
