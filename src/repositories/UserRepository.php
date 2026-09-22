<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\repositories;

use app\models\User;

/**
 * Provides user read queries with scenario-specific relations.
 */
final class UserRepository
{
    /**
     * Finds the current user with relations required by account settings.
     *
     * @param int $id
     *
     * @return ?User
     */
    public function findForSettings(int $id): ?User
    {
        return User::find()
            ->where(['id' => $id])
            ->with(['executorProfile', 'executorSpecializations'])
            ->one();
    }

    /**
     * Finds a user by ID.
     *
     * @param int $id
     *
     * @return ?User
     */
    public function findById(int $id): ?User
    {
        return User::findOne($id);
    }

    /**
     * Finds a user by their permanent GitHub account ID.
     *
     * @param int $githubId
     *
     * @return ?User
     */
    public function findByGithubId(int $githubId): ?User
    {
        return User::findOne(['github_id' => $githubId]);
    }

    /**
     * Finds a user by email.
     *
     * @param string $email
     *
     * @return ?User
     */
    public function findByEmail(string $email): ?User
    {
        return User::findOne(['email' => $email]);
    }

    /**
     * Finds an executor by ID with profile data.
     *
     * @param int $id
     *
     * @return ?User
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
