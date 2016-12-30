<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
define('ROOT_PATH', dirname(__FILE__));

include(ROOT_PATH . '../cron_init.php');
$pid_status_log = CRON_STATUS_DIR . '/' . getmypid();
while (true) {
    file_put_contents($pid_status_log, 1);
    $cron_flag = cron_switch('addUser');

    if (!$cron_flag) {
        unlink($pid_status_log);
        exit();
    }

    // 可从队列中取出数据进行处理
    // 这里作为例子，以记录一个日志好了
    echo 1;
}
