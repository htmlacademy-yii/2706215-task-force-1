<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\services;

use app\models\User;
use Sanweb\Taskforce\dto\UserLoginDto;
use Sanweb\Taskforce\repositories\UserRepository;
use yii\base\Security;

/**
 * Authenticates users with email and password.
 */
final class AuthService
{
    /**
     * Creates the authentication service.
     */
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly Security $security,
    ) {}

    /**
     * Returns the user when the supplied credentials are valid.
     */
    public function authenticate(UserLoginDto $dto): ?User
    {
        $user = $this->userRepository->findByEmail(
            mb_strtolower(trim($dto->email)),
        );

        if (
            $user === null
            || $user->password === null
            || !$this->security->validatePassword($dto->password, $user->password)
        ) {
            return null;
        }

        return $user;
    }
}
