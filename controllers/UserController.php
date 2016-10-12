<?php

namespace app\controllers;

use app\models\UserActiveRecordModel;
use yii\web\Controller;

/**
 * CountryController implements the CRUD actions for Country model.
 */
class UserController extends Controller
{
    
    private $_pageSzie = 15;
    public $enableCsrfValidation = false;

    /**
     * Lists all Country models.
     * @return mixed
     */
    public function actionSearch()
    {
        $userModel = new UserActiveRecordModel();
        $searchParams = array();
        if(isset($_GET['account']) && !empty($_GET['account'])){
            $searchParams['account'] = $_GET['account'];
        }
        if(isset($_GET['name']) && !empty($_GET['name'])){
            $searchParams['name'] = $_GET['name'];
        }
        if(isset($_GET['email']) && !empty($_GET['email'])){
            $searchParams['email'] = $_GET['email'];
        }
        if(isset($_GET['sex']) && !empty($_GET['sex'])){
            $searchParams['sex'] = $_GET['sex'];
        }
        if(isset($_GET['startTime']) && !empty($_GET['startTime'])){
            $searchParams['startTime'] = strtotime($_GET['startTime'])*1000;
        }
        if(isset($_GET['endTime']) && !empty($_GET['endTime'])){
            $searchParams['endTime'] =  strtotime($_GET['endTime'])*1000+86399000;
        }
        $currentPage = !empty($_GET['currentPage'])?(int)$_GET['currentPage']:1;
        $pageSize = !empty($_GET['pageSize'])?(int)$_GET['pageSize']:$this->_pageSzie;
        $dataProvider = $userModel->searchUserListBySql($searchParams,$currentPage,$pageSize);
        $this->retJSON(1, $dataProvider,$errMsg = '');
    }

    /**
     * Creates a new Country model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $userModel = new UserActiveRecordModel();
        $user = \Yii::$app->request->post('user');
        // 所有输入数据都有效
        $user['create_time'] = time()*1000;
        $result = $userModel->createUser($user);
        if (is_array($result)) {
            foreach ($result as $k => $v) {
                if (!empty($v) && is_array($v)) {
                    $result[$k] = $v[0];
                }
            }
            $this->retJSON(0, $result, $errMsg = '创建用户失败！');
        } else {
            if ($result) {
                $this->retJSON(1, $result, $errMsg = '');
            } else {
                $this->retJSON(-1, $result, $errMsg = '创建用户失败！');
            }
        }
    }

    /**
     * Updates an existing Country model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionDetail()
    {
        $userModel = new UserActiveRecordModel();
        $searchParams = array();
        if(isset($_GET['id']) && !empty($_GET['id'])){
            $searchParams['id'] = (int)$_GET['id'];
        }else{
          $this->retJSON(0, array(),$errMsg = 'id is null!');  
        }
        $result = $userModel->searchUserDetail($searchParams);
        if(!empty($result)){
            $this->retJSON(1, $result,$errMsg = '');
        }else{
            $this->retJSON(0, $result,$errMsg = '获取用户信息失败！');
        }
        
    }
    /**
     * Updates an existing Country model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate()
    {
        $userModel = new UserActiveRecordModel();
        $user = \Yii::$app->request->post('user');
        // 所有输入数据都有效
        $user['update_time'] = time()*1000;
        $result = $userModel->updateUser($user);
        if (is_array($result)) {
            foreach ($result as $k => $v) {
                if (!empty($v) && is_array($v)) {
                    $result[$k] = $v[0];
                }
            }
            $this->retJSON(0, $result, $errMsg = '更新用户失败！');
        } else {
            if ($result) {
                $this->retJSON(1, $result, $errMsg = '');
            } else {
                $this->retJSON(-1, $result, $errMsg = '更新用户失败！');
            }
        }
    }

    /**
     * Deletes an existing Country model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete()
    {
        $userModel = new UserActiveRecordModel();
        $searchParams = array();
        if(isset($_GET['id']) && !empty($_GET['id'])){
            $searchParams['id'] = (int)$_GET['id'];
        }else{
          $this->retJSON(0, array(),$errMsg = 'id is null!');  
        }
        $result = $userModel->deleteUser($searchParams);
        if($result){
            $this->retJSON(1, $result, $errMsg = '删除用户成功！');
        }else{
            $this->retJSON(0, $result, $errMsg = '删除用户失败！');
        }
    }
    
    /**
     * 清楚缓存
     */
    public function actionClearRedis() {
        $userModel = new UserActiveRecordModel();
        $result = $userModel->clearRedis();
        if($result){
            $this->retJSON(1, array(),'清楚缓存成功！');
        }else{
            $this->retJSON(0, array(),'清楚缓存失败！');
        }
        
    }
}
