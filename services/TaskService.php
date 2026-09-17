<?php

declare(strict_types=1);

namespace app\services;

use app\dto\TaskCreateDto;
use app\models\Attachment;
use app\models\Bid;
use app\models\Review;
use app\models\Task;
use app\models\User;
use app\repositories\TaskRepository;
use app\services\geocoding\GeocoderInterface;
use app\services\geocoding\GeocodingException;
use Sanweb\Taskforce\enum\BidStatus;
use Sanweb\Taskforce\enum\StorageArea;
use Sanweb\Taskforce\enum\TaskAction;
use Sanweb\Taskforce\enum\TaskStatus;
use Sanweb\Taskforce\exception\EntityNotFoundException;
use Sanweb\Taskforce\exception\FileException;
use Sanweb\Taskforce\exception\TaskActionException;
use Sanweb\Taskforce\exception\TaskCreateException;
use Sanweb\Taskforce\domain\task\ActorContext;
use Sanweb\Taskforce\domain\task\TaskContext;
use Sanweb\Taskforce\domain\task\TaskWorkflow;
use Throwable;
use yii\web\UploadedFile;

/**
 * Coordinates task persistence and domain workflow operations.
 */
final class TaskService
{
    /**
     * Creates the task service.
     */
    public function __construct(
        private readonly FileStorage $fileStorage,
        private readonly TaskRepository $taskRepository,
        private readonly TaskWorkflow $taskWorkflow,
        private readonly GeocoderInterface $geocoder,
    ) {
    }

