<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\services;

use Sanweb\Taskforce\dto\UserLoginDto;
use app\models\User;
use Sanweb\Taskforce\repositories\UserRepository;
use yii\base\Security;

final class AuthService
{
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
