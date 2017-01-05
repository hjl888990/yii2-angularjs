<?php
$env = require(__DIR__ . '/env.php');
return [
    'sessionName' => 'hjlYII',
    'emailDoRedisListKey' => 'email_list_do',
    'emailReDoRedisListKey' => 'email_list_redo',
    'emailDoRedisDetailKey' => 'email_detail_do',
    'swoole_service_host' => $env['swoole_service_host'],
    'webSiteUrl' => $env['webSiteUrl'],
];
