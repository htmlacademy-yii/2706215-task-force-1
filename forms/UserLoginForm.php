<?php

declare(strict_types=1);

namespace app\forms;

use Sanweb\Taskforce\dto\UserLoginDto;
use yii\base\Model;

/**
 * Validates credentials submitted through the login form.
 */
class UserLoginForm extends Model
{
    public string $email = '';
    public string $password = '';

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            [['email', 'password'], 'required'],

            ['email', 'string', 'max' => 255],
            ['email', 'email'],

            ['password', 'string'],
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
            'email' => 'Email',
            'password' => 'Пароль',
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function formName(): string
    {
        return 'login';
    }

    /**
     * Converts validated request data to a DTO.
     *
     * @return UserLoginDto
     */
    public function toDto(): UserLoginDto
    {
        return new UserLoginDto(
            email: $this->email,
            password: $this->password,
        );
    }
}
