<?php

declare(strict_types=1);

namespace app\dto;

/**
 * Contains normalized filters for the task list query.
 */
final readonly class TaskFilterDto
{
    /**
     * Initializes task list filters.
     */
    public function __construct(
        public array $categories = [],
        public bool $isRemote = false,
        public bool $hasNoBid = false,
        public ?int $createdAfter = null,
    ) {}
}
