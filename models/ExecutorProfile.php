<?php

declare(strict_types=1);

namespace app\models;

use Sanweb\Taskforce\enum\ExecutorStatus;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "executor_profile".
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $phone
 * @property string|null $telegram
 * @property string|null $about
 * @property int $hide_my_contacts
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property User $user
 */
class ExecutorProfile extends ActiveRecord
{
    public const string PHONE_PATTERN = '/^7\d{10}$/';
    public const string PHONE_VALIDATION_MESSAGE = 'Введите номер в формате 7XXXXXXXXXX.';
    public const int ABOUT_MAX_LENGTH = 1000;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'executor_profile';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['phone', 'telegram', 'about'], 'trim'],
            [['phone', 'telegram', 'about'], 'default', 'value' => null],
            [['hide_my_contacts'], 'default', 'value' => 0],

            [['user_id'], 'required'],
            [['user_id'], 'integer'],

            [['about'], 'string', 'max' => self::ABOUT_MAX_LENGTH],

            [['hide_my_contacts'], 'boolean'],

            [
                ['phone'],
                'match',
                'pattern' => self::PHONE_PATTERN,
                'message' => self::PHONE_VALIDATION_MESSAGE,
            ],

            [['telegram'], 'string', 'max' => 64],

            [['user_id'], 'unique'],
            [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'phone' => 'Phone',
            'telegram' => 'Telegram',
            'about' => 'About',
            'hide_my_contacts' => 'Hide My Contacts',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Returns the executor who owns this profile.
     */
    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Returns the executor status label.
     */
    public function getStatusLabel(): string
    {
        return ExecutorStatus::from($this->status)->label();
    }
}
