<?php

use yii\db\Migration;

class m260919_191146_add_status_to_executor_profile_table extends Migration
{
    private const STATUS_CHECK_CONSTRAINT = 'chk_executor_profile_status';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%executor_profile}}',
            'status',
            $this->string(32)->notNull()->defaultValue('available')
        );

        $this->addCheck(
            self::STATUS_CHECK_CONSTRAINT,
            '{{%executor_profile}}',
            "[[status]] IN ('available', 'busy', 'unavailable')"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropCheck(
            self::STATUS_CHECK_CONSTRAINT,
            '{{%executor_profile}}'
        );

        $this->dropColumn('{{%executor_profile}}', 'status');
    }
}
