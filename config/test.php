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
            \app\services\FileStorage::class => [
                'class' => \app\services\FileStorage::class,
                '__construct()' => [$params['fileStorage']['roots']],
            ],
            \app\services\http\HttpClientInterface::class => \app\services\http\GuzzleHttpClient::class,
            \app\services\geocoding\GeocoderInterface::class => [
                'class' => \app\services\geocoding\YandexGeocoder::class,
                '__construct()' => [
                    \yii\di\Instance::of(\app\services\http\HttpClientInterface::class),
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
