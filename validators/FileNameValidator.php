<?php

declare(strict_types=1);

namespace app\validators;

use yii\validators\Validator;

/**
 * Validates uploaded file names against the database column length.
 */
final class FileNameValidator extends Validator
{
    public function validateAttribute($model, $attribute): void
    {
        foreach ($model->$attribute as $file) {
            if (mb_strlen(basename(str_replace('\\', '/', $file->name))) > 255) {
                $this->addError($model, $attribute, 'Имя файла не должно превышать 255 символов.');

                return;
            }
        }
    }
}
