<?php

declare(strict_types=1);

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;
use yii\web\YiiAsset;

/**
 * Assets used only on the task details page.
 */
final class TaskViewAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $js = [
        'js/main.js',
    ];
    public $jsOptions = [
        'position' => View::POS_END,
    ];
    public $depends = [
        YiiAsset::class,
    ];
}
