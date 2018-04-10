<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class InitCms extends AbstractMigration
{
    public function change()
    {
        $this->createUser();
        $this->createMenu();
        $this->createTag();
        $this->createUserMenu();
        $this->createUserTag();
        $this->createTagMenu();
        $this->createMenuAjax();
        $this->createUserPermissionLog();
    }

    private function createUser()
    {
        $this->table('tb_admin2_user', ['id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'string', ['length' => 32])
            ->addColumn('passwd', 'string', ['length' => 128])
            ->addColumn('name', 'string', ['length' => 32])
            ->addColumn('team', 'string', ['length' => 32])
            ->addColumn('is_use', 'boolean')
            ->addColumn('reg_date', 'timestamp')
            ->addColumn('azure_id', 'string', ['length' => 32])
            ->create();
    }

    private function createMenu()
    {
        $this->table('tb_admin2_menu')
            ->addColumn('menu_title', 'string', ['length' => 50])
            ->addColumn('menu_url', 'string', ['length' => 200])
            ->addColumn('menu_deep', 'integer')
            ->addColumn('menu_order', 'integer')
            ->addColumn('is_use', 'boolean')
            ->addColumn('is_show', 'boolean')
            ->addColumn('reg_date', 'timestamp')
            ->addColumn('is_newtab', 'boolean')
            ->addTimestamps()
            ->create();
    }

    private function createTag()
    {
        $this->table('tb_admin2_tag')
            ->addColumn('name', 'string', ['length' => 32])
            ->addColumn('is_use', 'boolean')
            ->addColumn('creator', 'string', ['length' => 32])
            ->addColumn('reg_date', 'timestamp')
            ->addTimestamps()
            ->create();
    }

    private function createUserMenu()
    {
        $this->table('tb_admin2_user_menu')
            ->addColumn('user_id', 'string', ['length' => 32])
            ->addColumn('menu_id', 'integer')
            ->addColumn('reg_date', 'timestamp')
            ->addForeignKey('user_id', 'tb_admin2_user', 'id')
            ->addForeignKey('menu_id', 'tb_admin2_menu', 'id')
            ->addIndex('menu_id')
            ->create();
    }

    private function createUserTag()
    {
        $this->table('tb_admin2_user_tag')
            ->addColumn('user_id', 'string', ['length' => 32])
            ->addColumn('tag_id', 'integer')
            ->addForeignKey('user_id', 'tb_admin2_user', 'id')
            ->addForeignKey('tag_id', 'tb_admin2_tag', 'id')
            ->addIndex('tag_id')
            ->create();
    }

    private function createTagMenu()
    {
        $this->table('tb_admin2_tag_menu')
            ->addColumn('tag_id', 'integer')
            ->addColumn('menu_id', 'integer')
            ->addForeignKey('tag_id', 'tb_admin2_tag', 'id')
            ->addForeignKey('menu_id', 'tb_admin2_menu', 'id')
            ->addIndex('menu_id')
            ->create();
    }

    private function createMenuAjax()
    {
        $this->table('tb_admin2_menu_ajax')
            ->addColumn('menu_id', 'integer')
            ->addColumn('ajax_url', 'string', ['length' => 200])
            ->addForeignKey('menu_id', 'tb_admin2_menu', 'id')
            ->addIndex('menu_id')
            ->create();
    }

    private function createUserPermissionLog()
    {
        $this->table('tb_admin2_user_permission_log')
            ->addColumn('user_id', 'string', ['length' => 32])
            ->addColumn('menu_ids', 'string', ['length' => 512])
            ->addColumn('tag_ids', 'string', ['length' => 512])
            ->addColumn('edited_by', 'string', ['length' => 32])
            ->addColumn('created_at', 'timestamp')
            ->create();
    }
}
