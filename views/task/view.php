<?php

declare(strict_types=1);

use app\assets\TaskViewAsset;
use app\forms\BidCreateForm;
use app\forms\TaskCompleteForm;
use app\widgets\RatingWidget;
use Sanweb\Taskforce\enum\BidStatus;
use Sanweb\Taskforce\enum\TaskAction;
use Sanweb\Taskforce\enum\TaskStatus;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var \app\models\Task $task */
/** @var bool $isCustomer */
/** @var list<TaskAction> $availableActions */
/** @var BidCreateForm $bidForm */
/** @var TaskCompleteForm $completeForm */

TaskViewAsset::register($this);
?>

<div class="left-column">
    <div class="head-wrapper">
        <h3 class="head-main"><?= Html::encode($task->title) ?></h3>
        <p class="price price--big"><?= Yii::$app->formatter->asCurrency($task->budget) ?></p>
    </div>
    <p class="task-description"><?= Html::encode($task->description) ?></p>

    <?php if (in_array(TaskAction::Bid, $availableActions, true)): ?>
        <a
            href="#"
            class="button button--blue action-btn"
            data-action="act_response"
        >Откликнуться на задание</a>
    <?php endif; ?>

    <?php if (in_array(TaskAction::Refuse, $availableActions, true)): ?>
        <a
            href="#"
            class="button button--orange action-btn"
            data-action="refusal"
        >Отказаться от задания</a>
    <?php endif; ?>

    <?php if (in_array(TaskAction::Complete, $availableActions, true)): ?>
        <a
            href="#"
            class="button button--pink action-btn"
            data-action="completion"
        >Завершить задание</a>
    <?php endif; ?>

    <?php if (in_array(TaskAction::Cancel, $availableActions, true)): ?>
        <a
            href="#"
            class="button button--orange action-btn"
            data-action="cancellation"
        >Отменить задание</a>
    <?php endif; ?>

    <div class="task-map">
        <img class="map" src="/img/map.png" width="725" height="346" alt="Новый арбат, 23, к. 1">
        <p class="map-address town">Москва</p>
        <p class="map-address">Новый арбат, 23, к. 1</p>
    </div>

    <?php if (!empty($task->bids)): ?>
        <h4 class="head-regular">Отклики на задание</h4>

        <?php foreach ($task->bids as $bid): ?>
            <div class="response-card">
                <img
                    class="customer-photo"
                    src="<?= Html::encode($bid->user->avatar ?? '/img/avatars/default.png') ?>"
                    width="146"
                    height="156"
                    alt="Фото исполнителя"
                >
                <div class="feedback-wrapper">
                    <a
                        href="<?= Url::to(['user/view', 'id' => $bid->user_id]) ?>"
                        class="link link--block link--big"
                    ><?= Html::encode($bid->user->name) ?></a>
                    <div class="response-wrapper">
                        <?= RatingWidget::widget([
                            'value' => $bid->user->executorStats->avg_score ?? 0,
                            'size' => RatingWidget::SIZE_SMALL,
                        ]) ?>
                        <p class="reviews">
                            <?= Yii::t(
                                'app',
                                '{n, plural, one{# отзыв} few{# отзыва} many{# отзывов} other{# отзывов}}',
                                ['n' => count($bid->user->receivedReviews)],
                            ) ?>
                        </p>
                    </div>
                    <p class="response-message"><?= Html::encode($bid->comment) ?></p>
                </div>
                <div class="feedback-wrapper">
                    <p class="info-text">
                        <span class="current-time">
                            <?= Yii::$app->formatter->asRelativeTime($bid->created_at) ?>
                        </span>
                    </p>
                    <p class="price price--small"><?= Yii::$app->formatter->asCurrency($bid->price) ?></p>
                </div>

                <?php
                $showBidActions = $isCustomer
                    && $task->status === TaskStatus::New->value
                    && $bid->status === BidStatus::New->value;
                ?>

                <?php if ($showBidActions): ?>
                    <div class="button-popup">
                        <?= Html::a('Принять', ['task/accept-bid', 'id' => $bid->id], [
                            'class' => 'button button--blue button--small',
                            'data-method' => 'post',
                        ]) ?>
                        <?= Html::a('Отказать', ['task/reject-bid', 'id' => $bid->id], [
                            'class' => 'button button--orange button--small',
                            'data-method' => 'post',
                        ]) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>
</div>

<div class="right-column">
    <div class="right-card black info-card">
        <h4 class="head-card">Информация о задании</h4>
        <dl class="black-list">
            <dt>Категория</dt>
            <dd><?= Html::encode($task->category->name) ?></dd>
            <dt>Дата публикации</dt>
            <dd><?= Yii::$app->formatter->asRelativeTime($task->created_at) ?></dd>
            <dt>Срок выполнения</dt>
            <dd><?= Yii::$app->formatter->asDatetime($task->expire_date, 'd MMMM, HH:mm') ?></dd>
            <dt>Статус</dt>
            <dd><?= Html::encode($task->statusLabel) ?></dd>
        </dl>
    </div>
    <?php if (!empty($task->attachments)): ?>
        <div class="right-card white file-card">
            <h4 class="head-card">Файлы задания</h4>
            <ul class="enumeration-list">
                <?php foreach ($task->attachments as $attachment): ?>
                    <li class="enumeration-item">
                        <?= Html::a(
                            $attachment->original_name,
                            ['/task/download', 'id' => $attachment->id],
                            ['class' => 'link link--block link--clip']
                        ) ?>
                        <?php if ($attachment->size_bytes !== null): ?>
                            <p class="file-size">
                                <?= Yii::$app->formatter->asShortSize($attachment->size_bytes) ?>
                            </p>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>

<?= $this->render('_modals', [
    'task' => $task,
    'availableActions' => $availableActions,
    'bidForm' => $bidForm,
    'completeForm' => $completeForm,
]) ?>
