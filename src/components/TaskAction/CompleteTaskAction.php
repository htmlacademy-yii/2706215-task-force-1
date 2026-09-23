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
     *
     * @return TaskAction
     */
    #[Override]
    public function getAction(): TaskAction
    {
        return TaskAction::Complete;
    }

    /**
     * {@inheritdoc}
     *
     * @param TaskContext $task
     * @param ActorContext $actor
     *
     * @return bool
     */
    #[Override]
    public function isAllowed(TaskContext $task, ActorContext $actor): bool
    {
        return $actor->getId() === $task->getCustomerId();
    }
}
