<?php

namespace app\controllers;

use app\models\knowledge\KnowledgeConfig;
use app\models\knowledge\KnowledgeCategoryModel;
use yii\web\Controller;

/**
 * CountryController implements the CRUD actions for Country model.
 */
class KnowledgeCategoryController extends Controller {

    private $_pageSzie = 15;
    public $enableCsrfValidation = false;

    /**
     * 获取知识库配置信息
     */
    public function actionAjaxKnowledgeFormData() {
        $knowledgeStatus = empty(KnowledgeConfig::$knowledgeStatus) ? null : KnowledgeConfig::$knowledgeStatus; //知识库所有状态配置
        $knowledgeStoreIds = empty(KnowledgeConfig::$knowledgeStoreIds) ? null : KnowledgeConfig::$knowledgeStoreIds; //知识库所有来源配置
        $knowledgeTypes = empty(KnowledgeConfig::$knowledgeTypes) ? null : KnowledgeConfig::$knowledgeTypes; //知识库所有类型配置

        $data = array(
            'knowledgeStatus' => $knowledgeStatus,
            'knowledgeStoreIds' => $knowledgeStoreIds,
            'knowledgeTypes' => $knowledgeTypes,
        );
        $this->retJSON(1, $data, $errMsg = '');
    }


    /**
     * 查询分类
     */
    public function actionAjaxKnowledgeCat()
    {
        $kcatModel = new KnowledgeCategoryModel();
        $searchParams = array();
        if(isset($_GET['storeid']) && !empty($_GET['storeid'])){
            $searchParams['storeid'] = (int)$_GET['storeid'];
        }else{
            $this->retJSON(0,$_GET, $errMsg = '查询分类失败：storeid为空.');
        }
        if(isset($_GET['parentid'])){
            $searchParams['parentid'] = (int)$_GET['parentid'];
        }else{
            $this->retJSON(0,$_GET, $errMsg = '查询分类失败：parentid为空.');
        }
        $dataProvider = $kcatModel->searchKnowledgeCat($searchParams);
        $this->retJSON(1, $dataProvider,$errMsg = '');
    }
    
    /**
     * 添加分类
     */
    public function actionAjaxCreateCat() {
        $kcatModel = new KnowledgeCategoryModel();
        $cat = \Yii::$app->request->post('catCreateForm');
        $cat['create_time'] = date('Y-m-d H:i:s', time());
        $cat['modify_time'] = date('Y-m-d H:i:s', time());
        $result = $kcatModel->createKnowledgeCat($cat);
        if (is_array($result)) {
            foreach ($result as $k => $v) {
                if (!empty($v) && is_array($v)) {
                    $result[$k] = $v[0];
                }
            }
            $this->retJSON(0, $result, $errMsg = '添加分类失败！');
        } else {
            if ($result) {
                $this->retJSON(1, $result, $errMsg = '');
            } else {
                $this->retJSON(-1, $result, $errMsg = '添加分类失败！');
            }
        }
    }
    
    /**
     * 修改分类
     */
    public function actionAjaxUpdateCat() {
        $kcatModel = new KnowledgeCategoryModel();
        $cat = \Yii::$app->request->post('catUpdateForm');
        $cat['modify_time'] = date('Y-m-d H:i:s', time());
        $result = $kcatModel->updateKnowledgeCat($cat);
        if (is_array($result)) {
            foreach ($result as $k => $v) {
                if (!empty($v) && is_array($v)) {
                    $result[$k] = $v[0];
                }
            }
            $this->retJSON(0, $result, $errMsg = '修改分类失败！');
        } else {
            if ($result) {
                $this->retJSON(1, $result, $errMsg = '');
            } else {
                $this->retJSON(-1, $result, $errMsg = '修改分类失败！');
            }
        }
    }

}
