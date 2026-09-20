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
     */
    public function __construct(
        public ?string $newPassword,
        public bool $hideMyContacts,
    ) {}
}
