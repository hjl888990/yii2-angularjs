<?php
define('ROOT_PATH', dirname(__FILE__));
include (ROOT_PATH . '/function.php');

// 定义常量
define("PRIVDATA_DIR", '/www/privdata/cron_demo');
define('CRON_STATUS_DIR', PRIVDATA_DIR . '/cron_status');
define('CONFIG_DIR', PRIVDATA_DIR . '/config');
define('CRON_SWITCH_FILE', CONFIG_DIR . '/cron_switch.ini');


