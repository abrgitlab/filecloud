<?php

use yii\db\Migration;

class m160516_105113_users extends Migration
{
    public function up()
    {
        $tableOptions = null;
        $this->createTable('{{%users}}', [
            'id' => $this->primaryKey(),
            'login' => $this->text()->notNull(),
            'email' => $this->text()->notNull(),
            'password' => $this->string(32)->notNull(),
            'first_name' => $this->text()->notNull(),
            'last_name' => $this->text()->notNull(),
            'active' => $this->boolean()->defaultValue(true)->notNull(),
        ], $tableOptions);

        $this->addColumn('{{%files}}', 'user_id', $this->integer()->notNull());
        $this->addForeignKey('{{%fk_files_users}}', '{{%files}}', 'user_id', '{{%users}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('{{%fk_files_users}}', '{{%files}}');
        $this->dropColumn('{{%files}}', 'user_id');
        $this->dropTable('{{%users}}');

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
