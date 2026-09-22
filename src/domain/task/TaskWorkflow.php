<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\domain\task;

use Sanweb\Taskforce\components\TaskAction\AssignTaskAction;
use Sanweb\Taskforce\components\TaskAction\BaseTaskAction;
use Sanweb\Taskforce\components\TaskAction\BidTaskAction;
use Sanweb\Taskforce\components\TaskAction\CancelTaskAction;
use Sanweb\Taskforce\components\TaskAction\CompleteTaskAction;
use Sanweb\Taskforce\components\TaskAction\RefuseTaskAction;
use Sanweb\Taskforce\enum\TaskAction;
use Sanweb\Taskforce\enum\TaskStatus;
use Sanweb\Taskforce\exception\TaskActionException;

/**
 * Evaluates and performs in-memory task state transitions.
 */
final readonly class TaskWorkflow
{
    /** @var array<string, BaseTaskAction> */
    private array $actions;

    /**
     * Registers the supported task actions.
     */
    public function __construct()
    {
        $this->actions = [
            TaskAction::Cancel->value => new CancelTaskAction(),
            TaskAction::Bid->value => new BidTaskAction(),
            TaskAction::Assign->value => new AssignTaskAction(),
            TaskAction::Complete->value => new CompleteTaskAction(),
            TaskAction::Refuse->value => new RefuseTaskAction(),
        ];
    }

    /**
     * Returns actions allowed by both the current task state and the actor.
     *
     * @param TaskContext $task
     * @param ActorContext $actor
     *
     * @return list<TaskAction>
     */
    public function getAvailablePageActions(
        TaskContext $task,
        ActorContext $actor,
    ): array {
        return array_values(array_filter(
            $this->getPageActionsForStatus($task->getStatus()),
            fn (TaskAction $action): bool => $this->isAllowedForActor($action, $task, $actor),
        ));
    }

    /**
     * Checks whether the action is available to the actor in the current state.
     *
     * @param TaskContext $task
     * @param TaskAction $action
     * @param ActorContext $actor
     *
     * @return bool
     */
    public function isActionAvailable(
        TaskContext $task,
        TaskAction $action,
        ActorContext $actor,
    ): bool {
        return in_array($action, $this->getActionsForStatus($task->getStatus()), true)
            && $this->isAllowedForActor($action, $task, $actor);
    }

    /**
     * Performs an action when it is available to the actor.
     *
     * @param TaskContext $task
     * @param TaskAction $action
     * @param ActorContext $actor
     *
     * @return TaskContext
     *
     * @throws TaskActionException
     */
    public function performAction(
        TaskContext $task,
        TaskAction $action,
        ActorContext $actor,
    ): TaskContext {
        if (!$this->isActionAvailable($task, $action, $actor)) {
            throw new TaskActionException(
                'Действие недоступно для текущего пользователя или статуса задания.',
            );
        }

        $nextStatus = $this->getNextStatus($action);

        return $nextStatus === null ? $task : $task->withStatus($nextStatus);
    }

    /**
     * Starts a task and assigns its executor.
     *
     * @param TaskContext $task
     * @param ActorContext $actor
     * @param int $executorId
     *
     * @return TaskContext
     *
     * @throws TaskActionException
     */
    public function assignExecutor(
        TaskContext $task,
        ActorContext $actor,
        int $executorId,
    ): TaskContext {
        if ($executorId <= 0) {
            throw new TaskActionException('Идентификатор исполнителя должен быть положительным целым числом.');
        }

        if ($executorId === $task->getCustomerId()) {
            throw new TaskActionException('Заказчик не может быть назначен исполнителем.');
        }

        return $this->performAction($task, TaskAction::Assign, $actor)
            ->withExecutor($executorId);
    }

    /**
     * Returns the status reached after the action.
     *
     * @param TaskAction $action
     *
     * @return ?TaskStatus
     */
    public function getNextStatus(TaskAction $action): ?TaskStatus
    {
        return match ($action) {
            TaskAction::Cancel => TaskStatus::Canceled,
            TaskAction::Assign => TaskStatus::InProgress,
            TaskAction::Complete => TaskStatus::Completed,
            TaskAction::Refuse => TaskStatus::Failed,
            TaskAction::Bid => null,
        };
    }

    /**
     * Returns actions available for the specified task status.
     *
     * @param TaskStatus $status
     *
     * @return list<TaskAction>
     */
    private function getActionsForStatus(TaskStatus $status): array
    {
        return match ($status) {
            TaskStatus::New => [
                TaskAction::Cancel,
                TaskAction::Bid,
                TaskAction::Assign,
            ],
            TaskStatus::InProgress => [
                TaskAction::Complete,
                TaskAction::Refuse,
            ],
            TaskStatus::Canceled,
            TaskStatus::Completed,
            TaskStatus::Failed => [],
        };
    }

    /**
     * Returns actions represented by buttons on the task page.
     * Assignment is handled on a concrete bid instead.
     *
     * @param TaskStatus $status
     *
     * @return list<TaskAction>
     */
    private function getPageActionsForStatus(TaskStatus $status): array
    {
        return array_values(array_filter(
            $this->getActionsForStatus($status),
            static fn (TaskAction $action): bool => $action !== TaskAction::Assign,
        ));
    }

    /**
     * Checks whether the actor satisfies action-specific rules.
     *
     * @param TaskAction $action
     * @param TaskContext $task
     * @param ActorContext $actor
     *
     * @return bool
     */
    private function isAllowedForActor(
        TaskAction $action,
        TaskContext $task,
        ActorContext $actor,
    ): bool {
        return $this->actions[$action->value]->isAllowed($task, $actor);
    }
}
