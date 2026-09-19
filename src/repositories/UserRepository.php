<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\repositories;

use app\models\User;

final class UserRepository
{
    /**
     * Finds a user by ID.
     */
    public function findById(int $id): ?User
    {
        return User::findOne($id);
    }

    /**
     * Finds a user by their permanent GitHub account ID.
     */
    public function findByGithubId(int $githubId): ?User
    {
        return User::findOne(['github_id' => $githubId]);
    }

    /**
     * Finds a user by email.
     */
    public function findByEmail(string $email): ?User
    {
        return User::findOne(['email' => $email]);
    }

    /**
     * Finds an executor by ID with profile data.
     */
    public function findExecutorById(int $id): ?User
    {
        return User::find()
            ->where([
                'id' => $id,
                'is_executor' => 1,
            ])
            ->with([
                'city',
                'categories',
                'executorProfile',
                'executorStats',
                'receivedReviews.customer',
                'receivedReviews.task',
            ])
            ->one();
    }
}
