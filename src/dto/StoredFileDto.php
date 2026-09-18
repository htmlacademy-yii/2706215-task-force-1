<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\dto;

/**
 * Contains metadata of a file saved in storage.
 */
final readonly class StoredFileDto
{
    /**
     * Initializes stored file metadata.
     */
    public function __construct(
        public string $filePath,
        public string $originalName,
        public ?string $mimeType,
        public int $sizeBytes,
    ) {}
}
