<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'minimal',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'N9QvVaGmW7UPGcYe_UFtKjCV6auvJzyx',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'redis' => require(__DIR__ . '/redis.php'),
        'urlManager' => require(__DIR__ . '/urls.php'),
    ],
    'modules' => [
        'api' => [
            'class' => 'app\modules\api\Module'
        ]
    ],
    'params' => $params,
];

return $config;
