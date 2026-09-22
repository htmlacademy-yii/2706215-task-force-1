<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\domain\task;

use InvalidArgumentException;

/**
 * Immutable actor data used to evaluate task workflow permissions.
 */
final readonly class ActorContext
{
    /**
     * Creates an actor context.
     *
     * @param int $id
     * @param bool $isExecutor
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        private int $id,
        private bool $isExecutor,
    ) {
        if ($id <= 0) {
            throw new InvalidArgumentException(sprintf(
                'Идентификатор пользователя должен быть положительным числом, передано: %d.',
                $id,
            ));
        }
    }

    /**
     * Returns the actor identifier.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Whether the actor can work as an executor.
     *
     * @return bool
     */
    public function getIsExecutor(): bool
    {
        return $this->isExecutor;
    }
}
