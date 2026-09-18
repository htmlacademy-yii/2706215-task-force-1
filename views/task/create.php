<?php

declare(strict_types=1);

use app\assets\TaskCreateAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * @var \app\forms\TaskCreateForm $model
 * @var array<int, string> $categories
 */

$this->params['mainClass'] = 'main-content main-content--center';
TaskCreateAsset::register($this);
?>

<div class="add-task-form regular-form">
    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => '',
            'enctype' => 'multipart/form-data',
        ],
        'fieldConfig' => [
            'options' => ['class' => 'form-group'],
            'labelOptions' => ['class' => 'control-label'],
            'errorOptions' => ['class' => 'help-block'],
        ],
    ]); ?>
    <h3 class="head-main head-main">Публикация нового задания</h3>

    <?= $form->field($model, 'title')->textInput() ?>

    <?= $form->field($model, 'description')->textarea() ?>

    <?= $form->field($model, 'categoryId')->dropDownList($categories, ['prompt' => 'Выберите категорию']) ?>

    <?= $form->field(
        $model,
        'location',
        [
            'inputOptions' => [
                'class' => 'location-icon',
                'autocomplete' => 'off',
                'data-location-autocomplete' => true,
                'data-suggestions-url' => Url::to(['geo/suggestions']),
                'data-latitude-input' => Html::getInputId($model, 'latitude'),
                'data-longitude-input' => Html::getInputId($model, 'longitude'),
            ],
        ]
    )->textInput() ?>

    <?= Html::activeHiddenInput($model, 'latitude') ?>
    <?= Html::activeHiddenInput($model, 'longitude') ?>

    <div class="half-wrapper">
        <?= $form->field(
            $model,
            'budget',
            [
                'inputOptions' => [
                    'class' => 'budget-icon',
                ],
            ]
        )->textInput() ?>

        <?= $form->field(
            $model,
            'expireDate',
            [
                'inputOptions' => [
                    'type' => 'date',
                ],
            ]
        )->textInput() ?>
    </div>

    <?= $form->field($model, 'files', [
        'template' => '{label}<div class="new-file">{input}</div>{error}',
        'labelOptions' => ['class' => 'form-label'],
    ])->fileInput([
        'multiple' => true,
        'value' => '',
    ]) ?>

    <?= Html::submitInput('Опубликовать', ['class' => 'button button--blue']) ?>

    <?php ActiveForm::end(); ?>
</div>
