<?php

use Phinx\Migration\AbstractMigration;

class AddGroup extends AbstractMigration
{
    public function change()
    {
        $this->table('tb_admin2_group')
            ->addColumn('name', 'string', ['length' => 32])
            ->addColumn('is_use', 'boolean')
            ->addColumn('creator', 'string', ['length' => 32])
            ->addIndex('name')
            ->addTimestamps()
            ->create();

        $this->table('tb_admin2_group_user')
            ->addColumn('group_id', 'integer')
            ->addColumn('user_id', 'string', ['length' => 32])
            ->addForeignKey('group_id', 'tb_admin2_group', 'id') 
            ->addForeignKey('user_id', 'tb_admin2_user', 'id') 
            ->addIndex('user_id')
            ->create();

        $this->table('tb_admin2_group_tag')
            ->addColumn('group_id', 'integer')
            ->addColumn('tag_id', 'integer')
            ->addForeignKey('group_id', 'tb_admin2_group', 'id') 
            ->addForeignKey('tag_id', 'tb_admin2_tag', 'id') 
            ->addIndex('tag_id')
            ->create();
    }
}
