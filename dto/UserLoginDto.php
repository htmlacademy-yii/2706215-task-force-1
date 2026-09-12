<?php

declare(strict_types=1);

namespace app\dto;

/**
 * Contains validated user credentials for authentication.
 */
final readonly class UserLoginDto
{
    /**
     * Initializes user login credentials.
     */
    public function __construct(
        public string $email = '',
        public string $password = '',
    ) {}
}
