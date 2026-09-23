<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\domain\task;

use InvalidArgumentException;
use Sanweb\Taskforce\enum\TaskStatus;

/**
 * Immutable task data used by the task workflow.
 */
final readonly class TaskContext
{
    /**
     * Creates a task context.
     *
     * @param TaskStatus $status
     * @param int $customerId
     * @param ?int $executorId
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        private TaskStatus $status,
        private int $customerId,
        private ?int $executorId = null
    ) {
        if ($customerId <= 0) {
            throw new InvalidArgumentException(sprintf(
                'Идентификатор заказчика должен быть положительным числом, передано: %d.',
                $customerId,
            ));
        }

        if ($executorId !== null && $executorId <= 0) {
            throw new InvalidArgumentException(sprintf(
                'Идентификатор исполнителя должен быть положительным числом, передано: %d.',
                $executorId,
            ));
        }
    }

    /**
     * Returns a copy with the specified status.
     *
     * @param TaskStatus $status
     *
     * @return self
     */
    public function withStatus(TaskStatus $status): self
    {
        return new self(
            status: $status,
            customerId: $this->customerId,
            executorId: $this->executorId,
        );
    }

    /**
     * Returns a copy with the specified executor.
     *
     * @param int $executorId
     *
     * @return self
     */
    public function withExecutor(int $executorId): self
    {
        return new self(
            status: $this->status,
            customerId: $this->customerId,
            executorId: $executorId,
        );
    }

    /**
     * Returns the current task status.
     *
     * @return TaskStatus
     */
    public function getStatus(): TaskStatus
    {
        return $this->status;
    }

    /**
     * Returns the customer identifier.
     *
     * @return int
     */
    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    /**
     * Returns the assigned executor identifier, if any.
     *
     * @return ?int
     */
    public function getExecutorId(): ?int
    {
        return $this->executorId;
    }
}
