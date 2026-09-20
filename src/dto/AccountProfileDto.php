<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\dto;

/**
 * Contains validated account profile data.
 */
final readonly class AccountProfileDto
{
    /**
     * Initializes account profile data.
     *
     * @param list<int> $categoryIds
     */
    public function __construct(
        public string $name,
        public string $email,
        public ?string $birthday,
        public ?string $phone,
        public ?string $telegram,
        public ?string $about,
        public array $categoryIds,
    ) {}
}
