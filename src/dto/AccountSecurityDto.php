<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\dto;

/**
 * Contains validated account security settings.
 */
final readonly class AccountSecurityDto
{
    /**
     * Initializes account security settings.
     *
     * @param ?string $newPassword
     * @param bool $hideMyContacts
     */
    public function __construct(
        public ?string $newPassword,
        public bool $hideMyContacts,
    ) {}
}
