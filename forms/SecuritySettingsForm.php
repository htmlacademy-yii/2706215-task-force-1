<?php

declare(strict_types=1);

namespace app\forms;

use app\models\User;
use app\validators\CurrentPasswordValidator;
use Sanweb\Taskforce\dto\AccountSecurityDto;
use yii\base\Model;

final class SecuritySettingsForm extends Model
{
    public string $oldPassword = '';
    public string $newPassword = '';
    public string $newPasswordRepeat = '';
    public bool|int $hideMyContacts = false;

    private bool $isExecutor;
    private bool $canChangePassword;
    private ?string $passwordHash;

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

    public function isExecutor(): bool
    {
        return $this->isExecutor;
    }

    public function canChangePassword(): bool
    {
        return $this->canChangePassword;
    }

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

    private function isPasswordChangeRequested(): bool
    {
        return $this->oldPassword !== ''
            || $this->newPassword !== ''
            || $this->newPasswordRepeat !== '';
    }
}
