<?php

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
        'urlManager' => [
            //这个baseUrl 最终也会决定homeUrl的去处相当于给当前应用指定一个域名然后真个应用的                                路由都基于这个域名跳转
            'baseUrl' => 'http://hjl.yii.cn/',
            'class' => 'yii\web\UrlManager',
            'enablePrettyUrl' => false,
            'showScriptName' => false,
            //'suffix' => 'path',
        ],
        'redis' => $params['redis'],
        'session' =>  $params['session'],
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
                    'levels' => ['error', 'warning'],
                    'except'=>['shell_*'],
                    'logVars' => ['_GET', '_POST', '_FILES'],
                    'logFile' => '@app/runtime/logs/error.log',
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'except'=>['yii\db*','shell_*'],
                    'logVars' => ['_GET', '_POST', '_FILES'],
                    'logFile' => '@app/runtime/logs/access.log',
                ]
                
            ],
        ],
        'db' => $db,
    ],
    'params' => $params,
];

if (YII_ENV_DEV && YII_DEBUG) {
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
