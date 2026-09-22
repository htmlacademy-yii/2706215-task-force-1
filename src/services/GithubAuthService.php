<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\services;

use app\models\User;
use Sanweb\Taskforce\exception\GithubAuthException;
use Sanweb\Taskforce\repositories\UserRepository;
use Throwable;
use yii\authclient\ClientInterface;
use yii\db\IntegrityException;

/**
 * Registers or finds a user authenticated by GitHub.
 */
final class GithubAuthService
{
    /**
     * Creates the GitHub authentication service.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {}

    /**
     * Returns a user identified by the permanent GitHub account ID.
     *
     * @param ClientInterface $client
     *
     * @return User
     *
     * @throws GithubAuthException
     */
    public function authenticate(ClientInterface $client): User
    {
        if ($client->getId() !== 'github') {
            throw new GithubAuthException('Неподдерживаемый сервис авторизации.');
        }

        $attributes = $client->getUserAttributes();
        $githubId = $this->extractGithubId($attributes);

        $user = $this->userRepository->findByGithubId($githubId);

        if ($user !== null) {
            return $user;
        }

        $email = $this->extractEmail($attributes);

        try {
            $this->findOrRegisterInTransaction($githubId, $email, $attributes);
        } catch (IntegrityException $exception) {
            // A concurrent callback may have saved the same GitHub ID first.
            $user = $this->userRepository->findByGithubId($githubId);

            if ($user === null) {
                throw new GithubAuthException(
                    'Не удалось связать аккаунт GitHub с пользователем.',
                    0,
                    $exception,
                );
            }
        } catch (GithubAuthException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new GithubAuthException(
                'Не удалось выполнить вход через GitHub.',
                0,
                $exception,
            );
        }

        $user = $this->userRepository->findByGithubId($githubId);

        if ($user === null) {
            throw new GithubAuthException('Пользователь GitHub не найден после регистрации.');
        }

        return $user;
    }

    /**
     * Finds or registers a GitHub user within a transaction.
     *
     * @param int $githubId
     * @param string $email
     * @param array<string, mixed> $attributes
     *
     * @return void
     *
     * @throws GithubAuthException
     * @throws IntegrityException
     */
    private function findOrRegisterInTransaction(
        int $githubId,
        string $email,
        array $attributes,
    ): void {
        $transaction = User::getDb()->beginTransaction();

        try {
            // The callback can be processed concurrently, so repeat the lookup
            // after starting the transaction before creating or linking a user.
            $user = $this->userRepository->findByGithubId($githubId);

            if ($user === null) {
                $this->register($githubId, $email, $attributes);
            }

            $transaction->commit();
        } catch (Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * Extracts and validates the permanent GitHub account ID.
     *
     * @param array<string, mixed> $attributes
     *
     * @return int
     *
     * @throws GithubAuthException
     */
    private function extractGithubId(array $attributes): int
    {
        $githubId = filter_var(
            $attributes['id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]],
        );

        if ($githubId === false) {
            throw new GithubAuthException('GitHub не вернул корректный ID пользователя.');
        }

        return $githubId;
    }

    /**
     * Extracts and validates the email returned by GitHub.
     *
     * @param array<string, mixed> $attributes
     *
     * @return string
     *
     * @throws GithubAuthException
     */
    private function extractEmail(array $attributes): string
    {
        $email = mb_strtolower(trim((string) ($attributes['email'] ?? '')));

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new GithubAuthException(
                'GitHub не предоставил подтверждённый email. Проверьте настройки аккаунта GitHub.',
            );
        }

        return $email;
    }

    /**
     * Registers a GitHub user or links an existing user with the same email.
     *
     * @param int $githubId
     * @param string $email
     * @param array<string, mixed> $attributes
     *
     * @return User
     *
     * @throws GithubAuthException
     */
    private function register(int $githubId, string $email, array $attributes): User
    {
        $user = $this->userRepository->findByEmail($email);

        if ($user !== null) {
            return $this->linkGithubAccount($user, $githubId);
        }

        return $this->createGithubUser($githubId, $email, $attributes);
    }

    /**
     * Links an existing user to a GitHub account.
     *
     * @param User $user
     * @param int $githubId
     *
     * @return User
     *
     * @throws GithubAuthException
     */
    private function linkGithubAccount(User $user, int $githubId): User
    {
        if ($user->github_id !== null && (int) $user->github_id !== $githubId) {
            throw new GithubAuthException(
                'Этот email уже связан с другим аккаунтом GitHub.',
            );
        }

        $updatedRows = User::updateAll(
            ['github_id' => $githubId],
            ['id' => $user->id, 'github_id' => null],
        );

        if ($updatedRows !== 1) {
            throw new GithubAuthException(
                'Не удалось связать аккаунт GitHub с пользователем.',
            );
        }

        $linkedUser = $this->userRepository->findByGithubId($githubId);

        if ($linkedUser === null) {
            throw new GithubAuthException(
                'Пользователь не найден после привязки аккаунта GitHub.',
            );
        }

        return $linkedUser;
    }

    /**
     * Creates a user from GitHub account attributes.
     *
     * @param int $githubId
     * @param string $email
     * @param array<string, mixed> $attributes
     *
     * @return User
     *
     * @throws GithubAuthException
     */
    private function createGithubUser(
        int $githubId,
        string $email,
        array $attributes,
    ): User {
        $name = trim((string) ($attributes['name'] ?? ''));

        if ($name === '') {
            $name = trim((string) ($attributes['login'] ?? ''));
        }

        if ($name === '') {
            throw new GithubAuthException('GitHub не вернул имя пользователя.');
        }

        $avatar = trim((string) ($attributes['avatar_url'] ?? ''));

        $user = new User();
        $user->github_id = $githubId;
        $user->email = $email;
        $user->name = mb_substr($name, 0, 128);
        $user->password = null;
        $user->city_id = null;
        $user->avatar = $avatar === '' ? null : mb_substr($avatar, 0, 255);
        $user->is_executor = 0;

        if (!$user->save()) {
            throw new GithubAuthException('Не удалось зарегистрировать пользователя через GitHub.');
        }

        return $user;
    }
}
