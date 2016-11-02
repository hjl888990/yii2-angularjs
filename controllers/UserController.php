<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Filters;
use app\models\User;
use app\models\exception\OPException;
use app\models\common\Response;
use app\services\RedisService;

/**
 * CountryController implements the CRUD actions for Country model.
 */
class UserController extends Controller {

    private $_pageSzie = 15;
    public $enableCsrfValidation = false;
    
    public function behaviors() {
        return [
            'access' => [
                'class' => 'app\models\filters\AccessFilter',
                'except' => ['test'],
            ],
        ];
    }

    /**
     * Lists all Country models.
     * @return mixed
     */
    public function actionSearch() {
        try {
            //参数过滤
            $request = Yii::$app->getRequest()->getParam();
            $request = Filters::filter(json_encode($request,JSON_UNESCAPED_UNICODE));
            $request = json_decode($request, true);

            $userModel = new User();
            $searchParams = array();
            if (isset($request['account']) && !empty($request['account'])) {
                $searchParams['account'] = $request['account'];
            }
            if (isset($request['name']) && !empty($request['name'])) {
                $searchParams['name'] = $request['name'];
            }
            if (isset($request['email']) && !empty($request['email'])) {
                $searchParams['email'] = $request['email'];
            }
            if (isset($request['sex']) && !empty($request['sex'])) {
                $searchParams['sex'] = $request['sex'];
            }
            if (isset($request['startTime']) && !empty($request['startTime'])) {
                $searchParams['startTime'] = strtotime($request['startTime']) * 1000;
            }
            if (isset($request['endTime']) && !empty($request['endTime'])) {
                $searchParams['endTime'] = strtotime($request['endTime']) * 1000 + 86399000;
            }
            $currentPage = !empty($request['currentPage']) ? (int) $request['currentPage'] : 1;
            $pageSize = !empty($request['pageSize']) ? (int) $request['pageSize'] : $this->_pageSzie;
            $dataProvider = $userModel->searchUserListBySql($searchParams, $currentPage, $pageSize);
            Response::outputSuccess($dataProvider);
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage());
            $response = new Response($exc->getCode(), $exc->getMessage());
            $response->outputFailed();
        }
    }

    /**
     * Creates a new Country model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        try {
            $request = Yii::$app->getRequest()->post('user');
            $request = Filters::filter(json_encode($request,JSON_UNESCAPED_UNICODE));
            $user = json_decode($request, true);
            
            $userModel = new User();
            $result = $userModel->createUser($user);
            if ($result) {
                Response::outputSuccess($result);
            } else {
                throw new OPException(OPException::ERR_SYS_ERROR);
            }
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage());
            $response = new Response($exc->getCode(), $exc->getMessage());
            $response->outputFailed();
        }
    }

    /**
     * Updates an existing Country model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionDetail() {
        try {
            $request = Yii::$app->getRequest()->get();
            $userModel = new User();
            $searchParams = array();
            if (isset($request['id']) && !empty($request['id'])) {
                $searchParams['id'] = (int) $request['id'];
            } else {
                throw new OPException(OPException::ERR_SYS_PARAM_ERROR);
            }
            $result = $userModel->searchUserDetail($searchParams);
            if ($result) {
                Response::outputSuccess($result);
            } else {
                throw new OPException(OPException::ERR_SYS_ERROR);
            }
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage());
            $response = new Response($exc->getCode(), $exc->getMessage());
            $response->outputFailed();
        }
    }

    /**
     * Updates an existing Country model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate() {
        try {
            $request = Yii::$app->getRequest()->post('user');
            $request = Filters::filter(json_encode($request,JSON_UNESCAPED_UNICODE));
            $user = json_decode($request, true);
            

            if (!isset($user['id']) || empty($user['id'])) {
                throw new OPException(OPException::ERR_SYS_PARAM_ERROR);
            }
            $userModel = new User();
            $result = $userModel->updateUser($user);
            if ($result) {
                Response::outputSuccess($result);
            } else {
                throw new OPException(OPException::ERR_SYS_ERROR);
            }
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage());
            $response = new Response($exc->getCode(), $exc->getMessage());
            $response->outputFailed();
        }
    }

    /**
     * Deletes an existing Country model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete() {
        try {
            $userModel = new User();
            if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
                throw new OPException(OPException::ERR_SYS_PARAM_ERROR);
            }
            $result = $userModel->deleteUser((int) $_GET['id']);
            if ($result) {
                Response::outputSuccess($result);
            } else {
                throw new OPException(OPException::ERR_SYS_ERROR);
            }
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage());
            $response = new Response($exc->getCode(), $exc->getMessage());
            $response->outputFailed();
        }
    }
    

    public function actionTest() {
        try {
            $r = new RedisService();
            $account = $r->incr('account');
            if($account === false){
                throw new OPException(OPException::ERR_REDIS_CONNECT_ERROR);
            }
            $user = [
                "name"=>"dasda",
                "password"=>"231312",
                "email"=>$account."@qq.com",
                "age"=>"12",
                "sex"=>"1",
                "phone"=> "13627009379",
                "account" =>$account
            ];
            $userModel = new User();
            $result = $userModel->createUser($user);
            if ($result) {
                Response::outputSuccess($result);
            } else {
                throw new OPException(OPException::ERR_SYS_ERROR);
            }
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage());
            $response = new Response($exc->getCode(), $exc->getMessage());
            $response->outputFailed();
        }
    }

    /**
     * 清楚缓存
     */
    public function actionClearRedis() {
        try {
            $userModel = new User();
            $result = $userModel->clearRedis();
            if ($result) {
                Response::outputSuccess($result);
            } else {
                throw new \Exception('清楚缓存失败！');
            }
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage());
            $response = new Response($exc->getCode(), $exc->getMessage());
            $response->outputFailed();
        }
    }

}
