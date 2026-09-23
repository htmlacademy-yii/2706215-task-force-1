<?php

declare(strict_types=1);

namespace app\models;

use Sanweb\Taskforce\enum\BidStatus;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "bid".
 *
 * @property int $id
 * @property int $user_id
 * @property int $task_id
 * @property int $price
 * @property string|null $comment
 * @property string $status
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property Task $task
 * @property User $user
 */
class Bid extends ActiveRecord
{
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public static function tableName(): string
    {
        return 'bid';
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            [['status'], 'default', 'value' => BidStatus::New->value],
            [['status'], 'in', 'range' => BidStatus::values()],

            [['user_id', 'task_id', 'price'], 'required'],
            [['user_id', 'task_id'], 'integer'],
            [['price'], 'integer', 'min' => 1],
            [['comment'], 'string', 'max' => 1000],

            [['user_id', 'task_id'], 'unique', 'targetAttribute' => ['user_id', 'task_id']],

            [['task_id'], 'exist', 'targetClass' => Task::class, 'targetAttribute' => ['task_id' => 'id']],
            [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
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
            'user_id' => 'User ID',
            'task_id' => 'Task ID',
            'price' => 'Price',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets the task this bid belongs to.
     *
     * @return ActiveQuery
     */
    public function getTask(): ActiveQuery
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }

    /**
     * Gets the user who submitted this bid.
     *
     * @return ActiveQuery
     */
    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
