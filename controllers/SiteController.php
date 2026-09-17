<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use app\forms\UserLoginForm;
use app\services\AuthService;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ErrorAction;
use yii\web\Response;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function __construct(
        mixed $id,
        mixed $module,
        private readonly AuthService $authService,
        array $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'error' => [
                'class' => ErrorAction::class,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return Response|string
     */
    public function actionIndex(): Response|string
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['task/index']);
        }

        $this->layout = 'landing';
        return $this->render('index', [
            'loginForm' => new UserLoginForm(),
            'showLoginModal' => false,
        ]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin(): Response|string
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $loginForm = new UserLoginForm();

        if ($loginForm->load($this->request->post()) && $loginForm->validate()) {
            $user = $this->authService->authenticate($loginForm->toDto());

            if ($user !== null && Yii::$app->user->login($user)) {
                return $this->goBack();
            }

            $loginForm->addError('password', 'Неверный email или пароль.');
        }

        $loginForm->password = '';
        $this->layout = 'landing';

        return $this->render('index', [
            'loginForm' => $loginForm,
            'showLoginModal' => true,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout(): Response
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
