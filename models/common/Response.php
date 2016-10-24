<?php

namespace app\models\common;

use app\models\exception\OPException;

class Response extends \yii\base\Object
{
    /**
     * 成功的错误码
     */
    const ERR_CODE_SUCCESS = 0;
    /**
     * 错误码
     * @var int
     */
    protected $code;
    /**
     * 错误信息
     * @var string
     */
    protected $errMsg;
    /**
     * 数据
     * @var mixed
     */
    protected $data;

    /**
     * 构造函数
     * @param   int $code   错误码
     * @param   string  $errMsg 错误信息
     * @param   mixed   $data
     */
    public function __construct($code, $errMsg=null, $data=null){
        $this->code = $code;
        if (empty($errMsg) && ($code != self::ERR_CODE_SUCCESS)){
            $errMsg = OPException::getErrMsg($code);
        }

        $this->errMsg = $errMsg;
        $this->data = $data;

        $this->filterSpecialError();
    }

    /**
     * 过滤特别的异常码
     */
    protected function filterSpecialError(){
        if( ($this->code > 1000) || ($this->code == 42) ){
            $this->code = OPException::ERR_SYS_ERROR;
            $this->errMsg = OPException::getErrMsg($this->code);
        }
    }

    /**
     * 设置数据
     */
    public function setData($data){
        $this->data= $data;
        return $this;
    }
    /**
     * 输出
     */
    public function output(){
        $res = array('ret'  =>1, 'errCode' =>   $this->code, 'errMsg'   =>  $this->errMsg, 'data'   =>  $this->data);
        echo json_encode($res);
        exit();
    }

    /**
     * 输出成功的响应
     * @param   mixed   $data   数据
     */
    public static function outputSuccess($data = null){
        $res = array('ret'  =>1, 'errCode' => 0, 'errMsg'   =>  '', 'data'   =>  $data);
        echo json_encode($res);
        exit();
    }

    /**
     * 输出失败的响应
     * @param   mixed   $data   数据
     */
    public static function outputFailed($errCode = 0, $errMsg = 0, $data = null){
        $res = array('ret'  =>0, 'errCode' => $errCode, 'errMsg'=> $errMsg, 'data'=> $data);
        echo json_encode($res);
        exit();
    }

    /**
     * goto accoun to login
     * return to $referer
     * @param  string $referer [description]
     * @return [type]           [description]
     */
    public static function toAccount($referer = '') 
    {
        $accountUrl  = trim(\Yii::$app->params['account_url'], '/');
        $redirectUrl = $accountUrl . '/login?_x='.date('YmdHis');

        if (!empty($referer)) {
            $referer     = '&referer=' . base64_encode(htmlspecialchars($referer));
            $redirectUrl = $redirectUrl . $referer;
        }

        header('Location: ' . $redirectUrl);
    }
}
