<?php

declare(strict_types=1);

use app\models\Task;
use app\widgets\AppLinkPager;
use Sanweb\Taskforce\enum\MyTaskFilter;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var ActiveDataProvider $dataProvider */
/** @var MyTaskFilter $activeFilter */
/** @var list<MyTaskFilter> $filters */

$this->title = 'Мои задания';
$tasks = $dataProvider->getModels();
?>

<div class="left-menu">
    <h3 class="head-main head-task">Мои задания</h3>
    <ul class="side-menu-list">
        <?php foreach ($filters as $filter): ?>
            <li class="side-menu-item<?= $filter === $activeFilter ? ' side-menu-item--active' : '' ?>">
                <?= Html::a(
                    Html::encode($filter->label()),
                    ['my-task/index', 'filter' => $filter->value],
                    ['class' => 'link link--nav']
                ) ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<div class="left-column left-column--task">
    <h3 class="head-main head-regular"><?= Html::encode($activeFilter->heading()) ?></h3>

    <?php if ($tasks !== []): ?>
        <?php foreach ($tasks as $task): ?>
            <?php if ($task instanceof Task): ?>
                <div class="task-card">
                    <div class="header-task">
                        <?= Html::a(
                            Html::encode($task->title),
                            ['task/view', 'id' => $task->id],
                            ['class' => 'link link--block link--big']
                        ) ?>
                        <p class="price price--task">
                            <?= Yii::$app->formatter->asCurrency($task->budget) ?>
                        </p>
                    </div>
                    <p class="info-text">
                        <span class="current-time">
                            <?= Yii::$app->formatter->asRelativeTime($task->created_at) ?>
                        </span>
                    </p>
                    <p class="task-text">
                        <?= Html::encode(StringHelper::truncate($task->description, 300)) ?>
                    </p>
                    <div class="footer-task">
                        <p class="info-text town-text">
                            <?php if ($task->city === null): ?>
                                Удалённая работа
                            <?php else: ?>
                                <?= Html::encode($task->city->name) ?>
                                <?php if ($task->location !== null && $task->location !== ''): ?>
                                    <?= Html::encode(', ' . $task->location) ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </p>
                        <p class="info-text category-text">
                            <?= Html::encode($task->category->name) ?>
                        </p>
                        <?= Html::a(
                            'Смотреть задание',
                            ['task/view', 'id' => $task->id],
                            ['class' => 'button button--black']
                        ) ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if ($dataProvider->pagination !== false && $dataProvider->pagination->pageCount > 1): ?>
            <?= AppLinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
        <?php endif; ?>
    <?php else: ?>
        <p class="info-text">В выбранной категории заданий пока нет.</p>
    <?php endif; ?>
</div>
