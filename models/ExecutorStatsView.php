<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $executor_id
 * @property string $name
 * @property int $completed_tasks
 * @property int $failed_tasks
 * @property float|null $avg_score
 * @property int $rating_position
 */
class ExecutorStatsView extends ActiveRecord
{
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public static function tableName(): string
    {
        return 'executor_stats_view';
    }
}
