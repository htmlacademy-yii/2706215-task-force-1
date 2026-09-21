<?php

declare(strict_types=1);

namespace app\controllers;

use app\forms\UserSignupForm;
use Sanweb\Taskforce\exception\UserSignupException;
use Sanweb\Taskforce\repositories\CityRepository;
use Sanweb\Taskforce\repositories\TaskRepository;
use Sanweb\Taskforce\repositories\UserRepository;
use Sanweb\Taskforce\services\UserService;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class UserController extends AuthorizedController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $rules = [
            [
                'allow' => true,
                'actions' => ['signup'],
                'roles' => ['?'],
            ],
        ];

        $behaviors['access']['rules'] = array_merge(
            $rules,
            $behaviors['access']['rules'],
        );

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function __construct(
        mixed $id,
        mixed $module,
        private readonly UserRepository $userRepository,
        private readonly CityRepository $cityRepository,
        private readonly UserService $userService,
        private readonly TaskRepository $taskRepository,
        array $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * Displays a single User model.
     *
     * @throws NotFoundHttpException
     */
    public function actionView(int $id): string
    {
        $user = $this->userRepository->findExecutorById($id);

        if ($user === null) {
            throw new NotFoundHttpException('Исполнитель не найден.');
        }

        $canViewContacts = !$user->executorProfile?->hide_my_contacts;

        if (!$canViewContacts && !Yii::$app->user->isGuest) {
            $canViewContacts = $this->taskRepository->hasActiveTaskWithExecutor(
                (int) Yii::$app->user->id,
                $user->id
            );
        }

        return $this->render('view', [
            'user' => $user,
            'canViewContacts' => $canViewContacts,
        ]);
    }

    /**
     * Registers a new user.
     *
     * @throws UserSignupException
     */
    public function actionSignup(): Response|string
    {
        $signupForm = new UserSignupForm();

        if ($signupForm->load($this->request->post()) && $signupForm->validate()) {
            $user = $this->userService->signup($signupForm->toDto());

            Yii::$app->user->login($user);

            return $this->goHome();
        }

        return $this->render('signup', [
            'model' => $signupForm,
            'cities' => $this->cityRepository->findAllForSelect(),
        ]);
    }
}
