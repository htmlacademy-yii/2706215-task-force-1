<?php

declare(strict_types=1);

namespace app\dto;

/**
 * Contains validated data required to register a user.
 */
final readonly class UserSignupDto
{
    /**
     * Initializes user registration data.
     */
    public function __construct(
        public string $name = '',
        public string $email = '',
        public string $password = '',
        public string $passwordConfirm = '',
        public ?int $cityId = null,
        public bool $isExecutor = false,
    ) {}
}
