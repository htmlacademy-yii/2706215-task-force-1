<?php

declare(strict_types=1);

use app\forms\BidCreateForm;
use app\forms\TaskCompleteForm;
use Sanweb\Taskforce\enum\TaskAction;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var \app\models\Task $task */
/** @var list<TaskAction> $availableActions */
/** @var BidCreateForm $bidForm */
/** @var TaskCompleteForm $completeForm */
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
            <?php $form = ActiveForm::begin([
                'action' => ['task/refuse', 'id' => $task->id],
                'method' => 'post',
            ]); ?>
                <?= Html::submitButton('Отказаться', ['class' => 'button button--pop-up button--orange']) ?>
            <?php ActiveForm::end(); ?>
            <div class="button-container">
                <button class="button--close" type="button">Закрыть окно</button>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if (in_array(TaskAction::Complete, $availableActions, true)): ?>
    <section class="pop-up pop-up--completion pop-up--close">
        <div class="pop-up--wrapper">
            <h4>Завершение задания</h4>
            <p class="pop-up-text">
                Вы собираетесь отметить это задание как выполненное.
                Пожалуйста, оставьте отзыв об исполнителе и оцените его работу.
            </p>
            <div class="completion-form pop-up--form regular-form">
                <?php $form = ActiveForm::begin([
                    'action' => ['task/complete', 'id' => $task->id],
                    'method' => 'post',
                ]); ?>
                    <?= $form->field($completeForm, 'comment')->textarea() ?>
                    <?php
                    $selectedScore = (int) $completeForm->score;
                    $stars = '';

                    for ($score = 1; $score <= 5; $score++) {
                        $stars .= Html::tag('span', '&nbsp;', [
                            'class' => $score <= $selectedScore ? 'fill-star' : null,
                            'role' => 'radio',
                            'tabindex' => '0',
                            'data-score' => $score,
                            'aria-label' => $score . ' из 5',
                            'aria-checked' => $score === $selectedScore ? 'true' : 'false',
                        ]);
                    }

                    $rating = Html::tag('div', $stars, [
                        'class' => 'stars-rating big active-stars',
                        'role' => 'radiogroup',
                        'aria-label' => 'Оценка работы',
                        'data-rating-input' => Html::getInputId($completeForm, 'score'),
                    ]);
                    ?>
                    <?= $form->field($completeForm, 'score', [
                        'template' => "{label}\n{$rating}\n{input}\n{error}",
                        'labelOptions' => ['class' => 'completion-head control-label'],
                    ])->hiddenInput() ?>
                    <?= Html::submitButton('Завершить', ['class' => 'button button--pop-up button--blue']) ?>
                <?php ActiveForm::end(); ?>
            </div>
            <div class="button-container">
                <button class="button--close" type="button">Закрыть окно</button>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if (in_array(TaskAction::Bid, $availableActions, true)): ?>
    <section class="pop-up pop-up--act_response pop-up--close">
        <div class="pop-up--wrapper">
            <h4>Добавление отклика к заданию</h4>
            <p class="pop-up-text">
                Вы собираетесь оставить свой отклик к этому заданию.
                Пожалуйста, укажите стоимость работы и добавьте комментарий, если необходимо.
            </p>
            <div class="addition-form pop-up--form regular-form">
                <?php $form = ActiveForm::begin([
                    'action' => ['task/create-bid', 'id' => $task->id],
                    'method' => 'post',
                ]); ?>
                    <?= $form->field($bidForm, 'comment')->textarea() ?>
                    <?= $form->field($bidForm, 'price')->textInput() ?>
                    <?= Html::submitButton('Отправить', ['class' => 'button button--pop-up button--blue']) ?>
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
            <?php $form = ActiveForm::begin([
                'action' => ['task/cancel', 'id' => $task->id],
                'method' => 'post',
            ]); ?>
                <?= Html::submitButton('Отменить', ['class' => 'button button--pop-up button--orange']) ?>
            <?php ActiveForm::end(); ?>
            <div class="button-container">
                <button class="button--close" type="button">Закрыть окно</button>
            </div>
        </div>
    </section>
<?php endif; ?>

<div class="overlay"></div>
