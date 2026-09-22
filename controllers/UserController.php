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
     *
     * @return array
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
     *
     * @param mixed $id
     * @param mixed $module
     * @param UserRepository $userRepository
     * @param CityRepository $cityRepository
     * @param UserService $userService
     * @param TaskRepository $taskRepository
     * @param array $config
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
     * @param int $id
     *
     * @return string
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
     * @return Response|string
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
