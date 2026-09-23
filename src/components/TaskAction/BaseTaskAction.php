<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\components\TaskAction;

use Sanweb\Taskforce\domain\task\ActorContext;
use Sanweb\Taskforce\domain\task\TaskContext;
use Sanweb\Taskforce\enum\TaskAction;

/**
 * Base class for task actions (required by the specification).
 */
abstract class BaseTaskAction
{
    /**
     * Returns the string identifier of the action.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->getAction()->value;
    }

    /**
     * Returns the human-readable action label.
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->getAction()->label();
    }

    /**
     * Returns the action type.
     *
     * @return TaskAction
     */
    abstract public function getAction(): TaskAction;

    /**
     * Checks whether the action is allowed for the user.
     *
     * @param TaskContext $task
     * @param ActorContext $actor
     *
     * @return bool
     */
    abstract public function isAllowed(TaskContext $task, ActorContext $actor): bool;
}
