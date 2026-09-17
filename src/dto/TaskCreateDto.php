<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\dto;

/**
 * Contains validated data required to create a task.
 */
final readonly class TaskCreateDto
{
    /**
     * Initializes the data required to create a task.
     */
    public function __construct(
        public int $categoryId,
        public string $title,
        public string $description,
        public int $budget,
        public string $expireDate,
        public ?string $location,
        public ?int $cityId,
    ) {}
}
