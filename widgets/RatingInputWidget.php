<?php

declare(strict_types=1);

namespace app\widgets;

use app\assets\RatingInputAsset;
use yii\helpers\Html;
use yii\widgets\InputWidget;

final class RatingInputWidget extends InputWidget
{
    private const int MAX_STARS = 5;

    public string $size = RatingWidget::SIZE_SMALL;

    public function run(): string
    {
        RatingInputAsset::register($this->getView());

        $value = $this->hasModel()
            ? Html::getAttributeValue($this->model, $this->attribute)
            : $this->value;

        return $this->render('rating-input', [
            'selectedScore' => (int) $value,
            'size' => $this->size,
            'maxStars' => self::MAX_STARS,
            'inputId' => $this->options['id'],
            'input' => $this->renderInputHtml('hidden'),
        ]);
    }
}
