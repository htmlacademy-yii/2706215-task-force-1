<?php

declare(strict_types=1);

namespace app\validators;

use yii\base\Model;
use yii\validators\Validator;

/**
 * Requires coordinates when an address is selected for a task.
 */
final class LocationCoordinatesValidator extends Validator
{
    public string $latitudeAttribute = 'latitude';

    public string $longitudeAttribute = 'longitude';

    /**
     * {@inheritdoc}
     *
     * @param Model $model
     * @param string $attribute
     *
     * @return void
     */
    public function validateAttribute($model, $attribute): void
    {
        $latitude = $model->{$this->latitudeAttribute};
        $longitude = $model->{$this->longitudeAttribute};

        if ($this->isEmpty($latitude) || $this->isEmpty($longitude)) {
            $this->addError($model, $attribute, 'Выберите адрес из списка.');
        }
    }
}
