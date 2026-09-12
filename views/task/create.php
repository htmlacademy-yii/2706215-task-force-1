<?php

declare(strict_types=1);

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var \app\requests\TaskCreateRequest $model
 * @var array<int, string> $categories
 */

$this->params['mainClass'] = 'main-content main-content--center';
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
            ],
        ]
    )->textInput() ?>

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
    ])->fileInput(['multiple' => true]) ?>

    <?= Html::submitInput('Опубликовать', ['class' => 'button button--blue']) ?>

    <?php ActiveForm::end(); ?>
</div>
