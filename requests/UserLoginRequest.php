<?php

declare(strict_types=1);

namespace app\requests;

use app\dto\UserLoginDto;
use yii\base\Model;

/**
 * Validates credentials submitted through the login form.
 */
class UserLoginRequest extends Model
{
    public string $email = '';
    public string $password = '';

    /**
     * {@inheritdoc}
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
     */
    public function formName(): string
    {
        return 'login';
    }

    /**
     * Converts validated request data to a DTO.
     */
    public function toDto(): UserLoginDto
    {
        return new UserLoginDto(
            email: $this->email,
            password: $this->password,
        );
    }
}
