<?php

declare(strict_types=1);

use Sanweb\Taskforce\domain\task\ActorContext;
use Sanweb\Taskforce\domain\task\TaskContext;
use Sanweb\Taskforce\domain\task\TaskWorkflow;
use Sanweb\Taskforce\enum\TaskAction;
use Sanweb\Taskforce\enum\TaskStatus;
use Sanweb\Taskforce\exception\TaskActionException;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

/**
 * Asserts that a callback throws a task action exception.
 *
 * @param callable(): void $callback
 * @param string $message
 *
 * @return void
 */
function assertTaskActionException(callable $callback, string $message): void
{
    $exceptionThrown = false;

    try {
        $callback();
    } catch (TaskActionException $exception) {
        $exceptionThrown = true;
    }

    assert($exceptionThrown === true, $message);
}

$taskService = new TaskWorkflow();

$customerId = 1;
$executorId = 2;
$customerUser = new ActorContext($customerId, false);
$executorUser = new ActorContext($executorId, true);

assert(
    $taskService->getNextStatus(TaskAction::Cancel) === TaskStatus::Canceled,
    'Cancel task action next status',
);
assert(
    $taskService->getNextStatus(TaskAction::Assign) === TaskStatus::InProgress,
    'Assign executor and start task action next status',
);
assert(
    $taskService->getNextStatus(TaskAction::Complete) === TaskStatus::Completed,
    'Complete task action next status',
);
assert(
    $taskService->getNextStatus(TaskAction::Refuse) === TaskStatus::Failed,
    'Refuse task action next status',
);

// Test $task->act() on positive scenarios
$task = new TaskContext(TaskStatus::New, $customerId, null);

assert(
    $taskService->performAction(
        $task,
        TaskAction::Cancel,
        $customerUser,
    )->getStatus() === TaskStatus::Canceled,
    'Cancel task with status new by customer',
);

assert(
    $taskService->performAction(
        $task,
        TaskAction::Bid,
        $executorUser,
    )->getStatus() === TaskStatus::New,
    'Bid to task with status new by executor',
);

assert(
    $taskService->assignExecutor(
        $task,
        $customerUser,
        $executorId,
    )->getStatus() === TaskStatus::InProgress,
    'Assign executor and start task by customer',
);

$task = new TaskContext(TaskStatus::InProgress, $customerId, $executorId);
assert(
    $taskService->performAction(
        $task,
        TaskAction::Complete,
        $customerUser,
    )->getStatus() === TaskStatus::Completed,
    'Complete task with status in_progress by customer',
);

$task = new TaskContext(TaskStatus::New, $customerId, null);

assert(
    $taskService->assignExecutor(
        $task,
        $customerUser,
        $executorId,
    )->getStatus() === TaskStatus::InProgress,
    'Assign executor and start task by customer',
);

$task = new TaskContext(TaskStatus::InProgress, $customerId, $executorId);
assert(
    $taskService->performAction(
        $task,
        TaskAction::Refuse,
        $executorUser,
    )->getStatus() === TaskStatus::Failed,
    'Refuse assigned task by executor',
);

$task = new TaskContext(TaskStatus::Failed, $customerId, $executorId);
// Test $task->act() on negative scenarios
assertTaskActionException(
    fn() => $taskService->performAction(
        $task,
        TaskAction::Refuse,
        $executorUser,
    ),
    'Try to refuse already refused task by executor',
);

assertTaskActionException(
    fn() => $taskService->performAction(
        $task,
        TaskAction::Cancel,
        $customerUser,
    ),
    'Try to cancel already refused task by customer',
);

$task = new TaskContext(TaskStatus::New, $customerId, null);

assertTaskActionException(
    fn() => $taskService->performAction(
        $task,
        TaskAction::Complete,
        $customerUser,
    ),
    'Try to complete task with status new by customer',
);

assertTaskActionException(
    fn() => $taskService->performAction(
        $task,
        TaskAction::Cancel,
        $executorUser,
    ),
    'Try to cancel task with status new by executor',
);

// Cancel
$task = new TaskContext(TaskStatus::New, $customerId, null);
$task = $taskService->performAction(
    $task,
    TaskAction::Cancel,
    $customerUser,
);
echo $task->getStatus()->label() . '<br>' . PHP_EOL;

// Bid - Assign - Complete
$task = new TaskContext(TaskStatus::New, $customerId, null);
$task = $taskService->performAction(
    $task,
    TaskAction::Bid,
    $executorUser,
);
echo $task->getStatus()->label() . PHP_EOL;

$task = $taskService->assignExecutor(
    $task,
    $customerUser,
    $executorId,
);
echo $task->getStatus()->label() . PHP_EOL;

$task = $taskService->performAction(
    $task,
    TaskAction::Complete,
    $customerUser,
);
echo $task->getStatus()->label() . '<br>' . PHP_EOL;

// Assign - Refuse
$task = new TaskContext(TaskStatus::New, $customerId, null);
$task = $taskService->assignExecutor(
    $task,
    $customerUser,
    $executorId,
);
echo $task->getStatus()->label() . PHP_EOL;

$task = $taskService->performAction(
    $task,
    TaskAction::Refuse,
    $executorUser,
);
echo $task->getStatus()->label() . '<br>' . PHP_EOL;

echo 'Все тесты прошли успешно';
