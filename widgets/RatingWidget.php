<?php

declare(strict_types=1);

namespace app\widgets;

use yii\base\Widget;

/**
 * Renders a read-only star rating.
 */
final class RatingWidget extends Widget
{
    public const string SIZE_SMALL = 'small';
    public const string SIZE_BIG = 'big';

    private const int MAX_STARS = 5;

    /** Numeric rating value. */
    public float $value;

    /** Visual size passed to the rating view. */
    public string $size = self::SIZE_SMALL;

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function run(): string
    {
        return $this->render('rating', [
            'filledStars' => (int) floor($this->value),
            'size' => $this->size,
            'maxStars' => self::MAX_STARS,
        ]);
    }
}
