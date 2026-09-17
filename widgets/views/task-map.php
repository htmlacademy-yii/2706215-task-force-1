<?php

declare(strict_types=1);

use yii\helpers\Html;

/** @var string|null $address */
/** @var string $mapId */
/** @var bool $showInteractiveMap */
?>

<div class="task-map">
    <?php if ($showInteractiveMap): ?>
        <div
            id="<?= Html::encode($mapId) ?>"
            class="map"
            role="img"
            aria-label="Карта: <?= Html::encode($address ?? 'место выполнения задания') ?>"
        ></div>
    <?php endif; ?>

    <?php if ($address !== null): ?>
        <p class="map-address"><?= Html::encode($address) ?></p>
    <?php endif; ?>
</div>
