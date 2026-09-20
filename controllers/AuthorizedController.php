<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

abstract class AuthorizedController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'denyCallback' => function () {
                    return Yii::$app->response->redirect(['site/index']);
                },
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Returns the authenticated application user.
     *
     * @throws ForbiddenHttpException
     */
    protected function getCurrentUser(): User
    {
        $user = Yii::$app->user->identity;

        if (!$user instanceof User) {
            throw new ForbiddenHttpException('Требуется авторизация.');
        }

        return $user;
    }
}
