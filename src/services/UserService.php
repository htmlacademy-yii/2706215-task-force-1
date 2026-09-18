<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\services;

use Sanweb\Taskforce\dto\UserSignupDto;
use app\models\ExecutorProfile;
use app\models\User;
use Sanweb\Taskforce\exception\UserSignupException;
use Yii;

final class UserService
{
    /**
     * @throws UserSignupException
     */
    public function signup(UserSignupDto $dto): User
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $user = new User();
            $user->name = $dto->name;
            $user->email = $dto->email;
            $user->city_id = $dto->cityId;
            $user->is_executor = (int) $dto->isExecutor;

            $user->setPassword($dto->password);

            if (!$user->save()) {
                throw new UserSignupException('Не удалось создать пользователя.');
            }

            if ($dto->isExecutor) {
                $profile = new ExecutorProfile();
                $profile->user_id = $user->id;

                if (!$profile->save()) {
                    throw new UserSignupException('Не удалось создать профиль исполнителя.');
                }
            }

            $transaction->commit();

            return $user;
        } catch (\Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }
    }
}
