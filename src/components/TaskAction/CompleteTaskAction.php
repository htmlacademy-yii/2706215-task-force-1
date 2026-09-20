<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\components\TaskAction;

use Override;
use Sanweb\Taskforce\domain\task\ActorContext;
use Sanweb\Taskforce\domain\task\TaskContext;
use Sanweb\Taskforce\enum\TaskAction;

/**
 * Completes the task.
 */
final class CompleteTaskAction extends BaseTaskAction
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getAction(): TaskAction
    {
        return TaskAction::Complete;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isAllowed(TaskContext $task, ActorContext $actor): bool
    {
        return $actor->getId() === $task->getCustomerId();
    }
}
