<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\services;

use app\components\AvatarUrlResolver;
use app\models\ExecutorProfile;
use app\models\ExecutorSpecialization;
use app\models\User;
use Sanweb\Taskforce\dto\AccountProfileDto;
use Sanweb\Taskforce\dto\AccountSecurityDto;
use Sanweb\Taskforce\enum\StorageArea;
use Sanweb\Taskforce\exception\AccountSettingsException;
use Throwable;
use Yii;
use yii\web\UploadedFile;

/**
 * Coordinates transactional profile and security settings updates.
 */
final class AccountSettingsService
{
    /**
     * Creates the account settings service.
     *
     * @param FileStorage $fileStorage
     * @param AvatarUrlResolver $avatarUrlResolver
     */
    public function __construct(
        private readonly FileStorage $fileStorage,
        private readonly AvatarUrlResolver $avatarUrlResolver,
    ) {}

    /**
     * Updates profile data, avatar, and executor specializations.
     *
     * @param User $user
     * @param AccountProfileDto $dto
     * @param ?UploadedFile $avatarFile
     *
     * @return void
     *
     * @throws AccountSettingsException
     */
    public function updateProfile(
        User $user,
        AccountProfileDto $dto,
        ?UploadedFile $avatarFile,
    ): void {
        $oldAvatar = $user->avatar;
        $newAvatar = null;

        try {
            if ($avatarFile !== null) {
                $newAvatar = $this->fileStorage->store(
                    $avatarFile,
                    StorageArea::UserAvatars,
                    (string) $user->id,
                )->filePath;
            }

            $this->saveProfileInTransaction($user, $dto, $newAvatar);
        } catch (Throwable $exception) {
            if ($newAvatar !== null) {
                $this->removeAvatarSafely($newAvatar);
                $user->avatar = $oldAvatar;
            }

            Yii::error($exception, __METHOD__);
            throw new AccountSettingsException(
                'Не удалось сохранить настройки профиля.',
                0,
                $exception,
            );
        }

        if (
            $newAvatar !== null
            && is_string($oldAvatar)
            && $this->avatarUrlResolver->isLocalKey($oldAvatar)
        ) {
            $this->removeAvatarSafely($oldAvatar);
        }
    }

    /**
     * Saves the user and executor-specific profile data atomically.
     *
     * @param User $user
     * @param AccountProfileDto $dto
     * @param ?string $newAvatar
     *
     * @return void
     *
     * @throws AccountSettingsException
     */
    private function saveProfileInTransaction(
        User $user,
        AccountProfileDto $dto,
        ?string $newAvatar,
    ): void {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $user->name = $dto->name;
            $user->email = $dto->email;
            $user->birthday = $dto->birthday;

            if ($newAvatar !== null) {
                $user->avatar = $newAvatar;
            }

            if (!$user->save(false)) {
                throw new AccountSettingsException('Не удалось сохранить пользователя.');
            }

            if ((bool) $user->is_executor) {
                $this->updateExecutorProfile($user, $dto);
                $this->syncSpecializations($user->id, $dto->categoryIds);
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
     * Updates the password and contact visibility.
     *
     * @param User $user
     * @param AccountSecurityDto $dto
     *
     * @return void
     *
     * @throws AccountSettingsException
     */
    public function updateSecurity(User $user, AccountSecurityDto $dto): void
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            if ($dto->newPassword !== null) {
                $user->setPassword($dto->newPassword);

                if (!$user->save(false)) {
                    throw new AccountSettingsException('Не удалось сохранить пароль.');
                }
            }

            if ((bool) $user->is_executor) {
                $profile = $user->executorProfile ?? new ExecutorProfile();
                $profile->user_id = $user->id;
                $profile->hide_my_contacts = (int) $dto->hideMyContacts;

                if (!$profile->save(false)) {
                    throw new AccountSettingsException(
                        'Не удалось сохранить видимость контактов.',
                    );
                }

                $user->populateRelation('executorProfile', $profile);
            }

            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollBack();
            Yii::error($exception, __METHOD__);
            throw new AccountSettingsException(
                'Не удалось сохранить настройки безопасности.',
                0,
                $exception,
            );
        }
    }

    /**
     * Creates or updates executor-specific profile fields.
     *
     * @param User $user
     * @param AccountProfileDto $dto
     *
     * @return void
     *
     * @throws AccountSettingsException
     */
    private function updateExecutorProfile(User $user, AccountProfileDto $dto): void
    {
        $profile = $user->executorProfile ?? new ExecutorProfile();
        $profile->user_id = $user->id;
        $profile->phone = $dto->phone;
        $profile->telegram = $dto->telegram;
        $profile->about = $dto->about;

        if (!$profile->save(false)) {
            throw new AccountSettingsException(
                'Не удалось сохранить профиль исполнителя.',
            );
        }

        $user->populateRelation('executorProfile', $profile);
    }

    /**
     * Synchronizes executor specializations with selected categories.
     *
     * @param int $userId
     * @param list<int> $categoryIds
     *
     * @return void
     *
     * @throws AccountSettingsException
     */
    private function syncSpecializations(int $userId, array $categoryIds): void
    {
        $currentIds = ExecutorSpecialization::find()
            ->select('category_id')
            ->where(['user_id' => $userId])
            ->column();
        $currentIds = array_map('intval', $currentIds);

        $toDelete = array_diff($currentIds, $categoryIds);
        $toAdd = array_diff($categoryIds, $currentIds);

        if ($toDelete !== []) {
            ExecutorSpecialization::deleteAll([
                'user_id' => $userId,
                'category_id' => $toDelete,
            ]);
        }

        foreach ($toAdd as $categoryId) {
            $specialization = new ExecutorSpecialization();
            $specialization->user_id = $userId;
            $specialization->category_id = $categoryId;

            if (!$specialization->save(false)) {
                throw new AccountSettingsException(
                    'Не удалось сохранить специализации.',
                );
            }
        }
    }

    /**
     * Removes a local avatar without masking the completed settings update.
     *
     * @param string $avatar
     *
     * @return void
     */
    private function removeAvatarSafely(string $avatar): void
    {
        try {
            $this->fileStorage->remove(StorageArea::UserAvatars, $avatar);
        } catch (Throwable $exception) {
            Yii::error($exception, __METHOD__);
        }
    }
}
