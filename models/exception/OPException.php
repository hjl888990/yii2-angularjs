<?php

namespace app\models\exception;

class OPException extends \Exception {

    //1-系统
    //2-基础数据
    //3-购物车
    //4-邀请码
    //5-订单
    //6-库存
    //7-Paypal
    //8-用户
    //系统错误
    const ERR_SYS_PARAM_ERROR = 1;
    const ERR_SYS_ERROR = 2;    //系统内部错误
    const ERR_PARAMS_INJECT = 3;
    const ERR_NOLOGIN = 4;
    const ERR_SYS_AUTH_FAILED = 10; //认证错误
    const ERR_SYS_UNSUPPORT_STORAGE = 20; //不支持的引擎
    const ERR_REDIS_CONNECT_ERROR = 40;     //redis连接错误
    const ERR_REDIS_INVALID_KEY_ERROR = 50;  //无效的Redis键值
    
    //用户错误
    const ERR_USER_LOGIN = 100;
    const ERR_USER_LOGIN_ERROR = 101;
    const ERR_USER_LOGIN_VERIFYCODE_ERROR = 102;
    const ERR_USER_NOT_EXIST = 103;

    static protected $errMsgMap = [
        1 => 'Parameter error',
        2 => 'System error',
        3 => 'Parameter inject error',
        4 => '请先登录系统',
        10 => 'Auth failed',
        20 => 'Unsupported storage',
        40 => 'Redis Server connection failed.',
        50 => 'Invalid key value.',
        100 => '账号未登录',
        101 => '账号不存在或密码错误',
        102 => '验证码错误',
        103 => '用户不存在',
    ];
    public $data;

    /**
     * 构造函数
     * @param type $code
     * @param type $msg
     */
    public function __construct($code, $params = array(), $msg = '', $data = []) {
        if (empty($msg)) {
            $message = self::getErrMsg($code, $params);
        } else {
            $message = $msg;
        }

        $this->data = $data;

        parent::__construct($message, $code, null);
    }

    /**
     * 获取错误码
     * @param int $code 错误码
     * @param   mixed  $params    错误信息的参数
     * @return string
     */
    public static function getErrMsg($code, $params = array()) {
        $message = 'unknown error';
        if (isset(self::$errMsgMap[$code])) {
            $message = self::$errMsgMap[$code];
            if (!empty($params)) {
                $message = vsprintf($message, $params);
            }
        }
        return $message;
    }

    /**
     * 获取异常消息
     * @var Exception $ex
     */
    public static function getExceptionMsg($ex) {
        $msg = $ex->getMessage() . "\r\n\r\n";
        $trace = $ex->getTrace();
        $count = count($trace);
        if ($count > 4) {
            $count = 4;
        }

        for ($i = 0; $i < $count; $i++) {
            if (isset($trace[$i]['file']) && isset($trace[$i]['line'])) {
                $msg .= $trace[$i]['file'] . '#' . $trace[$i]['line'] . "\r\n";
            }
        }
        return $msg;
    }

}
