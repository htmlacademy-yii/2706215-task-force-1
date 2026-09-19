<?php

declare(strict_types=1);

/** @var app\forms\UserLoginForm $loginForm */
/** @var bool $isOpen */
/** @var bool $githubAuthEnabled */

use yii\authclient\widgets\AuthChoice;
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

    <?= Html::submitButton('Войти', ['class' => 'button button__full-width']) ?>

    <?php ActiveForm::end(); ?>

    <?php if ($githubAuthEnabled) : ?>
        <div class="login-divider" role="separator">
            <span>или</span>
        </div>

        <?php $authChoice = AuthChoice::begin([
            'baseAuthUrl' => ['site/auth'],
            'popupMode' => true,
        ]); ?>

        <?= $authChoice->clientLink(
            Yii::$app->authClientCollection->getClient('github'),
            Html::tag('span', '', [
                'class' => 'auth-icon github',
                'aria-hidden' => 'true',
            ]) . Html::tag('span', 'Войти через GitHub'),
            [
                'class' => 'button button__full-width button__secondary button__icon',
                'aria-label' => 'Войти через GitHub',
            ],
        ) ?>

        <?php AuthChoice::end(); ?>
    <?php endif; ?>

    <button class="form-modal-close" type="button">Закрыть</button>
</section>

<div
    class="overlay"
    <?= $isOpen ? 'style="display: block"' : '' ?>
></div>
