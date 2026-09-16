<?php

declare(strict_types=1);

/** @var int $selectedScore */
/** @var string $size */
/** @var int $maxStars */
/** @var string $inputId */
/** @var string $input */
?>

<div
    class="stars-rating <?= $size ?> active-stars"
    role="radiogroup"
    aria-label="Оценка работы"
    data-rating-input="<?= $inputId ?>"
>
    <?php foreach (range(1, $maxStars) as $score): ?>
        <span
            <?= $score <= $selectedScore ? 'class="fill-star"' : '' ?>
            role="radio"
            tabindex="0"
            data-score="<?= $score ?>"
            aria-label="<?= $score ?> из <?= $maxStars ?>"
            aria-checked="<?= $score === $selectedScore ? 'true' : 'false' ?>"
        >&nbsp;</span>
    <?php endforeach; ?>
</div>
<?= $input ?>
