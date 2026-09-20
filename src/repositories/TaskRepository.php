<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\repositories;

use app\models\Attachment;
use app\models\Bid;
use app\models\Task;
use DateTimeImmutable;
use Sanweb\Taskforce\dto\TaskFilterDto;
use Sanweb\Taskforce\enum\MyTaskFilter;
use Sanweb\Taskforce\enum\TaskStatus;
use yii\db\ActiveQuery;

/**
 * Provides task-related persistence queries.
 */
final class TaskRepository
{
    /**
     * Builds a query for tasks belonging to the current user's role context.
     *
     * @return ActiveQuery<Task>
     */
    public function findMyTasksQuery(
        int $userId,
        bool $isExecutor,
        MyTaskFilter $filter,
        ?DateTimeImmutable $today = null,
    ): ActiveQuery {
        $query = Task::find()
            ->orderBy(['task.created_at' => SORT_DESC])
            ->with(['category', 'city']);

        if ($isExecutor) {
            $query
                ->innerJoin('bid', 'bid.task_id = task.id')
                ->andWhere(['bid.user_id' => $userId])
                ->distinct();
        } else {
            $query->andWhere(['task.customer_id' => $userId]);
        }

        $todayValue = ($today ?? new DateTimeImmutable('today'))->format('Y-m-d');

        match ($filter) {
            MyTaskFilter::New => $query->andWhere([
                'task.status' => TaskStatus::New->value,
            ]),
            MyTaskFilter::InProgress => $query
                ->andWhere(['task.status' => TaskStatus::InProgress->value])
                ->andFilterWhere($isExecutor ? ['>=', 'task.expire_date', $todayValue] : []),
            MyTaskFilter::Overdue => $query
                ->andWhere(['task.status' => TaskStatus::InProgress->value])
                ->andWhere(['<', 'task.expire_date', $todayValue]),
            MyTaskFilter::Closed => $query->andWhere([
                'task.status' => $isExecutor
                    ? [TaskStatus::Completed->value, TaskStatus::Failed->value]
                    : [
                        TaskStatus::Canceled->value,
                        TaskStatus::Completed->value,
                        TaskStatus::Failed->value,
                    ],
            ]),
        };

        return $query;
    }

    /**
     * Builds a query for new tasks matching the filter.
     *
     * @return ActiveQuery<Task>
     */
    public function findNewQuery(TaskFilterDto $filter): ActiveQuery
    {
        $query = Task::find()
            ->where(['task.status' => TaskStatus::New->value])
            ->orderBy(['task.created_at' => SORT_DESC])
            ->with(['category', 'city']);

        $query->andFilterWhere(['in', 'task.category_id', $filter->categories]);

        if ($filter->isRemote) {
            $query->andWhere(['task.city_id' => null]);
        }

        if ($filter->hasNoBid) {
            $query->joinWith('bids', false)->andWhere(['bid.id' => null]);
        }

        if ($filter->createdAfter !== null) {
            $query->andWhere(['>=', 'task.created_at', $filter->createdAfter]);
        }

        return $query;
    }

    /**
     * Finds a task by ID.
     */
    public function findById(int $id): ?Task
    {
        return Task::findOne($id);
    }

    /**
     * Finds a task with the data required by the task details page.
     */
    public function findDetailsById(int $id, int $viewerId): ?Task
    {
        $task = Task::find()
            ->where(['task.id' => $id])
            ->with([
                'attachments',
                'category',
            ])
            ->one();

        if ($task === null) {
            return null;
        }

        $bidsQuery = $task->getBids()->with([
            'user.executorStats',
            'user.receivedReviews',
        ]);

        if ($task->customer_id !== $viewerId) {
            $bidsQuery->andWhere(['bid.user_id' => $viewerId]);
        }

        $task->populateRelation('bids', $bidsQuery->all());

        return $task;
    }

    /**
     * Finds a task attachment by ID.
     */
    public function findAttachmentById(int $id): ?Attachment
    {
        return Attachment::findOne($id);
    }

    /**
     * Finds a bid by ID with its author.
     */
    public function findBidById(int $id): ?Bid
    {
        return Bid::find()
            ->where(['bid.id' => $id])
            ->with('user')
            ->one();
    }

    /**
     * Checks for an active task assigned to the executor by the customer.
     */
    public function hasActiveTaskWithExecutor(int $customerId, int $executorId): bool
    {
        return Task::find()
            ->where([
                'customer_id' => $customerId,
                'executor_id' => $executorId,
                'status' => TaskStatus::InProgress->value,
            ])
            ->exists();
    }
}
