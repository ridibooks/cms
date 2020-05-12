<?php


use Phinx\Migration\AbstractMigration;

class AddLastloginAt extends AbstractMigration
{
    public function change()
    {
        $this->table('tb_admin2_user')
            ->addColumn('last_login_at', 'timestamp')
            ->update();
    }
}
