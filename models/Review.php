<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "review".
 *
 * @property int $id
 * @property int $customer_id
 * @property int $task_id
 * @property int $executor_id
 * @property int $score
 * @property string|null $comment
 * @property string $created_at
 *
 * @property User $customer
 * @property User $executor
 * @property Task $task
 */
class Review extends ActiveRecord
{
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public static function tableName(): string
    {
        return 'review';
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            [['comment'], 'trim'],
            [['comment'], 'default', 'value' => null],

            [['customer_id', 'task_id', 'executor_id', 'score'], 'required'],
            [['customer_id', 'task_id', 'executor_id'], 'integer'],
            [['score'], 'integer', 'min' => 1, 'max' => 5],

            [['comment'], 'string'],
            [['task_id'], 'unique'],

            [['customer_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => ['customer_id' => 'id']],
            [['executor_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => ['executor_id' => 'id']],
            [['task_id'], 'exist', 'targetClass' => Task::class, 'targetAttribute' => ['task_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'customer_id' => 'Customer ID',
            'task_id' => 'Task ID',
            'executor_id' => 'Executor ID',
            'score' => 'Score',
            'comment' => 'Comment',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Returns the customer who created the review.
     *
     * @return ActiveQuery
     */
    public function getCustomer(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'customer_id']);
    }

    /**
     * Returns the executor being reviewed.
     *
     * @return ActiveQuery
     */
    public function getExecutor(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'executor_id']);
    }

    /**
     * Returns the task associated with this review.
     *
     * @return ActiveQuery
     */
    public function getTask(): ActiveQuery
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }
}
