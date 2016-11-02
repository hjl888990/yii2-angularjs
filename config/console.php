<?php

Yii::setAlias('@services', dirname(__DIR__) . '/services');
Yii::setAlias('@components', dirname(__DIR__) . '/components');

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logVars' => ['_GET', '_POST', '_FILES'],
                    'logFile' => '@app/runtime/logs/error.log',
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'logVars' => ['_GET', '_POST', '_FILES'],
                    'logFile' => '@app/runtime/logs/access.log',
                ],
            ],
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '172.21.107.71',
            'port' => 7000,
            'database' => 0,
           // 'dataTimeout' => 1,
        ],
        'db' => $db,
    ],
    'params' => $params,
    
    'controllerMap' => [
        'worker'=>[
            'class' => 'app\commands\WorkerController',
        ]
 
    ],
    
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
