<?php

return [
    'class' => 'yii\db\Connection',
    // 配置主服务器
    'dsn' => 'mysql:host=172.21.107.208;dbname=hjl',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
   // 'tablePrefix' => 'tbl_',//表前缀

//    // 配置从服务器
//    'slaveConfig' => [
//        'username' => 'root',   
//        'password' => '',
//        'attributes' => [
//            // use a smaller connection timeout
//            PDO::ATTR_TIMEOUT => 10,
//        ],
//    ],
//
//    // 配置从服务器组
//    'slaves' => [
//        ['dsn' => 'mysql:host=172.21.107.247;dbname=hjltest'],
//    ],
];