    /**
     * Creates a task with files.
     *
     * @param list<UploadedFile> $files
     *
     * @throws FileException
     * @throws GeocodingException
     * @throws TaskCreateException
     */
    public function create(TaskCreateDto $dto, int $customerId, array $files = []): Task
    {
        $coordinates = null;

        if ($dto->location !== null) {
            $coordinates = $this->geocoder->geocode($dto->location);

            if ($coordinates === null) {
                throw new GeocodingException('Не удалось найти указанный адрес.');
            }
        }

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

            if ($coordinates !== null) {
                $task->lat = (string) $coordinates->latitude;
                $task->lng = (string) $coordinates->longitude;
            }

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
     * Creates a bid.
     *
     * @throws EntityNotFoundException
     * @throws TaskActionException
     */
    public function createBid(int $taskId, User $user, int $price, ?string $comment): int
    {
        $task = $this->findTask($taskId);
        $this->ensurePersistenceChecks(TaskAction::Bid, $task, $user);
        $this->performAction($task, $user, TaskAction::Bid);

        $bid = new Bid();
        $bid->task_id = $task->id;
        $bid->user_id = $user->id;
        $bid->price = $price;
        $bid->comment = $comment;

        if (!$bid->save()) {
            throw new TaskActionException('Не удалось сохранить отклик.');
        }

        return (int) $task->id;
    }

    /**
     * Accepts a bid.
     *
     * @throws EntityNotFoundException
     * @throws TaskActionException
     */
    public function acceptBid(int $bidId, User $user): int
    {
        return Task::getDb()->transaction(function () use ($bidId, $user): int {
            $bid = $this->findBid($bidId);
            $task = $this->findTask((int) $bid->task_id);

            if ($bid->status !== BidStatus::New->value) {
                throw new TaskActionException('Отклик уже был обработан.');
            }

            $this->ensureBidBelongsToExecutor($bid, $task);
            $result = $this->taskWorkflow->assignExecutor(
                $this->toTaskContext($task),
                $this->toActorContext($user),
                (int) $bid->user_id,
            );

            $this->applyTaskContext($task, $result);
            $bid->status = BidStatus::Accepted->value;

            if (!$task->save() || !$bid->save()) {
                throw new TaskActionException('Не удалось принять отклик.');
            }

            return (int) $task->id;
        });
    }

    /**
     * Rejects a bid.
     *
     * @throws EntityNotFoundException
     * @throws TaskActionException
     */
    public function rejectBid(int $bidId, User $user): int
    {
        $bid = $this->findBid($bidId);
        $task = $this->findTask((int) $bid->task_id);
        $this->ensureBidCanBeRejected($bid, $task, $user);
        $bid->status = BidStatus::Rejected->value;

        if (!$bid->save()) {
            throw new TaskActionException('Не удалось отклонить отклик.');
        }

        return (int) $task->id;
    }

    /**
     * Completes a task and saves a review.
     *
     * @throws EntityNotFoundException
     * @throws TaskActionException
     */
    public function complete(int $taskId, User $user, int $score, string $comment): int
    {
        return Task::getDb()->transaction(function () use ($taskId, $user, $score, $comment): int {
            $task = $this->findTask($taskId);
            $this->ensurePersistenceChecks(TaskAction::Complete, $task, $user);
            $result = $this->performAction($task, $user, TaskAction::Complete);

            $review = new Review();
            $review->customer_id = $user->id;
            $review->task_id = $task->id;
            $review->executor_id = $task->executor_id;
            $review->score = $score;
            $review->comment = $comment;
            $this->applyTaskContext($task, $result);

            if (!$review->save() || !$task->save()) {
                throw new TaskActionException('Не удалось завершить задание.');
            }

            return (int) $task->id;
        });
    }

    /**
     * Marks a refused task as failed.
     *
     * @throws EntityNotFoundException
     * @throws TaskActionException
     */
    public function refuse(int $taskId, User $user): int
    {
        return $this->performActionAndSave($taskId, $user, TaskAction::Refuse);
    }

    /**
     * Cancels a task.
     *
     * @throws EntityNotFoundException
     * @throws TaskActionException
     */
    public function cancel(int $taskId, User $user): int
    {
        return $this->performActionAndSave($taskId, $user, TaskAction::Cancel);
    }

    /**
     * Returns actions available to the user.
     *
     * @return list<TaskAction>
     */
    public function getAvailableActions(Task $task, User $user): array
    {
        return array_values(array_filter(
            $this->taskWorkflow->getAvailablePageActions(
                $this->toTaskContext($task),
                $this->toActorContext($user),
            ),
            fn (TaskAction $action): bool => $this->passesPersistenceChecks($action, $task, $user),
        ));
    }

    /**
     * Checks whether an action is available.
     */
    public function isActionAvailable(TaskAction $action, Task $task, User $user): bool
    {
        return $this->taskWorkflow->isActionAvailable(
            $this->toTaskContext($task),
            $action,
            $this->toActorContext($user),
        ) && $this->passesPersistenceChecks($action, $task, $user);
    }

    /**
     * Saves task files.
     *
     * @param list<UploadedFile> $files
     *
     * @throws FileException
     * @throws TaskCreateException
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
     * Runs an action and saves the task.
     *
     * @throws EntityNotFoundException
     * @throws TaskActionException
     */
    private function performActionAndSave(int $taskId, User $user, TaskAction $action): int
    {
        $task = $this->findTask($taskId);
        $result = $this->performAction($task, $user, $action);
        $this->applyTaskContext($task, $result);

        if (!$task->save()) {
            throw new TaskActionException('Не удалось изменить статус задания.');
        }

        return (int) $task->id;
    }

    /**
     * Runs an action in the task workflow.
     *
     * @throws TaskActionException
     */
    private function performAction(
        Task $task,
        User $user,
        TaskAction $action,
    ): TaskContext {
        return $this->taskWorkflow->performAction(
            $this->toTaskContext($task),
            $action,
            $this->toActorContext($user),
        );
    }

    /**
     * Creates task data for action checks.
     */
    private function toTaskContext(Task $task): TaskContext
    {
        return new TaskContext(
            TaskStatus::from($task->status),
            (int) $task->customer_id,
            $task->executor_id === null ? null : (int) $task->executor_id,
        );
    }

    /**
     * Creates user data for action checks.
     */
    private function toActorContext(User $user): ActorContext
    {
        return new ActorContext((int) $user->id, (bool) $user->is_executor);
    }

    /**
     * Updates a task from an action result.
     */
    private function applyTaskContext(Task $task, TaskContext $taskContext): void
    {
        $task->status = $taskContext->getStatus()->value;
        $task->executor_id = $taskContext->getExecutorId();
    }

    /**
     * Checks rules that use saved data.
     */
    private function passesPersistenceChecks(TaskAction $action, Task $task, User $user): bool
    {
        return match ($action) {
            TaskAction::Bid => !$task->getBids()->andWhere(['user_id' => $user->id])->exists(),
            TaskAction::Complete => !$task->getReview()->exists(),
            default => true,
        };
    }

    /**
     * Checks that the action was not done before.
     *
     * @throws TaskActionException
     */
    private function ensurePersistenceChecks(TaskAction $action, Task $task, User $user): void
    {
        if (!$this->passesPersistenceChecks($action, $task, $user)) {
            throw new TaskActionException('Действие уже было выполнено ранее.');
        }
    }

    /**
     * Checks that the bid belongs to an executor.
     *
     * @throws TaskActionException
     */
    private function ensureBidBelongsToExecutor(Bid $bid, Task $task): void
    {
        if (
            !(bool) $bid->user->is_executor
            || (int) $bid->user_id === (int) $task->customer_id
        ) {
            throw new TaskActionException('Отклик не принадлежит исполнителю.');
        }
    }

    /**
     * Checks whether the bid can be rejected.
     *
     * @throws TaskActionException
     */
    private function ensureBidCanBeRejected(Bid $bid, Task $task, User $user): void
    {
        if ((int) $user->id !== (int) $task->customer_id) {
            throw new TaskActionException('Отклонять отклики может только автор задания.');
        }

        if ($task->status !== TaskStatus::New->value || $bid->status !== BidStatus::New->value) {
            throw new TaskActionException('Отклик недоступен для отклонения в текущем статусе.');
        }

        $this->ensureBidBelongsToExecutor($bid, $task);
    }

    /**
     * Finds a task by ID.
     *
     * @throws EntityNotFoundException
     */
    private function findTask(int $taskId): Task
    {
        $task = $this->taskRepository->findById($taskId);

        if ($task === null) {
            throw new EntityNotFoundException('Задание не найдено.');
        }

        return $task;
    }

    /**
     * Finds a bid by ID.
     *
     * @throws EntityNotFoundException
     */
    private function findBid(int $bidId): Bid
    {
        $bid = $this->taskRepository->findBidById($bidId);

        if ($bid === null) {
            throw new EntityNotFoundException('Отклик не найден.');
        }

        return $bid;
    }
}
