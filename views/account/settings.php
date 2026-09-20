<?php

declare(strict_types=1);

use app\forms\ProfileSettingsForm;
use app\forms\SecuritySettingsForm;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var ProfileSettingsForm $profileForm */
/** @var SecuritySettingsForm $securityForm */
/** @var array<int, string> $categories */
/** @var string $avatarUrl */
/** @var string $activeSection */

$this->title = 'Настройки аккаунта';
$this->params['mainClass'] = 'main-content main-content--left';
$this->registerJsFile('@web/js/settings-tabs.js', ['position' => View::POS_END]);
?>

<div class="left-menu left-menu--edit">
    <h3 class="head-main head-task">Настройки</h3>
    <ul class="side-menu-list" role="tablist" data-settings-tabs>
        <li class="side-menu-item<?= $activeSection === 'profile'
            ? ' side-menu-item--active'
            : '' ?>">
            <a
                id="profile-tab"
                href="#profile"
                class="link link--nav"
                role="tab"
                aria-controls="profile"
                aria-selected="<?= $activeSection === 'profile' ? 'true' : 'false' ?>"
                tabindex="<?= $activeSection === 'profile' ? '0' : '-1' ?>">
                Мой профиль
            </a>
        </li>
        <li class="side-menu-item<?= $activeSection === 'security'
            ? ' side-menu-item--active'
            : '' ?>">
            <a
                id="security-tab"
                href="#security"
                class="link link--nav"
                role="tab"
                aria-controls="security"
                aria-selected="<?= $activeSection === 'security' ? 'true' : 'false' ?>"
                tabindex="<?= $activeSection === 'security' ? '0' : '-1' ?>">
                Безопасность
            </a>
        </li>
    </ul>
</div>

<div class="my-profile-form">
    <section
        id="profile"
        role="tabpanel"
        aria-labelledby="profile-tab"
        <?= $activeSection === 'profile' ? '' : 'hidden' ?>>
        <?php $form = ActiveForm::begin([
            'action' => ['account/update-profile'],
            'method' => 'post',
            'options' => ['enctype' => 'multipart/form-data'],
        ]); ?>

        <h3 class="head-main head-regular">Мой профиль</h3>
        <div class="photo-editing">
            <div>
                <p class="form-label">Аватар</p>
                <?= Html::img($avatarUrl, [
                    'class' => 'avatar-preview',
                    'width' => 83,
                    'height' => 83,
                    'alt' => 'Аватар',
                ]) ?>
            </div>
            <?= $form->field($profileForm, 'avatarFile', [
                'template' => '{input}{error}',
                'options' => ['class' => 'photo-editing-control'],
            ])->fileInput([
                'hidden' => true,
                'id' => 'avatar-file',
                'hiddenOptions' => ['disabled' => true],
            ]) ?>
            <label for="avatar-file" class="button button--black">Сменить аватар</label>
        </div>

        <?= $form->field($profileForm, 'name')->textInput() ?>

        <div class="half-wrapper">
            <?= $form->field($profileForm, 'email')->input('email') ?>
            <?= $form->field($profileForm, 'birthday')->textInput([
                'placeholder' => 'дд.мм.гггг',
            ]) ?>
        </div>

        <?php if ($profileForm->isExecutor()): ?>
            <div class="half-wrapper">
                <?= $form->field($profileForm, 'phone')->input('tel', [
                    'placeholder' => '7XXXXXXXXXX',
                    'maxlength' => 11,
                ]) ?>
                <?= $form->field($profileForm, 'telegram')->textInput() ?>
            </div>
            <?= $form->field($profileForm, 'about')->textarea() ?>
            <?= $form->field($profileForm, 'categoryIds', [
                'template' => '<p class="form-label">{label}</p>{input}{error}',
            ])->checkboxList($categories, [
                'class' => 'checkbox-profile',
                'item' => function ($index, $label, $name, $checked, $value): string {
                    $id = 'specialization-' . $value;

                    return Html::label(
                        Html::checkbox(
                            $name,
                            $checked,
                            ['value' => $value, 'id' => $id]
                        ) . ' ' . Html::encode($label),
                        $id,
                        ['class' => 'control-label']
                    );
                },
            ]) ?>
        <?php endif; ?>

        <?= Html::submitInput('Сохранить', ['class' => 'button button--blue']) ?>
        <?php ActiveForm::end(); ?>
    </section>

    <section
        id="security"
        role="tabpanel"
        aria-labelledby="security-tab"
        <?= $activeSection === 'security' ? '' : 'hidden' ?>>
        <?php $form = ActiveForm::begin([
            'action' => ['account/update-security'],
            'method' => 'post',
        ]); ?>

        <h3 class="head-main head-regular">Безопасность</h3>

        <?php if ($securityForm->canChangePassword()): ?>
            <?= $form->field($securityForm, 'oldPassword')->passwordInput() ?>
            <?= $form->field($securityForm, 'newPassword')->passwordInput() ?>
            <?= $form->field($securityForm, 'newPasswordRepeat')->passwordInput() ?>
        <?php else: ?>
            <p class="info-text">
                Для аккаунта GitHub изменение пароля недоступно.
            </p>
        <?php endif; ?>

        <?php if ($securityForm->isExecutor()): ?>
            <?= $form->field($securityForm, 'hideMyContacts')->checkbox([
                'label' => 'Показывать мои контакты только заказчику',
            ]) ?>
        <?php endif; ?>

        <?php if ($securityForm->canChangePassword() || $securityForm->isExecutor()): ?>
            <?= Html::submitInput('Сохранить', ['class' => 'button button--blue']) ?>
        <?php endif; ?>
        <?php ActiveForm::end(); ?>
    </section>
</div>
