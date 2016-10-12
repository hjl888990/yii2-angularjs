<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;

class SiteController extends Controller {

    private $_cnMenu = array(
        100 => array(
            array(
                'itemname' => '用户管理',
                'icon' => 'glyphicon glyphicon-th',
                'list' => array(
                    101 => array('name' => '用户列表管理', 'url' => 'app.user.userList'),
                )
            )
        ),
        200 => array(
            array(
                'itemname' => '知识库管理',
                'icon' => 'glyphicon glyphicon-th',
                'list' => array(
                    201 => array('name' => '分类管理', 'url' => 'app.knowledge.knowledgeCategoryList'),
                )
            )
        ),
    );
    
        /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex() {
        return $this->render('index');
    }

    

    public function actionAjaxLeftMenu() {
        $Menu = $this->_cnMenu;
        $retunMenu = array();
        foreach ($Menu as $k => $v) {
            $v = $v[0];
            $v['count'] = count($v['list']);
            $v['user'] = array('userName'=>$this->user['name']);
            $retunMenu[] = $v;
        }
        $this->retJSON(1, $retunMenu, $errMsg = '');
    }
}
