<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\repositories;

use Sanweb\Taskforce\dto\TaskFilterDto;
use app\models\Attachment;
use app\models\Bid;
use app\models\Task;
use Sanweb\Taskforce\enum\TaskStatus;
use yii\db\ActiveQuery;

/**
 * Provides task-related persistence queries.
 */
final class TaskRepository
{
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
