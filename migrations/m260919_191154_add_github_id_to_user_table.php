<?php

use yii\db\Migration;

/**
 * Adds a unique GitHub account identifier to users.
 */
class m260919_191154_add_github_id_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%user}}',
            'github_id',
            $this->bigInteger()->unsigned()->null(),
        );

        $this->createIndex(
            'uq_user_github_id',
            '{{%user}}',
            'github_id',
            true,
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function safeDown()
    {
        $this->dropIndex('uq_user_github_id', '{{%user}}');
        $this->dropColumn('{{%user}}', 'github_id');
    }
}
