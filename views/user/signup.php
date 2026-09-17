<?php

declare(strict_types=1);

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var \app\forms\UserSignupForm $model
 * @var array<int, string> $cities
 */

$this->params['mainClass'] = 'container--registration';
?>

<div class="center-block">
    <div class="registration-form regular-form">
        <?php $form = ActiveForm::begin([
            'options' => ['class' => ''],
            'fieldConfig' => [
                'options' => ['class' => 'form-group'],
                'labelOptions' => ['class' => 'control-label'],
                'errorOptions' => ['class' => 'help-block'],
            ],
        ]); ?>

        <h3 class="head-main head-task">Регистрация нового пользователя</h3>

        <?= $form->field($model, 'name')->textInput() ?>

        <div class="half-wrapper">
            <?= $form->field($model, 'email')->input('email') ?>
            <?= $form->field($model, 'cityId')->dropDownList($cities, ['prompt' => 'Выберите город']) ?>
        </div>

        <div class="half-wrapper">
            <?= $form->field($model, 'password')->passwordInput() ?>
        </div>

        <div class="half-wrapper">
            <?= $form->field($model, 'passwordConfirm')->passwordInput() ?>
        </div>

        <div class="form-group">
            <label class="control-label checkbox-label">
                <?= Html::activeCheckbox($model, 'isExecutor', ['label' => false]) ?>
                <?= Html::encode($model->getAttributeLabel('isExecutor')) ?>
            </label>
            <?= Html::error($model, 'isExecutor', ['class' => 'help-block']) ?>
        </div>

        <?= Html::submitInput('Создать аккаунт', ['class' => 'button button--blue']) ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>
