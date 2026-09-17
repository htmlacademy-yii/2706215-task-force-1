<?php

declare(strict_types=1);

/** @var app\forms\UserLoginForm $loginForm */
/** @var bool $isOpen */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>
<section
    class="modal enter-form form-modal"
    id="enter-form"
    <?= $isOpen ? 'style="display: block"' : '' ?>
>
    <h2>Вход на сайт</h2>

    <?php $form = ActiveForm::begin([
        'id' => 'login-form',
        'action' => ['site/login'],
        'fieldConfig' => [
            'options' => ['class' => 'form-group'],
            'labelOptions' => ['class' => 'form-modal-description'],
            'errorOptions' => ['class' => 'help-block'],
        ],
    ]); ?>

    <?= $form->field($loginForm, 'email')->input('email', [
        'class' => 'enter-form-email input input-middle',
    ]) ?>

    <?= $form->field($loginForm, 'password')->passwordInput([
        'class' => 'enter-form-email input input-middle',
    ]) ?>

    <?= Html::submitButton('Войти', ['class' => 'button']) ?>

    <?php ActiveForm::end(); ?>

    <button class="form-modal-close" type="button">Закрыть</button>
</section>

<div
    class="overlay"
    <?= $isOpen ? 'style="display: block"' : '' ?>
></div>
