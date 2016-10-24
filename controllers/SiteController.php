<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\common\Response;

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
    
    public function behaviors() {
        return [
            'access' => [
                'class' => 'app\models\filters\AccessFilter',
                'except' => [''],
            ],
        ];
    }

    public function actionIndex() {
        return $this->render('index');
    }

    

    public function actionAjaxLeftMenu() {
        $session = Yii::$app->session;
        $session->open();
        $name = $session->get('name');

        $Menu = $this->_cnMenu;
        $retunMenu = array();
        foreach ($Menu as $k => $v) {
            $v = $v[0];
            $v['count'] = count($v['list']);
            $v['user'] = array('userName'=>$name);
            $retunMenu[] = $v;
        }
        Response::outputSuccess($retunMenu);
    }
}
