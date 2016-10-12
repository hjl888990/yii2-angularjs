<?php

namespace app\models\knowledge;


/**
 * This is the model class for table "user".
 *
 * @property string $code
 * @property string $name
 * @property integer $population
 */
class KnowledgeConfig {
    //知识库来源集合
    static $knowledgeStoreIds = array(
        array(
            'id'=>1,
            'tag'=>'CN',
            'name_ch'=>'客服知识库',
            'name_en'=>'Agent Knowledge',
        ),
        array(
            'id'=>2,
            'tag'=>'EN',
            'name_ch'=>'网点知识库',
            'name_en'=>'Service Knowledge',
        ),
    );
    
    /**
     * 所有状态
     * @var array
     */
    static $knowledgeStatus = array(
        array(
            'id'=>1,
            'name_ch'=>'未提交',
            'name_en'=>'Draft',
        ),
        array(
            'id'=>2,
            'name_ch'=>'已发布',
            'name_en'=>'Published',
        ),
        array(
            'id'=>3,
            'name_ch'=>'已提交',
            'name_en'=>'Submitted',
        ),
        array(
            'id'=>4,
            'name_ch'=>'已拒绝',
            'name_en'=>'Refused',
        ),
        array(
            'id'=>5,
            'name_ch'=>'已删除',
            'name_en'=>'Disabled',
        ),
    );
    
    /**
     * 所有类型
     * @var array
     */
    static $knowledgeTypes = array(
        array(
            'id'=>1,
            'name_ch'=>'活动信息',
            'name_en'=>'Campaign',
        ),
        array(
            'id'=>2,
            'name_ch'=>'产品知识',
            'name_en'=>'Product',
        ),
        array(
            'id'=>3,
            'name_ch'=>'处理流程',
            'name_en'=>'Process',
        ),
        array(
            'id'=>4,
            'name_ch'=>'故障信息',
            'name_en'=>'Malfunction',
        ),
        array(
            'id'=>5,
            'name_ch'=>'培训材料',
            'name_en'=>'Training Materials',
        ),
    );

}
