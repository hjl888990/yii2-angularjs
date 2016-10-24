<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\User;
use app\models\Filters;
use app\models\factoryCode;
use app\models\exception\OPException;
use app\models\common\Response;

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
            $request = Filters::filter(json_encode($request)); 
            $request = json_decode($request,true);
            //验证码验证
            $flag = factoryCode::validate(trim($request['verifyCode']), true);
            if (!$flag) {
                throw new OPException(OPException::ERR_USER_LOGIN_VERIFYCODE_ERROR);
            }
            $params = array('account' => $request['account'], 'password' => $request['password']);
            
            //登录
            $model = new User();
            if ($result = $model->login($params)) {
                Response::outputSuccess($result);
            } else {
                throw new OPException(OPException::ERR_USER_LOGIN_ERROR);
            }
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage());
            Response::outputFailed($exc->getCode(), $exc->getMessage());
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
            Response::outputFailed($exc->getCode(), $exc->getMessage());
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
            Yii::error($exc->getMessage());
            Response::outputFailed($exc->getCode(), $exc->getMessage());
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
        } catch (OPException $exc) {
            
            Yii::error($exc->getMessage());
            OPResponse::outputFailed($exc->getCode(), $exc->getMessage());
        }
    }
}
