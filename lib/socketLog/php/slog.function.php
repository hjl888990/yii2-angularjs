<?php

require 'slog.php';

use app\lib\socketLog\php\Slog;

function slog($log, $type = 'log', $css = '', $rtime = 0) {
    try {
        if (is_string($type)) {
            $type = preg_replace_callback('/_([a-zA-Z])/', create_function('$matches', 'return strtoupper($matches[1]);'), $type);
            if (method_exists('app\lib\socketLog\php\Slog', $type) || in_array($type, Slog::$log_types)) {
                return call_user_func(array('app\lib\socketLog\php\Slog', $type), $log, $css);
            }
        }

        if (is_object($type) && 'mysqli' == get_class($type)) {
            return Slog::mysqlilog($log, $type);
        }

        if (is_resource($type) && ('mysql link' == get_resource_type($type) || 'mysql link persistent' == get_resource_type($type))) {
            return Slog::mysqllog($log, $type);
        }


        if (is_object($type) && 'PDO' == get_class($type)) {
            return Slog::pdolog($log, $type, $rtime);
        }

        throw new \Exception($type . ' is not SocketLog method');
    } catch (\Exception $exc) {
        echo $exc->getMessage();
        die();
    }
}
