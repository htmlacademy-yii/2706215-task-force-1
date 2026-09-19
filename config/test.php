<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/test_db.php';

/**
 * Application configuration shared by all test types
 */
return [
    'id' => 'basic-tests',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [
        \app\tests\Support\MailerBootstrap::class,
    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'language' => 'en-US',
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
                    'test-api-key',
                ],
            ],
        ],
    ],
    'components' => [
        'db' => $db,
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'messageClass' => \yii\symfonymailer\Message::class,
            'useFileTransport' => true,
            'viewPath' => '@app/mail',
        ],
        'assetManager' => [
            'basePath' => __DIR__ . '/../web/assets',
        ],
        'urlManager' => [
            'showScriptName' => true,
        ],
        'user' => [
            'identityClass' => \app\models\User::class,
        ],
        'authClientCollection' => [
            'class' => \yii\authclient\Collection::class,
            'clients' => [
                'github' => [
                    'class' => \yii\authclient\clients\GitHub::class,
                    'clientId' => 'test-client-id',
                    'clientSecret' => 'test-client-secret',
                    'scope' => 'user:email',
                ],
            ],
        ],
        'request' => [
            'cookieValidationKey' => 'test',
            'enableCsrfValidation' => false,
            // but if you absolutely need it set cookie domain to localhost
            /*
            'csrfCookie' => [
                'domain' => 'localhost',
            ],
            */
        ],
    ],
    'params' => $params,
];
