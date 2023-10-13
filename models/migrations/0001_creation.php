<?php

namespace MemberDataForms\Models;

use MemberData\Models\MigrationObject;

class Migration0001 extends MigrationObject
{
    public function up()
    {
        $this->createTable("memberdataforms_form", "(
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(200) NOT NULL,
            `sheet_id` int(11) NULL,
            `settings` TEXT NULL,
            PRIMARY KEY (`id`)) ENGINE=InnoDB");
        return true;
    }

    public function down()
    {
        $this->dropTable("memberdataforms_form");
        return true;
    }
}
