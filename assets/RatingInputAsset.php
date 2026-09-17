<?php

declare(strict_types=1);

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

final class RatingInputAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $js = [
        'js/rating-input.js',
    ];
    public $jsOptions = [
        'position' => View::POS_END,
    ];
}
