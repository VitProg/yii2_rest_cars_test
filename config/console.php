<?php

$params = require(__DIR__ . '/params.php');

return [
    'id' => 'minimal-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'parser'],
    'controllerNamespace' => 'app\commands',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'flushInterval' => 1,
            'targets' => [
                [
                    'class' => 'pahanini\log\ConsoleTarget',
                    'categories' => ['parser'],
                    'levels' => ['info', 'error', 'warning'],
                    'exportInterval' => 1,
                    'displayDate' => true,
                    'padSize' => 30,
                ],
                [
                   'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'exportInterval' => 1000,
                ],
            ],
        ],
        'db' => require(__DIR__ . '/redis.php'),
        'urlManager' => require(__DIR__ . '/urls.php'),
    ],
    'modules' => [
        'parser' => [
            'class' => 'app\modules\parser\Module',
        ]
    ],
    'params' => $params,
];
