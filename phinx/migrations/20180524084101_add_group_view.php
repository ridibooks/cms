<?php

use Phinx\Migration\AbstractMigration;

class AddGroupView extends AbstractMigration
{
    private $VIEW_NAME = 'vw_admin2_user_tag_via_group';

    public function up()
    {
        $this->execute("create view {$this->VIEW_NAME} AS
            select user_id, tag_id
            from tb_admin2_user_tag
            union
            select group_user.user_id, group_tag.tag_id
            from tb_admin2_group_tag as group_tag
            join tb_admin2_group_user as group_user on group_user.group_id = group_tag.group_id;
        ");
    }

    public function down()
    {
        $this->execute("drop view {$this->VIEW_NAME}");
    }
}
