<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\components\TaskAction;

use Sanweb\Taskforce\enum\TaskAction;
use Sanweb\Taskforce\domain\task\ActorContext;
use Sanweb\Taskforce\domain\task\TaskContext;

/**
 * Base class for task actions (required by the specification).
 */
abstract class BaseTaskAction
{
    /**
     * Returns the string identifier of the action.
     */
    public function getName(): string
    {
        return $this->getAction()->value;
    }

    /**
     * Returns the human-readable action label.
     */
    public function getLabel(): string
    {
        return $this->getAction()->label();
    }

    /**
     * Returns the action type.
     */
    abstract public function getAction(): TaskAction;

    /**
     * Checks whether the action is allowed for the user.
     */
    abstract public function isAllowed(TaskContext $task, ActorContext $actor): bool;
}
