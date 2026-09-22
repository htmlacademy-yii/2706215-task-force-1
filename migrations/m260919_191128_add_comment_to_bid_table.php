<?php

use yii\db\Migration;

/**
 * Adds an optional comment to task bids.
 */
class m260919_191128_add_comment_to_bid_table extends Migration
{
    /**
     * {@inheritdoc}
     *
     * @return void
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
     *
     * @return void
     */
    public function safeDown()
    {
        $this->dropColumn('{{%bid}}', 'comment');
    }
}
