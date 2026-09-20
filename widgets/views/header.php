<?php

declare(strict_types=1);

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var bool $isGuest */
/** @var bool $canCreateTask */
/** @var string $route */
/** @var string $userName */
?>

<header class="page-header">
    <nav class="main-nav">
        <a href="<?= Url::to(['site/index']) ?>" class="header-logo">
            <img class="logo-image" src="/img/logotype.png" width=227 height=60 alt="taskforce">
        </a>
        <div class="nav-wrapper">
            <ul class="nav-list">
                <li class="list-item<?= $route === 'task/index' ? ' list-item--active' : '' ?>">
                    <a href="<?= Url::to(['/task/index']) ?>" class="link link--nav">Новое</a>
                </li>
                <?php if (!$isGuest): ?>
                    <li class="list-item<?= $route === 'my-task/index' ? ' list-item--active' : '' ?>">
                        <a href="<?= Url::to(['/my-task/index']) ?>" class="link link--nav">Мои задания</a>
                    </li>
                <?php endif; ?>
                <?php if ($canCreateTask): ?>
                    <li class="list-item">
                        <a href="<?= Url::to(['/task/create']) ?>" class="link link--nav">Создать задание</a>
                    </li>
                <?php endif; ?>
                <li class="list-item">
                    <a href="#" class="link link--nav">Настройки</a>
                </li>
            </ul>
        </div>
    </nav>
    <?php if (!$isGuest): ?>
        <div class="user-block">
            <a href="#">
                <img class="user-photo" src="/img/man-glasses.png" width="55" height="55" alt="Аватар">
            </a>
            <div class="user-menu">
                <p class="user-name"><?= Html::encode($userName) ?></p>
                <div class="popup-head">
                    <ul class="popup-menu">
                        <li class="menu-item">
                            <a href="#" class="link">Настройки</a>
                        </li>
                        <li class="menu-item">
                            <a href="#" class="link">Связаться с нами</a>
                        </li>
                        <li class="menu-item">
                            <?= Html::a('Выход из системы', ['/site/logout'], [
                                'class' => 'link',
                                'data-method' => 'post',
                            ]) ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>
</header>
