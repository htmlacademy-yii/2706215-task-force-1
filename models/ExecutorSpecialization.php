<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "executor_specialization".
 *
 * @property int $id
 * @property int $user_id
 * @property int $category_id
 * @property string $created_at
 *
 * @property Category $category
 * @property User $user
 */
class ExecutorSpecialization extends ActiveRecord
{
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public static function tableName(): string
    {
        return 'executor_specialization';
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            [['user_id', 'category_id'], 'required'],
            [['user_id', 'category_id'], 'integer'],
            [['user_id', 'category_id'], 'unique', 'targetAttribute' => ['user_id', 'category_id']],
            [['category_id'], 'exist', 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
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
            'category_id' => 'Category ID',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Returns the specialization category.
     *
     * @return ActiveQuery
     */
    public function getCategory(): ActiveQuery
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * Returns the executor associated with this specialization.
     *
     * @return ActiveQuery
     */
    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
