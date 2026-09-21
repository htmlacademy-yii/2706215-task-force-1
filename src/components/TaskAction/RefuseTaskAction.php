<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\components\TaskAction;

use Override;
use Sanweb\Taskforce\domain\task\ActorContext;
use Sanweb\Taskforce\domain\task\TaskContext;
use Sanweb\Taskforce\enum\TaskAction;

/**
 * Refuses the task.
 */
final class RefuseTaskAction extends BaseTaskAction
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getAction(): TaskAction
    {
        return TaskAction::Refuse;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isAllowed(TaskContext $task, ActorContext $actor): bool
    {
        return $actor->getIsExecutor()
            && $actor->getId() === $task->getExecutorId();
    }
}
