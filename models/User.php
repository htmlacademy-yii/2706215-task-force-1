<?php

declare(strict_types=1);

namespace app\models;

use DateTimeImmutable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property int|null $github_id
 * @property string $email
 * @property string $name
 * @property string|null $password
 * @property int|null $city_id
 * @property string|null $avatar
 * @property string|null $birthday
 * @property int $is_executor
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property-read Bid[] $bids
 * @property-read Category[] $categories
 * @property-read City|null $city
 * @property-read ExecutorProfile|null $executorProfile
 * @property-read ExecutorSpecialization[] $executorSpecializations
 * @property-read ExecutorStatsView|null $executorStats
 * @property-read Review[] $sentReviews
 * @property-read Review[] $receivedReviews
 * @property-read Task[] $customerTasks
 * @property-read Task[] $executorTasks
 * @property-read Task[] $bidTasks
 * @property-read int|null $age
 */
class User extends ActiveRecord implements IdentityInterface
{
    /**
     * Stores a secure hash of the given plain-text password.
     *
     * @param string $plainPassword
     *
     * @return void
     */
    public function setPassword(string $plainPassword): void
    {
        $this->password = Yii::$app->security->generatePasswordHash($plainPassword);
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $id
     *
     * @return ?static
     */
    public static function findIdentity($id): ?static
    {
        return static::findOne($id);
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $token
     * @param mixed $type
     *
     * @return ?static
     */
    public static function findIdentityByAccessToken($token, $type = null): ?static
    {
        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @return int|string
     */
    public function getId(): int|string
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     *
     * @return ?string
     */
    public function getAuthKey(): ?string
    {
        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $authKey
     *
     * @return bool
     */
    public function validateAuthKey($authKey): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public static function tableName(): string
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            [['github_id', 'password', 'city_id', 'avatar', 'birthday'], 'default', 'value' => null],
            [['is_executor'], 'default', 'value' => 0],

            [['email', 'name'], 'trim'],
            [['email', 'name'], 'required'],

            [['city_id'], 'integer'],
            [['github_id'], 'integer', 'min' => 1],
            [['is_executor'], 'boolean'],

            [['birthday'], 'date', 'format' => 'php:Y-m-d'],

            [['email', 'password', 'avatar'], 'string', 'max' => 255],
            [['name'], 'string', 'max' => 128],

            [['email'], 'email'],
            [['email'], 'unique'],
            [['github_id'], 'unique'],

            [['city_id'], 'exist', 'targetClass' => City::class, 'targetAttribute' => ['city_id' => 'id']],
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
            'github_id' => 'GitHub ID',
            'email' => 'Email',
            'name' => 'Name',
            'password' => 'Password',
            'city_id' => 'City ID',
            'avatar' => 'Avatar',
            'birthday' => 'Birthday',
            'is_executor' => 'Is Executor',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets bids submitted by this user.
     *
     * @return ActiveQuery
     */
    public function getBids(): ActiveQuery
    {
        return $this->hasMany(Bid::class, ['user_id' => 'id']);
    }

    /**
     * Gets categories this user specializes in.
     *
     * @return ActiveQuery
     *
     * @throws InvalidConfigException
     */
    public function getCategories(): ActiveQuery
    {
        return $this->hasMany(
            Category::class,
            ['id' => 'category_id']
        )->viaTable('executor_specialization', ['user_id' => 'id']);
    }

    /**
     * Gets the user's city.
     *
     * @return ActiveQuery
     */
    public function getCity(): ActiveQuery
    {
        return $this->hasOne(City::class, ['id' => 'city_id']);
    }

    /**
     * Gets the user's executor profile.
     *
     * @return ActiveQuery
     */
    public function getExecutorProfile(): ActiveQuery
    {
        return $this->hasOne(ExecutorProfile::class, ['user_id' => 'id']);
    }

    /**
     * Gets the user's executor specializations.
     *
     * @return ActiveQuery
     */
    public function getExecutorSpecializations(): ActiveQuery
    {
        return $this->hasMany(ExecutorSpecialization::class, ['user_id' => 'id']);
    }

    /**
     * Gets the executor statistics.
     *
     * @return ActiveQuery
     */
    public function getExecutorStats(): ActiveQuery
    {
        return $this->hasOne(ExecutorStatsView::class, ['executor_id' => 'id']);
    }

    /**
     * Gets reviews sent by this user.
     *
     * @return ActiveQuery
     */
    public function getSentReviews(): ActiveQuery
    {
        return $this->hasMany(Review::class, ['customer_id' => 'id']);
    }

    /**
     * Gets reviews received by this user.
     *
     * @return ActiveQuery
     */
    public function getReceivedReviews(): ActiveQuery
    {
        return $this->hasMany(Review::class, ['executor_id' => 'id']);
    }

    /**
     * Gets tasks created by this user.
     *
     * @return ActiveQuery
     */
    public function getCustomerTasks(): ActiveQuery
    {
        return $this->hasMany(Task::class, ['customer_id' => 'id']);
    }

    /**
     * Gets tasks assigned to this user.
     *
     * @return ActiveQuery
     */
    public function getExecutorTasks(): ActiveQuery
    {
        return $this->hasMany(Task::class, ['executor_id' => 'id']);
    }

    /**
     * Gets tasks this user has bid on.
     *
     * @return ActiveQuery
     *
     * @throws InvalidConfigException
     */
    public function getBidTasks(): ActiveQuery
    {
        return $this->hasMany(
            Task::class,
            ['id' => 'task_id']
        )->viaTable('bid', ['user_id' => 'id']);
    }

    /**
     * Returns the user's age in full years, or null when the birthday is not set.
     *
     * @return ?int
     *
     * @throws \DateMalformedStringException If the birthday contains an invalid date.
     */
    public function getAge(): ?int
    {
        if ($this->birthday === null) {
            return null;
        }

        return (new DateTimeImmutable($this->birthday))->diff(new DateTimeImmutable())->y;
    }
}
