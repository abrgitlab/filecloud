<?php

use yii\db\Migration;
use yii\db\Schema;

class m160506_194907_filecloud extends Migration
{
    public function up()
    {
        $tableOptions = null;
        $this->createTable('{{%files}}', [
            'id' => $this->primaryKey(),
            'title' => $this->text()->notNull(),
            'shortlink' => $this->text()->notNull(),
            'uploaded_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%files}}');
        return true;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
