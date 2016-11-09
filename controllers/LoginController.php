<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\User;
use app\models\Filters;
use app\models\factoryCode;
use app\models\exception\OPException;
use app\models\common\Response;
use app\models\common\Encryption;
use yii\web\Session;

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
    
    const prefix = 'hjlYII__';
    
//    public function behaviors(){
//        return [
//            'access' =>[
//                'class' => 'app\models\filters\ParamsFilter',
//                'except' => ['ajax-veryfy'],
//            ],
//        ];
//    }
    
    
    public function actionAjaxLogin() {
        try {
            //参数过滤
            $request = Yii::$app->getRequest()->post();
            $request = Filters::filter(json_encode($request,JSON_UNESCAPED_UNICODE)); 
            $request = json_decode($request,true);
            //验证码验证
            $flag = factoryCode::validate(trim($request['verifyCode']), true);
            if (!$flag) {
                throw new OPException(OPException::ERR_USER_LOGIN_VERIFYCODE_ERROR);
            }
            $params = array('account' => $request['account'], 'password' => $request['password']);
            
            //登录
            $model = new User();
            $result = $model->login($params);
            if ($result) {
                $token = Encryption::encrypt(self::prefix.session_id(), 'E');//加密后的sessionid，用于联合登录
                Response::outputSuccess($token);
            } else {
                throw new OPException(OPException::ERR_USER_LOGIN_ERROR);
            }
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage());
            $response = new Response($exc->getCode(), $exc->getMessage());
            $response->outputFailed();
        }
    }
    

    /**
     * 退出登录
     * @return type
     */
    public function actionAjaxLogout() {
        try {
            $session = Yii::$app->session;
            $session->open();
            $session->destroy();
            Response::outputSuccess([]);
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage());
            $response = new Response($exc->getCode(), $exc->getMessage());
            $response->outputFailed();
        }
        
    }
    
    /**
     * 检测登录状态
     * @return type
     */
    public function actionAjaxCheckLogin() {
        try {
            $session = Yii::$app->session;
            $session->open();
            $account = $session->get('account');
            if (!empty($account)) {
                Response::outputSuccess([]);
            } else {
                throw new OPException(OPException::ERR_NOLOGIN);
            }
        } catch (\Exception $exc) {
            $response = new Response($exc->getCode(), $exc->getMessage());
            $response->outputFailed();
        }
    }

    /**
     * 
     * 生成验证码
     */
    public function actionAjaxVeryfy() {
        try {
            $captcha = factoryCode::createObj(1);
            $captcha->doimg();
            Yii::$app->end();
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage());
            $response = new Response($exc->getCode(), $exc->getMessage());
            $response->outputFailed();
        }
    }
    
    /**
     * 
     * 联合登录
     */
    public function actionSsoLogin() {
        try {
            $token = Yii::$app->getRequest()->get('token');
            $dString = Encryption::encrypt($token, 'D'); //解密sessionid
            if(strpos($dString,self::prefix) === 0){
                $dString = str_replace(self::prefix,'',$dString);
            }else{
                throw new \Exception('Parameter error');
            }
            
            $cookies = Yii::$app->response->cookies;
            $sessionName = Yii::$app->params['sessionName'];
            if (!empty($sessionName)) {
                $name = $sessionName;
            } else {
                $name = session_name();
            }
            $sessionModel = new Session();
            $data = $sessionModel->getCookieParams();
            extract($data);
            if (isset($lifetime, $path, $domain, $secure, $httponly)) {
                setcookie($name ,$dString, $lifetime, $path, $domain, $secure, $httponly);
            }
//            $cookies->add(new \yii\web\Cookie([
//                'name' => ,
//                'value' => ,
//                'httpOnly' => true
//            ]));
            Yii::$app->end();
        } catch (\Exception $exc) {
            $response = new Response($exc->getCode(), $exc->getMessage());
            $response->outputFailed();
        }
    }
}
