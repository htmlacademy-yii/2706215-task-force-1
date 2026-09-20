<?php

declare(strict_types=1);

namespace app\widgets;

use app\components\AvatarUrlResolver;
use app\models\User;
use Yii;
use yii\base\Widget;

final class HeaderWidget extends Widget
{
    public function run(): string
    {
        $identity = Yii::$app->user->identity;
        $user = $identity instanceof User ? $identity : null;

        return $this->render('header', [
            'isGuest' => Yii::$app->user->isGuest,
            'canCreateTask' => $user !== null && !(bool) $user->is_executor,
            'route' => Yii::$app->controller->route,
            'userName' => $user !== null ? $user->name : '',
            'avatarUrl' => (new AvatarUrlResolver())->resolve(
                $user !== null ? $user->avatar : null,
            ),
        ]);
    }
}
