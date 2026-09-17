<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\dto;

/**
 * Contains validated data required to register a user.
 */
final readonly class UserSignupDto
{
    /**
     * Initializes user registration data.
     */
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?int $cityId,
        public bool $isExecutor,
    ) {}
}
