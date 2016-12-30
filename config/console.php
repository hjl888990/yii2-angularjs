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
                    'levels' => ['error','info'],
                    'categories'=>['shell_*'],
                    'logVars' => ['_GET', '_POST', '_FILES'],
                    'logFile' => '@app/runtime/logs/shell.log',
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info','error'],
                    'categories'=>['email'],
                    'logVars' => ['_GET', '_POST', '_FILES'],
                    'logFile' => '@app/runtime/logs/email.log',
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info','error'],
                    'categories'=>['swoole_service'],
                    'logVars' => ['_GET', '_POST', '_FILES'],
                    'logFile' => '@app/runtime/logs/swoole_service.log',
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'except'=>['shell_*','email','swoole_service'],
                    'logVars' => ['_GET', '_POST', '_FILES'],
                    'logFile' => '@app/runtime/logs/error.log',
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'except'=>['shell_*','email','swoole_service'],
                    'logVars' => ['_GET', '_POST', '_FILES'],
                    'logFile' => '@app/runtime/logs/access.log',
                ]
                
            ],
        ],
        //发邮件
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.163.com', //每种邮箱的host配置不一样
                'username' => '13627009379@163.com',//邮件后台开启 POP3/SMTP服务
                'password' => 'hjl888990',//授权码
                'port' => '25',
                'encryption' => 'tls',
            ],
            'messageConfig' => [
                'charset' => 'UTF-8',
                'from' => ['13627009379@163.com' => 'admin']
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
