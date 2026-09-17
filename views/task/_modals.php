<?php

declare(strict_types=1);

use app\forms\BidCreateForm;
use app\forms\TaskCompleteForm;
use app\widgets\RatingInputWidget;
use app\widgets\RatingWidget;
use Sanweb\Taskforce\enum\TaskAction;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var \app\models\Task $task */
/** @var list<TaskAction> $availableActions */
/** @var BidCreateForm $bidForm */
/** @var TaskCompleteForm $completeForm */
/** @var string|null $activeModal */
?>

<?php if (in_array(TaskAction::Refuse, $availableActions, true)): ?>
    <section class="pop-up pop-up--refusal pop-up--close">
        <div class="pop-up--wrapper">
            <h4>Отказ от задания</h4>
            <p class="pop-up-text">
                <b>Внимание!</b><br>
                Вы собираетесь отказаться от выполнения этого задания.<br>
                Это действие плохо скажется на вашем рейтинге и увеличит счетчик проваленных заданий.
            </p>
            <?= Html::a('Отказаться', ['task/refuse', 'id' => $task->id], [
                'class' => 'button button--pop-up button--orange',
                'data-method' => 'post',
            ]) ?>
            <div class="button-container">
                <button class="button--close" type="button">Закрыть окно</button>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if (in_array(TaskAction::Complete, $availableActions, true)): ?>
    <section class="pop-up pop-up--completion <?= $activeModal === 'completion' ? 'pop-up--open' : 'pop-up--close' ?>">
        <div class="pop-up--wrapper">
            <h4>Завершение задания</h4>
            <p class="pop-up-text">
                Вы собираетесь отметить это задание как выполненное.
                Пожалуйста, оставьте отзыв об исполнителе и отметьте отдельно, если возникли проблемы.
            </p>
            <div class="completion-form pop-up--form regular-form">
                <?php $completionActiveForm = ActiveForm::begin([
                    'id' => 'completion-form',
                    'action' => ['task/complete', 'id' => $task->id],
                    'method' => 'post',
                    'fieldConfig' => [
                        'options' => ['class' => 'form-group'],
                        'labelOptions' => ['class' => 'control-label'],
                        'errorOptions' => ['class' => 'help-block'],
                    ],
                ]); ?>

                <?= $completionActiveForm
                    ->field($completeForm, 'comment')
                    ->textarea(['id' => 'completion-comment']) ?>

                <p class="completion-head control-label">Оценка работы</p>
                <?= $completionActiveForm->field($completeForm, 'score', [
                    'template' => "{input}\n{error}",
                ])->widget(RatingInputWidget::class, [
                    'size' => RatingWidget::SIZE_BIG,
                ]) ?>

                <?= Html::submitInput('Завершить', ['class' => 'button button--pop-up button--blue']) ?>

                <?php ActiveForm::end(); ?>
            </div>
            <div class="button-container">
                <button class="button--close" type="button">Закрыть окно</button>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if (in_array(TaskAction::Bid, $availableActions, true)): ?>
    <section class="pop-up pop-up--act_response <?= $activeModal === 'act_response' ? 'pop-up--open' : 'pop-up--close' ?>">
        <div class="pop-up--wrapper">
            <h4>Добавление отклика к заданию</h4>
            <p class="pop-up-text">
                Вы собираетесь оставить свой отклик к этому заданию.
                Пожалуйста, укажите стоимость работы и добавьте комментарий, если необходимо.
            </p>
            <div class="addition-form pop-up--form regular-form">
                <?php $bidActiveForm = ActiveForm::begin([
                    'id' => 'bid-form',
                    'action' => ['task/create-bid', 'id' => $task->id],
                    'method' => 'post',
                    'fieldConfig' => [
                        'options' => ['class' => 'form-group'],
                        'labelOptions' => ['class' => 'control-label'],
                        'errorOptions' => ['class' => 'help-block'],
                    ],
                ]); ?>

                <?= $bidActiveForm
                    ->field($bidForm, 'comment')
                    ->textarea(['id' => 'addition-comment']) ?>

                <?= $bidActiveForm
                    ->field($bidForm, 'price')
                    ->textInput(['id' => 'addition-price']) ?>

                <?= Html::submitInput('Откликнуться', ['class' => 'button button--pop-up button--blue']) ?>

                <?php ActiveForm::end(); ?>
            </div>
            <div class="button-container">
                <button class="button--close" type="button">Закрыть окно</button>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if (in_array(TaskAction::Cancel, $availableActions, true)): ?>
    <section class="pop-up pop-up--cancellation pop-up--close">
        <div class="pop-up--wrapper">
            <h4>Отмена задания</h4>
            <p class="pop-up-text">Вы действительно хотите отменить это задание?</p>
            <?= Html::a('Отменить', ['task/cancel', 'id' => $task->id], [
                'class' => 'button button--pop-up button--orange',
                'data-method' => 'post',
            ]) ?>
            <div class="button-container">
                <button class="button--close" type="button">Закрыть окно</button>
            </div>
        </div>
    </section>
<?php endif; ?>

<div class="overlay<?= $activeModal !== null ? ' db' : '' ?>"></div>
