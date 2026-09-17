<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\services;

use Sanweb\Taskforce\dto\StoredFileDto;
use Sanweb\Taskforce\enum\StorageArea;
use Sanweb\Taskforce\exception\FileException;
use Yii;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * Stores files under the configured root directory using random names
 * and MIME-based extensions.
 */
final class FileStorage
{
    /**
     * @param array<string, string> $rootAliases
     */
    public function __construct(
        private readonly array $rootAliases,
    ) {}

    /**
     * Saves an uploaded file and returns its metadata.
     *
     * @throws FileException
     */
    public function store(
        UploadedFile $file,
        StorageArea $storageArea,
        string $directory,
    ): StoredFileDto {
        $relativeDirectory = trim($directory, '/');
        $absoluteDirectory = $this->getAbsolutePath($storageArea, $relativeDirectory);

        $this->ensureDirectoryExists($absoluteDirectory);

        $mimeType = $this->detectMimeType($file);
        $storedName = $this->buildStoredName($mimeType);

        $relativePath = $relativeDirectory . '/' . $storedName;
        $absolutePath = $this->getAbsolutePath($storageArea, $relativePath);

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
     *
     * @throws FileException
     */
    public function removeDirectory(StorageArea $storageArea, string $directory): void
    {
        $path = $this->getAbsolutePath($storageArea, trim($directory, '/'));

        if (is_dir($path)) {
            FileHelper::removeDirectory($path);
        }
    }

    /**
     * Returns an existing stored file path.
     *
     * @throws FileException
     */
    public function find(StorageArea $storageArea, string $filePath): ?string
    {
        $path = $this->getAbsolutePath($storageArea, $filePath);

        return is_file($path) ? $path : null;
    }

    /**
     * Resolves a relative path inside the file storage.
     */
    private function getAbsolutePath(StorageArea $storageArea, string $relativePath): string
    {
        $rootAlias = $this->rootAliases[$storageArea->value] ?? null;

        if (!is_string($rootAlias) || $rootAlias === '') {
            throw new FileException('Хранилище файлов не настроено.');
        }

        $rootPath = FileHelper::normalizePath(Yii::getAlias($rootAlias));
        $absolutePath = FileHelper::normalizePath(
            $rootPath . DIRECTORY_SEPARATOR . $relativePath,
        );

        if (
            $absolutePath === $rootPath
            || !str_starts_with($absolutePath, $rootPath . DIRECTORY_SEPARATOR)
        ) {
            throw new FileException('Путь выходит за пределы файлового хранилища.');
        }

        return $absolutePath;
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
