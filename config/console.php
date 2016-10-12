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
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'beanstalk'=>[
            'class' => 'udokmeci\yii2beanstalk\Beanstalk',
            'host'=> "192.168.95.128", // default host
            'port'=>11301, //default port
            'connectTimeout'=> 1,
            'sleep' => false, // or int for usleep after every job 
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
