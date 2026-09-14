<?php

declare(strict_types=1);

namespace app\services;

use app\dto\TaskCreateDto;
use app\models\Attachment;
use app\models\Bid;
use app\models\Task;
use Sanweb\Taskforce\enum\BidStatus;
use Sanweb\Taskforce\enum\StorageArea;
use Sanweb\Taskforce\enum\TaskStatus;
use Sanweb\Taskforce\exception\TaskActionException;
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
            // Keep task creation atomic: if any attachment fails, roll back
            // the database changes and remove all files already saved for the task.
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            if ($attachmentDirectory !== null) {
                $this->fileStorage->removeDirectory(
                    StorageArea::TaskAttachments,
                    $attachmentDirectory,
                );
            }

            if ($exception instanceof TaskCreateException) {
                throw $exception;
            }

            throw new TaskCreateException('Не удалось создать задание.', 0, $exception);
        }
    }

    /**
     * Accepts a bid and assigns its author as the task executor.
     *
     * @throws TaskActionException
     */
    public function acceptBid(Bid $bid, int $currentUserId): void
    {
        $task = $bid->task;
        $this->ensureBidCanBeChanged($bid, $task, $currentUserId);

        $transaction = Task::getDb()->beginTransaction();

        try {
            $task->executor_id = $bid->user_id;
            $task->status = TaskStatus::InProgress->value;
            $bid->status = BidStatus::Accepted->value;

            if (!$task->save() || !$bid->save()) {
                throw new TaskActionException('Не удалось принять отклик.');
            }

            $transaction->commit();
        } catch (Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            if ($exception instanceof TaskActionException) {
                throw $exception;
            }

            throw new TaskActionException('Не удалось принять отклик.', 0, $exception);
        }
    }

    /**
     * Rejects a bid for the task.
     *
     * @throws TaskActionException
     */
    public function rejectBid(Bid $bid, int $currentUserId): void
    {
        $this->ensureBidCanBeChanged($bid, $bid->task, $currentUserId);
        $bid->status = BidStatus::Rejected->value;

        if (!$bid->save()) {
            throw new TaskActionException('Не удалось отклонить отклик.');
        }
    }

    /**
     * Saves task files and their metadata.
     */
    private function saveAttachments(Task $task, array $files): void
    {
        foreach ($files as $file) {
            $storedFile = $this->fileStorage->store(
                $file,
                StorageArea::TaskAttachments,
                (string) $task->id,
            );

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

    /**
     * Ensures that the customer may change the bid in the current task state.
     *
     * @throws TaskActionException
     */
    private function ensureBidCanBeChanged(Bid $bid, Task $task, int $currentUserId): void
    {
        if ($task->customer_id !== $currentUserId) {
            throw new TaskActionException('Действие доступно только автору задания.');
        }

        if ($task->status !== TaskStatus::New->value || $bid->status !== BidStatus::New->value) {
            throw new TaskActionException('Действие недоступно в текущем статусе.');
        }
    }
}
