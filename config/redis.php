<?php

/** @noinspection PhpIncludeInspection */
return file_exists(__DIR__ . '/redis.local.php') ? require __DIR__ . '/redis.local.php' : [
    'class' => 'yii\redis\Connection',
    'hostname' => 'localhost',
    'port' => 6379,
    'database' => 2,
//    'username' => 'root',
//    'password' => '',
];
