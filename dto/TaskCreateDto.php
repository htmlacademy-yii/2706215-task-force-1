<?php

declare(strict_types=1);

namespace app\dto;

/**
 * Contains validated data required to create a task.
 */
final readonly class TaskCreateDto
{
    /**
     * Initializes the data required to create a task.
     */
    public function __construct(
        public int $categoryId = 0,
        public string $title = '',
        public string $description = '',
        public int $budget = 0,
        public string $expireDate = '',
        public ?string $location = null,
        public ?int $cityId = null,
    ) {}
}
