<?php

declare(strict_types=1);

namespace app\services;

use app\dto\StoredFileDto;
use Sanweb\Taskforce\exception\FileException;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

final class FileStorage
{
    private const STORAGE_DIRECTORY = '/storage/task-attachments/';

    /**
     * Saves an uploaded file and returns its metadata.
     *
     * @throws FileException
     */
    public function store(UploadedFile $file, string $directory): StoredFileDto
    {
        $relativeDirectory = trim($directory, '/');
        $absoluteDirectory = $this->getAbsolutePath($relativeDirectory);

        if (!FileHelper::createDirectory($absoluteDirectory)) {
            throw new FileException('Не удалось создать каталог для файла.');
        }

        $storedName = hash_file('sha256', $file->tempName);

        if ($storedName === false) {
            throw new FileException('Не удалось вычислить хеш файла.');
        }

        $relativePath = $relativeDirectory . '/' . $storedName;
        $absolutePath = $this->getAbsolutePath($relativePath);

        if (!is_file($absolutePath) && !$file->saveAs($absolutePath)) {
            throw new FileException('Не удалось сохранить файл.');
        }

        return new StoredFileDto(
            filePath: $relativePath,
            originalName: basename(str_replace('\\', '/', $file->name)),
            mimeType: FileHelper::getMimeType($absolutePath),
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
        return dirname(__DIR__) . self::STORAGE_DIRECTORY . $relativePath;
    }
}
