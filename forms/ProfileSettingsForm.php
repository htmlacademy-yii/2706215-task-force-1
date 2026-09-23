<?php

declare(strict_types=1);

namespace app\forms;

use app\models\Category;
use app\models\ExecutorProfile;
use app\models\ExecutorSpecialization;
use app\models\User;
use DateTimeImmutable;
use Sanweb\Taskforce\dto\AccountProfileDto;
use yii\base\Model;
use yii\web\UploadedFile;

/**
 * Validates and normalizes account profile settings.
 */
final class ProfileSettingsForm extends Model
{
    public string $name = '';
    public string $email = '';
    public string $birthday = '';
    public ?UploadedFile $avatarFile = null;
    public ?string $phone = null;
    public ?string $telegram = null;
    public ?string $about = null;

    /** @var list<int> */
    public array $categoryIds = [];

    private bool $isExecutor;
    private int $userId;

    /**
     * Initializes profile fields from the current user.
     *
     * @param User $user
     * @param array $config
     */
    public function __construct(User $user, array $config = [])
    {
        $this->isExecutor = (bool) $user->is_executor;
        $this->userId = (int) $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->birthday = $this->formatBirthday($user->birthday);

        if ($this->isExecutor) {
            $profile = $user->executorProfile;
            $this->phone = $profile?->phone === null
                ? null
                : preg_replace('/\D/', '', $profile->phone);
            $this->telegram = $profile?->telegram;
            $this->about = $profile?->about;
            $this->categoryIds = array_map(
                static fn (ExecutorSpecialization $specialization): int => $specialization->category_id,
                $user->executorSpecializations,
            );
        }

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            [['name', 'email', 'birthday', 'phone', 'telegram', 'about'], 'trim'],
            ['email', 'filter', 'filter' => 'mb_strtolower'],
            [['phone', 'telegram', 'about'], 'default', 'value' => null],

            [['name', 'email'], 'required'],
            ['name', 'string', 'min' => 2, 'max' => 128],

            ['email', 'string', 'max' => 255],
            ['email', 'email'],
            [
                'email',
                'unique',
                'targetClass' => User::class,
                'targetAttribute' => 'email',
                'filter' => ['<>', 'id', $this->userId],
                'message' => 'Пользователь с таким Email уже зарегистрирован.',
            ],

            [
                'birthday',
                'match',
                'pattern' => '/^\d{2}\.\d{2}\.\d{4}$/',
                'message' => 'Введите дату в формате дд.мм.гггг.',
            ],
            [
                'birthday',
                'date',
                'format' => 'php:d.m.Y',
            ],

            [
                'avatarFile',
                'image',
                'extensions' => ['png', 'jpg', 'jpeg'],
                'mimeTypes' => ['image/png', 'image/jpeg'],
                'maxSize' => 2 * 1024 * 1024,
            ],

            [
                'phone',
                'match',
                'pattern' => ExecutorProfile::PHONE_PATTERN,
                'message' => ExecutorProfile::PHONE_VALIDATION_MESSAGE,
            ],

            ['telegram', 'string', 'max' => 64],
            ['about', 'string', 'max' => ExecutorProfile::ABOUT_MAX_LENGTH],

            ['categoryIds', 'each', 'rule' => ['integer']],
            [
                'categoryIds',
                'each',
                'rule' => [
                    'exist',
                    'targetClass' => Category::class,
                    'targetAttribute' => 'id',
                ],
            ],
        ];
    }

    /**
     * Checks whether executor-specific profile fields are available.
     *
     * @return bool
     */
    public function isExecutor(): bool
    {
        return $this->isExecutor;
    }

    /**
     * Converts validated profile data to a DTO.
     *
     * @return AccountProfileDto
     */
    public function toDto(): AccountProfileDto
    {
        $birthday = null;

        if ($this->birthday !== '') {
            $date = DateTimeImmutable::createFromFormat('!d.m.Y', $this->birthday);
            $birthday = $date === false ? null : $date->format('Y-m-d');
        }

        return new AccountProfileDto(
            name: $this->name,
            email: $this->email,
            birthday: $birthday,
            phone: $this->isExecutor ? $this->phone : null,
            telegram: $this->isExecutor ? $this->telegram : null,
            about: $this->isExecutor ? $this->about : null,
            categoryIds: $this->isExecutor
                ? array_values(array_unique(array_map('intval', $this->categoryIds)))
                : [],
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'name' => 'Ваше имя',
            'email' => 'Email',
            'birthday' => 'День рождения',
            'avatarFile' => 'Аватар',
            'phone' => 'Номер телефона',
            'telegram' => 'Telegram',
            'about' => 'Информация о себе',
            'categoryIds' => 'Специализации',
        ];
    }

    /**
     * Converts a stored birthday to the profile form format.
     *
     * @param ?string $birthday
     *
     * @return string
     */
    private function formatBirthday(?string $birthday): string
    {
        if ($birthday === null) {
            return '';
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $birthday);

        return $date === false ? '' : $date->format('d.m.Y');
    }
}
