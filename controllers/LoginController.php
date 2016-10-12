<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use components\factoryCode;
use app\models\UserActiveRecordModel;
use components\Func;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * CountryController implements the CRUD actions for Country model.
 */
class LoginController extends Controller
{
    public $enableCsrfValidation = false;
    
    public function actionAjaxLogin() {
        if(!isset($_POST['verifyCode']) || empty($_POST['verifyCode'])){
            $this->retJSON(0, null,'验证码不能为空.');
        }
        if(!isset($_POST['account']) || empty($_POST['account'])){
            $this->retJSON(0, null,'账号不能为空.');
        }
        if(!isset($_POST['password']) || empty($_POST['password'])){
            $this->retJSON(0, null,'密码不能为空.');
        }
        
        //验证码验证
        $flag = factoryCode::validate(trim($_POST['verifyCode']),true);        
        if(!$flag){
            $this->retJSON(0, null,'验证码错误.');
        }
        $LoginForm = array('account'=>$_POST['account'],'password'=>$_POST['password']);
        if ($this->loadLogin($LoginForm)) {
            $this->retJSON(1, array(), '');
        } else {
            $this->retJSON(0, null, '账号不存在或密码错误.');
        }
    }
    
    /**
     * ִ执行登录
     */
    private function loadLogin($dataList) {
        $isLogin = true;
        $userModel = new UserActiveRecordModel();
        $result = $userModel->checkUser($dataList);
        if (!empty($result)) {
            $dataateList = array();
            $dataateList ['account'] = $result ['account'];
            $dataateList ['name'] = $result ['name'];
            $dataateList ['token'] = $result ['account'].time();
            Func::setSession(array('loginUser:' . $dataateList ['account']=>$dataateList ['token']));
            Func::setCookiesUserInfo($dataateList);
            $isLogin = true;
        } else {
            $isLogin = false;
        }
        return $isLogin;
    }

    /**
     * 退出登录
     * @return type
     */
    public function actionAjaxLogout() {
        Yii::$app->getSession()->destroy();
        $this->retJSON(1, array(), '');
    }

    /**
     * 验证用户登录状态
     */
    public function actionAjaxCheckLogin() {
        $userInfo = $this->checkValidate();
        if ($userInfo) {
            $this->retJSON(1, array(), '');
        } else {
            $this->retJSON(0, null, '');
        }
    }
    
     /**
     * 
     * 生成验证码
     */
    public function actionAjaxVeryfy() {
        $captcha = factoryCode::createObj(1);
        $captcha->doimg();
        Yii::$app->end();
    }
}
