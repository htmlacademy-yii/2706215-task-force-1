<?php

declare(strict_types=1);

require_once __DIR__ . '/Task.php';

$task = new Task(TASK::STATUS_NEW, 1);

echo Task::getStatusLabel($task->getStatus()) . PHP_EOL;

$task->assign(1);

echo Task::getStatusLabel($task->getStatus()) . PHP_EOL;

$task->complete();

echo Task::getStatusLabel($task->getStatus()) . PHP_EOL;
