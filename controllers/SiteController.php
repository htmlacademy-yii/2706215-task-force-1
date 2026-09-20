<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use app\forms\UserLoginForm;
use Sanweb\Taskforce\exception\GithubAuthException;
use Sanweb\Taskforce\services\AuthService;
use Sanweb\Taskforce\services\GithubAuthService;
use yii\authclient\AuthAction;
use yii\authclient\ClientInterface;
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
        private readonly GithubAuthService $githubAuthService,
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
            'auth' => [
                'class' => AuthAction::class,
                'successCallback' => [$this, 'onAuthSuccess'],
                'cancelCallback' => [$this, 'onAuthCancel'],
            ],
        ];
    }

    /**
     * Logs in a user after a successful GitHub OAuth callback.
     */
    public function onAuthSuccess(ClientInterface $client): void
    {
        if (!Yii::$app->user->isGuest) {
            return;
        }

        try {
            $user = $this->githubAuthService->authenticate($client);

            if (!Yii::$app->user->login($user)) {
                throw new GithubAuthException('Не удалось авторизовать пользователя.');
            }
        } catch (GithubAuthException $exception) {
            Yii::warning([
                'message' => $exception->getMessage(),
                'cause' => $exception->getPrevious()?->getMessage(),
            ], __METHOD__);
            Yii::$app->session->setFlash('error', $exception->getMessage());
        }
    }

    /**
     * Handles a user-cancelled GitHub OAuth flow.
     */
    public function onAuthCancel(ClientInterface $client): void
    {
        Yii::$app->session->setFlash(
            'info',
            sprintf('Вход через %s отменён.', $client->getTitle()),
        );
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
            'githubAuthEnabled' => $this->isGithubAuthEnabled(),
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
            'githubAuthEnabled' => $this->isGithubAuthEnabled(),
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

    /**
     * Whether GitHub authentication is configured for the interface.
     */
    private function isGithubAuthEnabled(): bool
    {
        $githubConfig = Yii::$app->params['github'] ?? [];

        return !empty($githubConfig['clientId'])
            && !empty($githubConfig['clientSecret']);
    }
}
