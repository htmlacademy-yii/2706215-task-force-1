<?php

declare(strict_types=1);

namespace app\models;

use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "category".
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 *
 * @property ExecutorSpecialization[] $executorSpecializations
 * @property Task[] $tasks
 * @property User[] $users
 */
class Category extends ActiveRecord
{
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public static function tableName(): string
    {
        return 'category';
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            [['name', 'slug'], 'required'],
            [['name', 'slug'], 'trim'],
            [['name', 'slug'], 'string', 'max' => 64],
            [['name'], 'unique'],
            [['slug'], 'unique'],
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
            'name' => 'Name',
            'slug' => 'Slug',
        ];
    }

    /**
     * Gets executor specializations for this category.
     *
     * @return ActiveQuery
     */
    public function getExecutorSpecializations(): ActiveQuery
    {
        return $this->hasMany(ExecutorSpecialization::class, ['category_id' => 'id']);
    }

    /**
     * Gets tasks in this category.
     *
     * @return ActiveQuery
     */
    public function getTasks(): ActiveQuery
    {
        return $this->hasMany(Task::class, ['category_id' => 'id']);
    }

    /**
     * Gets users specialized in this category.
     *
     * @return ActiveQuery
     *
     * @throws InvalidConfigException
     */
    public function getUsers(): ActiveQuery
    {
        return $this->hasMany(
            User::class,
            ['id' => 'user_id']
        )->viaTable('executor_specialization', ['category_id' => 'id']);
    }
}
