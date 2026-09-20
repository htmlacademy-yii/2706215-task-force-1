<?php

declare(strict_types=1);

use app\components\AvatarUrlResolver;
use app\widgets\RatingWidget;
use yii\helpers\Html;

/** @var app\models\User $user */
/** @var bool $canViewContacts */

$avatarUrlResolver = new AvatarUrlResolver();
?>

<div class="left-column">
    <h3 class="head-main">Астахов Павел</h3>
    <div class="user-card">
        <div class="photo-rate">
            <?= Html::img($avatarUrlResolver->resolve($user->avatar), [
                'class' => 'card-photo',
                'width' => 191,
                'height' => 190,
                'alt' => 'Фото пользователя',
            ]) ?>
            <div class="card-rate">
                <?= RatingWidget::widget([
                    'value' => $user->executorStats->avg_score ?? 0,
                    'size' => RatingWidget::SIZE_BIG,
                ]) ?>
                <span class="current-rate"><?= $user->executorStats->avg_score ?? 0  ?></span>
            </div>
        </div>
        <p class="user-description"><?= Html::encode($user->executorProfile->about ?? '') ?></p>
    </div>

    <div class="specialization-bio">
        <div class="specialization">
            <p class="head-info">Специализации</p>
            <?php if ($user->categories): ?>
                <ul class="special-list">
                    <?php foreach ($user->categories as $category): ?>
                        <li class="special-item">
                            <a href="#" class="link link--regular">
                                <?= Html::encode($category->name) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="special-list">Специализации не указаны</p>
            <?php endif; ?>
        </div>
        <div class="bio">
            <p class="head-info">Био</p>
            <p class="bio-info">
                <span class="country-info">Россия</span>,
                <span class="town-info"><?= Html::encode($user->city->name) ?></span>,
                <span class="age-info"><?= $user->age ?></span> лет
            </p>
        </div>
    </div>

    <h4 class="head-regular">Отзывы заказчиков</h4>

    <?php foreach ($user->receivedReviews as $review): ?>
        <div class="response-card">
            <img
                class="customer-photo"
                src="<?= Html::encode($avatarUrlResolver->resolve($review->customer?->avatar)) ?>"
                width="120"
                height="127"
                alt="<?= Html::encode('Фото ' . $review->customer?->name) ?>">

            <div class="feedback-wrapper">
                <p class="feedback">«<?= Html::encode($review->comment) ?>»</p>

                <p class="task">
                    Задание «
                    <?= Html::a(
                        Html::encode($review->task->title),
                        ['task/view', 'id' => $review->task_id],
                        ['class' => 'link link--small']
                    ) ?>
                    » выполнено
                </p>
            </div>

            <div class="feedback-wrapper">
                <?= RatingWidget::widget([
                    'value' => $review->score ?? 0,
                    'size' => RatingWidget::SIZE_SMALL,
                ]) ?>

                <p class="info-text">
                    <?= Yii::$app->formatter->asRelativeTime($review->created_at) ?>
                </p>
            </div>
        </div>
    <?php endforeach; ?>

</div>
<div class="right-column">
    <div class="right-card black">
        <h4 class="head-card">Статистика исполнителя</h4>
        <dl class="black-list">
            <dt>Всего заказов</dt>
            <dd><?= $user->executorStats?->completed_tasks ?? 0 ?> выполнено, <?= $user->executorStats?->failed_tasks ?? 0 ?> провалено</dd>
            <dt>Место в рейтинге</dt>
            <dd><?= $user->executorStats?->rating_position ?> место</dd>
            <dt>Дата регистрации</dt>
            <dd><?= Yii::$app->formatter->asDatetime($user->created_at, 'd MMMM, HH:mm') ?></dd>
            <dt>Статус</dt>
            <dd><?= Html::encode($user->executorProfile->statusLabel) ?></dd>
        </dl>
    </div>
    <div class="right-card white">
        <h4 class="head-card">Контакты</h4>

        <?php if ($canViewContacts): ?>
            <ul class="enumeration-list">
                <?php if ($user->executorProfile?->phone): ?>
                    <li class="enumeration-item">
                        <a
                            href="tel:<?= Html::encode($user->executorProfile->phone) ?>"
                            class="link link--block link--phone">
                            <?= Html::encode(Yii::$app->formatter->asPhone($user->executorProfile->phone)) ?>
                        </a>
                    </li>
                <?php endif; ?>

                <li class="enumeration-item">
                    <a
                        href="mailto:<?= Html::encode($user->email) ?>"
                        class="link link--block link--email">
                        <?= Html::encode($user->email) ?>
                    </a>
                </li>

                <?php if ($user->executorProfile?->telegram): ?>
                    <li class="enumeration-item">
                        <a
                            href="https://t.me/<?= Html::encode(ltrim($user->executorProfile->telegram, '@')) ?>"
                            class="link link--block link--tg">
                            <?= Html::encode($user->executorProfile->telegram) ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        <?php else: ?>
            <p>Исполнитель скрыл контактные данные.</p>
        <?php endif; ?>
    </div>
</div>
