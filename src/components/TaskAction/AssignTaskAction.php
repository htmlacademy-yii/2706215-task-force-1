<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\components\TaskAction;

use Override;
use Sanweb\Taskforce\domain\task\ActorContext;
use Sanweb\Taskforce\domain\task\TaskContext;
use Sanweb\Taskforce\enum\TaskAction;

/**
 * Assigns an executor to the task.
 */
final class AssignTaskAction extends BaseTaskAction
{
    #[Override]
    public function getAction(): TaskAction
    {
        return TaskAction::Assign;
    }

    #[Override]
    public function isAllowed(TaskContext $task, ActorContext $actor): bool
    {
        return $actor->getId() === $task->getCustomerId();
    }
}
