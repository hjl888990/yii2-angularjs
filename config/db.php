<?php

return [
    'class' => 'yii\db\Connection',
    // 配置主服务器
   // 'dsn' => 'mysql:host=172.21.104.1;dbname=hjl',
    'username' => 'web',
    'password' => '123456',
    'charset' => 'utf8',
   // 'tablePrefix' => 'tbl_',//表前缀
    'enableSchemaCache' => true,
    // Duration of schema cache.
    'schemaCacheDuration' => 3600,
    // Name of the cache component used to store schema information
    'schemaCache' => 'cache',
    'attributes' => [
        // use a smaller connection timeout
        PDO::ATTR_TIMEOUT => 10,
    ],
    'masters' => [
        ['dsn' => 'mysql:host=172.21.104.1;dbname=hjl'],
    ],
    'masterConfig' =>[
        'username' => 'web',   
        'password' => '123456',
        'attributes' => [
            // use a smaller connection timeout
            PDO::ATTR_TIMEOUT => 10,
        ],
    ],
    // 配置从服务器
    'slaveConfig' => [
        'username' => 'web',   
        'password' => '123456',
        'attributes' => [
            // use a smaller connection timeout
            PDO::ATTR_TIMEOUT => 10,
        ],
    ],

    // 配置从服务器组
    'slaves' => [
        ['dsn' => 'mysql:host=172.21.104.1;dbname=hjl_slaves'],
        ['dsn' => 'mysql:host=172.21.104.1;dbname=hjl_slaves2'],
    ],
];
