<?php

declare(strict_types=1);

namespace app\services;

use app\dto\TaskCreateDto;
use app\models\Attachment;
use app\models\Task;
use Sanweb\Taskforce\exception\TaskCreateException;
use Throwable;

final class TaskService
{
    public function __construct(
        private readonly FileStorage $fileStorage,
    ) {
    }

    /**
     * Creates a task and its attachments for the authenticated user.
     *
     * @throws TaskCreateException
     */
    public function create(TaskCreateDto $dto, int $customerId, array $files = []): Task
    {
        $transaction = Task::getDb()->beginTransaction();
        $attachmentDirectory = null;

        try {
            $task = new Task();
            $task->customer_id = $customerId;
            $task->category_id = $dto->categoryId;
            $task->title = $dto->title;
            $task->description = $dto->description;
            $task->budget = $dto->budget;
            $task->expire_date = $dto->expireDate;
            $task->location = $dto->location;
            $task->city_id = $dto->cityId;

            if (!$task->save()) {
                throw new TaskCreateException('Не удалось создать задание.');
            }

            if (!empty($files)) {
                $attachmentDirectory = (string) $task->id;
                $this->saveAttachments($task, $files);
            }

            $transaction->commit();

            return $task;
        } catch (Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            if ($attachmentDirectory !== null) {
                $this->fileStorage->removeDirectory($attachmentDirectory);
            }

            if ($exception instanceof TaskCreateException) {
                throw $exception;
            }

            throw new TaskCreateException('Не удалось создать задание.', 0, $exception);
        }
    }

    /**
     * Saves task files and their metadata.
     */
    private function saveAttachments(Task $task, array $files): void
    {
        foreach ($files as $file) {
            $storedFile = $this->fileStorage->store($file, (string) $task->id);

            $attachment = new Attachment();
            $attachment->task_id = $task->id;
            $attachment->file_path = $storedFile->filePath;
            $attachment->original_name = $storedFile->originalName;
            $attachment->mime_type = $storedFile->mimeType;
            $attachment->size_bytes = $storedFile->sizeBytes;

            if (!$attachment->save()) {
                throw new TaskCreateException('Не удалось сохранить данные файла задания.');
            }
        }
    }
}
