<?php

declare(strict_types=1);

namespace app\forms;

use app\models\User;
use app\validators\CurrentPasswordValidator;
use Sanweb\Taskforce\dto\AccountSecurityDto;
use yii\base\Model;

/**
 * Form for changing the password and contact visibility.
 */
final class SecuritySettingsForm extends Model
{
    public string $oldPassword = '';
    public string $newPassword = '';
    public string $newPasswordRepeat = '';
    public bool|int $hideMyContacts = false;

    private bool $isExecutor;
    private bool $canChangePassword;
    private ?string $passwordHash;

    /**
     * Initializes security settings from the current user.
     *
     * @param User $user
     * @param array $config
     */
    public function __construct(User $user, array $config = [])
    {
        $this->isExecutor = (bool) $user->is_executor;
        $this->canChangePassword = $user->password !== null;
        $this->passwordHash = $user->password;

        if ($this->isExecutor) {
            $profile = $user->executorProfile;
            $this->hideMyContacts = $profile === null
                ? false
                : (bool) $profile->hide_my_contacts;
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
            ['hideMyContacts', 'boolean'],
            [
                ['oldPassword', 'newPassword', 'newPasswordRepeat'],
                'required',
                'when' => fn(): bool => $this->isPasswordChangeRequested(),
                'enableClientValidation' => false,
            ],
            [
                'oldPassword',
                CurrentPasswordValidator::class,
                'passwordHash' => $this->passwordHash ?? '',
            ],
            [ 'newPassword', 'string', 'min' => 8, ],
            [
                'newPasswordRepeat',
                'compare',
                'compareAttribute' => 'newPassword',
                'message' => 'Пароли не совпадают.',
            ],
        ];
    }

    /**
     * Whether executor-only security settings are available.
     *
     * @return bool
     */
    public function isExecutor(): bool
    {
        return $this->isExecutor;
    }

    /**
     * Whether the account has a password that can be changed.
     *
     * @return bool
     */
    public function canChangePassword(): bool
    {
        return $this->canChangePassword;
    }

    /**
     * Converts validated settings to a DTO.
     *
     * @return AccountSecurityDto
     */
    public function toDto(): AccountSecurityDto
    {
        return new AccountSecurityDto(
            newPassword: $this->canChangePassword && $this->isPasswordChangeRequested()
                ? $this->newPassword
                : null,
            hideMyContacts: $this->isExecutor && (bool) $this->hideMyContacts,
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
            'oldPassword' => 'Старый пароль',
            'newPassword' => 'Новый пароль',
            'newPasswordRepeat' => 'Повтор нового пароля',
            'hideMyContacts' => 'Скрыть контакты',
        ];
    }

    /**
     * Whether any password-change field has been filled in.
     *
     * @return bool
     */
    private function isPasswordChangeRequested(): bool
    {
        return $this->oldPassword !== ''
            || $this->newPassword !== ''
            || $this->newPasswordRepeat !== '';
    }
}
