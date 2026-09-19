<?php

use yii\db\Migration;

class m260919_191128_add_comment_to_bid_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%bid}}',
            'comment',
            $this->text()->null()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%bid}}', 'comment');
    }
}
