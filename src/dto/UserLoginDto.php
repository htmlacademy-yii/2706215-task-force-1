<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\dto;

/**
 * Contains validated user credentials for authentication.
 */
final readonly class UserLoginDto
{
    /**
     * Initializes user login credentials.
     *
     * @param string $email
     * @param string $password
     */
    public function __construct(
        public string $email,
        public string $password,
    ) {}
}
