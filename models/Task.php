<?php

declare(strict_types=1);

namespace app\models;

use Sanweb\Taskforce\enum\TaskStatus;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "task".
 *
 * @property int $id
 * @property int $customer_id
 * @property int $category_id
 * @property int|null $executor_id
 * @property string $title
 * @property string $description
 * @property int $budget
 * @property string $expire_date
 * @property string $status
 * @property string|null $location
 * @property int|null $city_id
 * @property string|null $lat
 * @property string|null $lng
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property Attachment[] $attachments
 * @property Bid[] $bids
 * @property Category $category
 * @property City $city
 * @property User $customer
 * @property User $executor
 * @property Review $review
 * @property User[] $bidders
 */
class Task extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'task';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['title', 'description', 'location'], 'trim'],
            [['executor_id', 'location', 'city_id', 'lat', 'lng'], 'default', 'value' => null],
            [['status'], 'default', 'value' => TaskStatus::New->value],

            [['customer_id', 'category_id', 'title', 'description', 'budget', 'expire_date'], 'required'],
            [['customer_id', 'category_id', 'executor_id', 'city_id'], 'integer'],
            [['budget'], 'integer', 'min' => 1],
            [['description'], 'string'],
            [['expire_date'], 'date', 'format' => 'php:Y-m-d'],
            [['lat', 'lng'], 'number'],
            [['title', 'location'], 'string', 'max' => 255],

            [['status'], 'in', 'range' => TaskStatus::values()],

            [['category_id'], 'exist', 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
            [['city_id'], 'exist', 'targetClass' => City::class, 'targetAttribute' => ['city_id' => 'id']],
            [['customer_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => ['customer_id' => 'id']],
            [['executor_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => ['executor_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'customer_id' => 'Customer ID',
            'category_id' => 'Category ID',
            'executor_id' => 'Executor ID',
            'title' => 'Title',
            'description' => 'Description',
            'budget' => 'Budget',
            'expire_date' => 'Expire Date',
            'status' => 'Status',
            'location' => 'Location',
            'city_id' => 'City ID',
            'lat' => 'Lat',
            'lng' => 'Lng',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Returns the attachments associated with this task.
     */
    public function getAttachments(): ActiveQuery
    {
        return $this->hasMany(Attachment::class, ['task_id' => 'id']);
    }

    /**
     * Returns the bids submitted for this task.
     */
    public function getBids(): ActiveQuery
    {
        return $this->hasMany(Bid::class, ['task_id' => 'id']);
    }

    /**
     * Returns the category of this task.
     */
    public function getCategory(): ActiveQuery
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * Returns the city associated with this task.
     */
    public function getCity(): ActiveQuery
    {
        return $this->hasOne(City::class, ['id' => 'city_id']);
    }

    /**
     * Returns the customer who created this task.
     */
    public function getCustomer(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'customer_id']);
    }

    /**
     * Returns the executor assigned to this task.
     */
    public function getExecutor(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'executor_id']);
    }

    /**
     * Returns the review associated with this task.
     */
    public function getReview(): ActiveQuery
    {
        return $this->hasOne(Review::class, ['task_id' => 'id']);
    }

    /**
     * Returns the users who submitted bids for this task.
     *
     * @throws InvalidConfigException
     */
    public function getBidders(): ActiveQuery
    {
        return $this->hasMany(
            User::class,
            ['id' => 'user_id']
        )->viaTable('bid', ['task_id' => 'id']);
    }

    /**
     * Returns the localized task status label.
     */
    public function getStatusLabel(): string
    {
        return TaskStatus::from($this->status)->label();
    }
}
