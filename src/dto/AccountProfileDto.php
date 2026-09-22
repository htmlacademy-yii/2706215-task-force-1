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
     * @param string $name
     * @param string $email
     * @param ?string $birthday
     * @param ?string $phone
     * @param ?string $telegram
     * @param ?string $about
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
