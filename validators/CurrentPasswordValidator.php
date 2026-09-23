<?php

declare(strict_types=1);

namespace app\validators;

use Yii;
use yii\validators\Validator;

/**
 * Validates a plaintext password against the stored password hash.
 */
final class CurrentPasswordValidator extends Validator
{
    public string $passwordHash = '';

    /**
     * {@inheritdoc}
     *
     * @param mixed $model
     * @param mixed $attribute
     *
     * @return void
     */
    public function validateAttribute($model, $attribute): void
    {
        $password = $model->$attribute;

        if (
            !is_string($password)
            || $this->passwordHash === ''
            || !Yii::$app->security->validatePassword($password, $this->passwordHash)
        ) {
            $this->addError($model, $attribute, 'Старый пароль указан неверно.');
        }
    }
}
