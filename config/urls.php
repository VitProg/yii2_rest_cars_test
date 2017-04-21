<?php

return [
    'scriptUrl' => '',
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'baseUrl' => file_exists(__DIR__ . '/host.local.php') ? require __DIR__ . '/host.local.php' : 'http://car.rest',
    'rules' => [
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'api/car',
//                    'only' => ['index', 'view', 'create', 'delete', 'update'],
        ],
        'api/cars/years' => 'api/car/years',
    ],
];