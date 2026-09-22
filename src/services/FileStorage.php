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
     * Creates file storage with root aliases keyed by storage area.
     *
     * @param array<string, string> $rootAliases
     */
    public function __construct(
        private readonly array $rootAliases,
    ) {}

    /**
     * Saves an uploaded file and returns its metadata.
     *
     * @param UploadedFile $file
     * @param StorageArea $storageArea
     * @param string $directory
     *
     * @return StoredFileDto
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
     * @param StorageArea $storageArea
     * @param string $directory
     *
     * @return void
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
     * Removes one stored file when it exists.
     *
     * @param StorageArea $storageArea
     * @param string $filePath
     *
     * @return void
     *
     * @throws FileException
     */
    public function remove(StorageArea $storageArea, string $filePath): void
    {
        $path = $this->getAbsolutePath($storageArea, $filePath);

        if (is_file($path) && !unlink($path)) {
            throw new FileException('Не удалось удалить файл.');
        }
    }

    /**
     * Returns an existing stored file path.
     *
     * @param StorageArea $storageArea
     * @param string $filePath
     *
     * @return ?string
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
     *
     * @param StorageArea $storageArea
     * @param string $relativePath
     *
     * @return string
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

    /**
     * Creates the target directory when it does not exist.
     *
     * @param string $directory
     *
     * @return void
     */
    private function ensureDirectoryExists(string $directory): void
    {
        if (!FileHelper::createDirectory($directory)) {
            throw new FileException('Не удалось создать каталог для файла.');
        }
    }

    /**
     * Detects a file MIME type from its temporary contents.
     *
     * @param UploadedFile $file
     *
     * @return string
     */
    private function detectMimeType(UploadedFile $file): string
    {
        $mimeType = FileHelper::getMimeType($file->tempName);

        if ($mimeType === null) {
            throw new FileException('Не удалось определить MIME-тип файла.');
        }

        return $mimeType;
    }

    /**
     * Creates a random storage name with a MIME-derived extension.
     *
     * @param string $mimeType
     *
     * @return string
     */
    private function buildStoredName(string $mimeType): string
    {
        $name = bin2hex(random_bytes(16));
        $extension = FileHelper::getExtensionsByMimeType($mimeType)[0] ?? null;

        return $extension !== null
            ? $name . '.' . $extension
            : $name;
    }

    /**
     * Persists an uploaded file at the resolved storage path.
     *
     * @param UploadedFile $file
     * @param string $path
     *
     * @return void
     */
    private function saveFile(UploadedFile $file, string $path): void
    {
        if (!$file->saveAs($path)) {
            throw new FileException('Не удалось сохранить файл.');
        }
    }
}
