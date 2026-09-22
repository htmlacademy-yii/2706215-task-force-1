<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "city".
 *
 * @property int $id
 * @property string $name
 * @property string $lat Latitude stored as DECIMAL in the database.
 * @property string $lng Longitude stored as DECIMAL in the database.
 *
 * @property Task[] $tasks
 * @property User[] $users
 */
class City extends ActiveRecord
{
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public static function tableName(): string
    {
        return 'city';
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            [['name', 'lat', 'lng'], 'required'],
            [['lat', 'lng'], 'number'],
            [['name'], 'trim'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
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
            'lat' => 'Lat',
            'lng' => 'Lng',
        ];
    }

    /**
     * Gets tasks located in this city.
     *
     * @return ActiveQuery
     */
    public function getTasks(): ActiveQuery
    {
        return $this->hasMany(Task::class, ['city_id' => 'id']);
    }

    /**
     * Gets users from this city.
     *
     * @return ActiveQuery
     */
    public function getUsers(): ActiveQuery
    {
        return $this->hasMany(User::class, ['city_id' => 'id']);
    }
}
