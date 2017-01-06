<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
return [
    'webSiteUrl' => 'http://hjl.yii.cn/',
    'session' => [
        'class' => 'yii\redis\Session',
        'redis' => [
            'hostname' => '172.21.107.71',
            'port' => 7000,
            'database' => 0,
        ],
    ],
    'redis' => [
        'class' => 'yii\redis\Connection',
        'hostname' => '172.21.107.71',
        'port' => 7000,
        'database' => 0,
    // 'dataTimeout' => 1,
    ],
    'swoole_service_host' => ['ip' => '172.21.107.77'],
];

