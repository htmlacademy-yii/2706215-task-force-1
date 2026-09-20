<?php

declare(strict_types=1);

namespace app\controllers;

use app\components\AvatarUrlResolver;
use app\forms\ProfileSettingsForm;
use app\forms\SecuritySettingsForm;
use app\models\User;
use Sanweb\Taskforce\exception\AccountSettingsException;
use Sanweb\Taskforce\repositories\CategoryRepository;
use Sanweb\Taskforce\repositories\UserRepository;
use Sanweb\Taskforce\services\AccountSettingsService;
use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * Handles settings for the authenticated user's account.
 */
class AccountController extends AuthorizedController
{
    private const SECTION_PROFILE = 'profile';
    private const SECTION_SECURITY = 'security';

    /**
     * {@inheritdoc}
     */
    public function __construct(
        mixed $id,
        mixed $module,
        private readonly UserRepository $userRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly AccountSettingsService $accountSettingsService,
        private readonly AvatarUrlResolver $avatarUrlResolver,
        array $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'settings' => ['get'],
                'update-profile' => ['post'],
                'update-security' => ['post'],
            ],
        ];

        return $behaviors;
    }

    /**
     * Displays settings for the authenticated user.
     *
     * @throws NotFoundHttpException
     */
    public function actionSettings(): string
    {
        $user = $this->findCurrentUser();
        [$profileForm, $securityForm] = $this->createSettingsForms($user);

        return $this->renderSettings($user, $profileForm, $securityForm);
    }

    /**
     * Updates profile settings.
     *
     * @throws NotFoundHttpException
     */
    public function actionUpdateProfile(): Response|string
    {
        $user = $this->findCurrentUser();
        [$profileForm, $securityForm] = $this->createSettingsForms($user);

        if (!$profileForm->load($this->request->post())) {
            return $this->renderSettings(
                $user,
                $profileForm,
                $securityForm,
                self::SECTION_PROFILE,
            );
        }

        $profileForm->avatarFile = UploadedFile::getInstance($profileForm, 'avatarFile');

        if (!$profileForm->validate()) {
            return $this->renderSettings(
                $user,
                $profileForm,
                $securityForm,
                self::SECTION_PROFILE,
            );
        }

        try {
            $this->accountSettingsService->updateProfile(
                $user,
                $profileForm->toDto(),
                $profileForm->avatarFile,
            );
        } catch (AccountSettingsException) {
            Yii::$app->session->setFlash('error', 'Не удалось сохранить настройки. Попробуйте ещё раз.');

            return $this->renderSettings(
                $user,
                $profileForm,
                $securityForm,
                self::SECTION_PROFILE,
            );
        }

        Yii::$app->session->setFlash('success', 'Настройки успешно сохранены.');

        if ((bool) $user->is_executor) {
            return $this->redirect(['user/view', 'id' => $user->id]);
        }

        [$profileForm, $securityForm] = $this->createSettingsForms($user);

        return $this->renderSettings($user, $profileForm, $securityForm);
    }

    /**
     * Updates security settings.
     *
     * @throws NotFoundHttpException
     */
    public function actionUpdateSecurity(): Response|string
    {
        $user = $this->findCurrentUser();
        [$profileForm, $securityForm] = $this->createSettingsForms($user);

        if (
            !$securityForm->load($this->request->post())
            || !$securityForm->validate()
        ) {
            return $this->renderSettings(
                $user,
                $profileForm,
                $securityForm,
                self::SECTION_SECURITY,
            );
        }

        try {
            $this->accountSettingsService->updateSecurity(
                $user,
                $securityForm->toDto(),
            );
        } catch (AccountSettingsException) {
            Yii::$app->session->setFlash(
                'error',
                'Не удалось сохранить настройки. Попробуйте ещё раз.',
            );

            return $this->renderSettings(
                $user,
                $profileForm,
                $securityForm,
                self::SECTION_SECURITY,
            );
        }

        Yii::$app->session->setFlash('success', 'Настройки успешно сохранены.');

        if ((bool) $user->is_executor) {
            return $this->redirect(['user/view', 'id' => $user->id]);
        }

        [$profileForm, $securityForm] = $this->createSettingsForms($user);

        return $this->renderSettings(
            $user,
            $profileForm,
            $securityForm,
            self::SECTION_SECURITY,
        );
    }

    /**
     * Returns the authenticated user with settings relations.
     *
     * @throws NotFoundHttpException
     */
    private function findCurrentUser(): User
    {
        $identity = $this->getCurrentUser();
        $user = $this->userRepository->findForSettings($identity->id);

        if ($user === null) {
            throw new NotFoundHttpException('Пользователь не найден.');
        }

        return $user;
    }

    /**
     * Creates profile and security forms for the settings page.
     *
     * @return array{ProfileSettingsForm, SecuritySettingsForm}
     */
    private function createSettingsForms(User $user): array
    {
        return [
            new ProfileSettingsForm($user),
            new SecuritySettingsForm($user),
        ];
    }

    /**
     * Renders the settings page.
     */
    private function renderSettings(
        User $user,
        ProfileSettingsForm $profileForm,
        SecuritySettingsForm $securityForm,
        string $activeSection = self::SECTION_PROFILE,
    ): string {
        return $this->render('settings', [
            'profileForm' => $profileForm,
            'securityForm' => $securityForm,
            'categories' => $user->is_executor
                ? $this->categoryRepository->findAllForSelect()
                : [],
            'avatarUrl' => $this->avatarUrlResolver->resolve($user->avatar),
            'activeSection' => $activeSection,
        ]);
    }
}
