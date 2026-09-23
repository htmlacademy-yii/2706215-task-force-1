<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\components\TaskAction;

use Override;
use Sanweb\Taskforce\domain\task\ActorContext;
use Sanweb\Taskforce\domain\task\TaskContext;
use Sanweb\Taskforce\enum\TaskAction;

/**
 * Makes a bid on the task.
 */
final class BidTaskAction extends BaseTaskAction
{
    /**
     * {@inheritdoc}
     *
     * @return TaskAction
     */
    #[Override]
    public function getAction(): TaskAction
    {
        return TaskAction::Bid;
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
        return $actor->getIsExecutor()
            && $actor->getId() !== $task->getCustomerId();
    }
}
