<?php

declare(strict_types=1);

namespace app\services;

use app\dto\StoredFileDto;
use Sanweb\Taskforce\exception\FileException;
use Yii;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * Stores files using random names and MIME-based extensions
 * in entity-based directories.
 *
 * Unlike the original assignment requirements, files are intentionally stored
 * outside the public web directory to prevent direct access. They must be
 * served through a controller after the required access checks.
 */
final class FileStorage
{
    private const STORAGE_DIRECTORY_ALIAS = '@app/storage/task-attachments/';

    /**
     * Saves an uploaded file and returns its metadata.
     *
     * @throws FileException
     */
    public function store(UploadedFile $file, string $directory): StoredFileDto
    {
        $relativeDirectory = trim($directory, '/');
        $absoluteDirectory = $this->getAbsolutePath($relativeDirectory);

        $this->ensureDirectoryExists($absoluteDirectory);

        $mimeType = $this->detectMimeType($file);
        $storedName = $this->buildStoredName($mimeType);

        $relativePath = $relativeDirectory . '/' . $storedName;
        $absolutePath = $this->getAbsolutePath($relativePath);

        $this->saveFile($file, $absolutePath);

        return new StoredFileDto(
            filePath: $relativePath,
            originalName: basename(str_replace('\\', '/', $file->name)),
            mimeType: $mimeType,
            sizeBytes: $file->size,
        );
    }

    /**
     * Removes a directory with all stored files.
     */
    public function removeDirectory(string $directory): void
    {
        $path = $this->getAbsolutePath(trim($directory, '/'));

        if (is_dir($path)) {
            FileHelper::removeDirectory($path);
        }
    }

    /**
     * Returns an existing stored file path.
     */
    public function find(string $filePath): ?string
    {
        $path = $this->getAbsolutePath($filePath);

        return is_file($path) ? $path : null;
    }

    /**
     * Resolves a relative path inside the file storage.
     */
    private function getAbsolutePath(string $relativePath): string
    {
        return Yii::getAlias(self::STORAGE_DIRECTORY_ALIAS) . $relativePath;
    }

    private function ensureDirectoryExists(string $directory): void
    {
        if (!FileHelper::createDirectory($directory)) {
            throw new FileException('Не удалось создать каталог для файла.');
        }
    }

    private function detectMimeType(UploadedFile $file): string
    {
        $mimeType = FileHelper::getMimeType($file->tempName);

        if ($mimeType === null) {
            throw new FileException('Не удалось определить MIME-тип файла.');
        }

        return $mimeType;
    }

    private function buildStoredName(string $mimeType): string
    {
        $name = bin2hex(random_bytes(16));
        $extension = FileHelper::getExtensionsByMimeType($mimeType)[0] ?? null;

        return $extension !== null
            ? $name . '.' . $extension
            : $name;
    }

    private function saveFile(UploadedFile $file, string $path): void
    {
        if (!$file->saveAs($path)) {
            throw new FileException('Не удалось сохранить файл.');
        }
    }
}
