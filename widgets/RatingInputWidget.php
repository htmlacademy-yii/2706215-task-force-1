<?php

declare(strict_types=1);

namespace app\widgets;

use app\assets\RatingInputAsset;
use yii\helpers\Html;
use yii\widgets\InputWidget;

/**
 * Renders an interactive star-rating form input.
 */
final class RatingInputWidget extends InputWidget
{
    private const int MAX_STARS = 5;

    /** Visual size passed to the rating view. */
    public string $size = RatingWidget::SIZE_SMALL;

    /**
     * {@inheritdoc}
     *
     * @return string
     */
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
