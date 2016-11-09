<?php

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');
define('WEBSITENAME', 'hjlYII');

require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/vendor/yiisoft/yii2/Yii.php');

if (YII_DEBUG) {
    require(__DIR__ . '/lib/socketLog/php/slog.function.php');
    if (function_exists('slog')) {
        slog(array(
            'enable' => true, //是否打印日志的开关
            'host' => '172.21.107.77', //websocket服务器地址，默认localhost
            'port' => '1229', //websocket服务器端口，默认端口是1229    
            'optimize' => true, //是否显示利于优化的参数，如运行时间，消耗内存等，默认为false
            'show_included_files' => false, //是否显示本次程序运行加载了哪些文件，默认为false
            'error_handler' => true, //是否接管程序错误，将程序错误显示在console中，默认为false
            'force_client_id' => 'dragon', //日志强制记录到配置的client_id,默认为空
            'allow_client_ids' => array('dragon')////限制允许读取日志的client_id，默认为空,表示所有人都可以获得日志。
                )
                , 'config');
    }
}



$config = require(__DIR__ . '/config/main.php');

$WebApplicationnew= new yii\web\Application($config);
$WebApplicationnew->run();
