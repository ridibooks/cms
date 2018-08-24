<?php


use Phinx\Migration\AbstractMigration;

class AddEmailColumn extends AbstractMigration
{
    public function change()
    {
        $this->table('tb_admin2_user')
            ->addColumn('email', 'string', ['length' => 128, 'after' => 'passwd', 'collation' => 'utf8_unicode_ci', 'default' => ''])
            ->update();
    }
}
