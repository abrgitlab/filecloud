<?php

use yii\db\Migration;
use yii\db\Schema;

class m160506_194907_filecloud extends Migration
{
    public function up() {
        $tableOptions = null;
        $this->createTable('{{%files}}', [
            'id' => $this->primaryKey(),
            'title' => $this->text()->notNull(),
            'shortlink' => $this->text()->notNull(),
            'loading_state' => $this->smallInteger()->notNull(),
            'uploaded_at' => $this->timestamp()->notNull(),
        ], $tableOptions);
    }

    public function down() {
        $this->dropTable('{{%files}}');
        return true;
    }

}
