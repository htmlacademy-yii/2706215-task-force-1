<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'language' => 'ru-RU',
    'bootstrap' => ['log'],
    'container' => [
        'singletons' => [
            \Sanweb\Taskforce\services\FileStorage::class => [
                'class' => \Sanweb\Taskforce\services\FileStorage::class,
                '__construct()' => [$params['fileStorage']['roots']],
            ],
            \Sanweb\Taskforce\services\http\HttpClientInterface::class => \Sanweb\Taskforce\services\http\GuzzleHttpClient::class,
            \Sanweb\Taskforce\services\geocoding\GeocoderInterface::class => [
                'class' => \Sanweb\Taskforce\services\geocoding\YandexGeocoder::class,
                '__construct()' => [
                    \yii\di\Instance::of(\Sanweb\Taskforce\services\http\HttpClientInterface::class),
                    $params['yandex']['geocoderApiKey'],
                ],
            ],
        ],
    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // A unique cookie validation secret must be provided for each environment.
            'cookieValidationKey' => $_ENV['COOKIE_VALIDATION_KEY'] ?? '',
        ],
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
        'user' => [
            'identityClass' => \app\models\User::class,
            'enableAutoLogin' => true,
        ],
        'authClientCollection' => [
            'class' => \yii\authclient\Collection::class,
            'clients' => [
                'github' => [
                    'class' => \yii\authclient\clients\GitHub::class,
                    'clientId' => $params['github']['clientId'],
                    'clientSecret' => $params['github']['clientSecret'],
                    'scope' => 'user:email',
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'site/index',
                'task/view/<id:\d+>' => 'task/view',
                'task/file/<id:\d+>' => 'task/download',
                'task/create' => 'task/create',
                'tasks' => 'task/index',
                'my-tasks' => 'my-task/index',
                'settings' => 'account/settings',
                'settings/profile' => 'account/update-profile',
                'settings/security' => 'account/update-security',
                'user/view/<id:\d+>' => 'user/view',
                'signup' => 'user/signup',
                'login' => 'site/login',
            ],
        ],
        'formatter' => [
            'class' => \app\components\AppFormatter::class,
            'locale' => 'ru-RU',
            'currencyCode' => 'RUB',
            'numberFormatterOptions' => [
                \NumberFormatter::MIN_FRACTION_DIGITS => 0,
                \NumberFormatter::MAX_FRACTION_DIGITS => 0,
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \yii\debug\Module::class,
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => \yii\gii\Module::class,
    ];
}

return $config;
