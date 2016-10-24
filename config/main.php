<?php

Yii::setAlias('@services', dirname(__DIR__) . '/services');
Yii::setAlias('@components', dirname(__DIR__) . '/components');

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    //'controllerNamespace' => 'app\controllers',
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'AhYlml6MTZ8n2Lhk1IehuxlOPU-dgSiq',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
            'directoryLevel' => 2,
        ],
        'user' => [
            'identityClass' => 'app\models\User1',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'urlManager' => [
            //这个baseUrl 最终也会决定homeUrl的去处相当于给当前应用指定一个域名然后真个应用的                                路由都基于这个域名跳转
            'baseUrl' => 'http://hjl.yii.cn/',
            'class' => 'yii\web\UrlManager',
            'enablePrettyUrl' => false,
            'showScriptName' => false,
            //'suffix' => 'path',
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '172.21.107.71',
            'port' => 7000,
            'database' => 0,
           // 'dataTimeout' => 1,
        ],
        'session' => [
            'class' => 'yii\redis\Session',
            'redis' => [
                'hostname' => '172.21.107.71',
                'port' => 7000,
                'database' => 0,
            ]
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
        'db' => $db,
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs'=>array('172.21.104.25','172.21.104.1')
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}
return $config;
