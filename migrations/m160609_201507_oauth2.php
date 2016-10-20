<?php

use yii\db\Migration;

class m160609_201507_oauth2 extends Migration
{
    public function up() {
        $this->addColumn('{{%users}}', 'tokens_valid_after', $this->dateTime());
        $this->addColumn('{{%users}}', 'secret', $this->text()->notNull());
    }

    public function down() {
        $this->dropColumn('{{%users}}', 'tokens_valid_after');
        $this->dropColumn('{{%users}}', 'secret');

        return true;
    }

}
