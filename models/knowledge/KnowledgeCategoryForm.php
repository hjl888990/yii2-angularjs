<?php

namespace app\models\knowledge;

/**
 * This is the model class for table "user".
 *
 * @property string $code
 * @property string $name
 * @property integer $population
 */
class KnowledgeCategoryForm extends \yii\db\ActiveRecord {
    
    const openRedis = false;//是否开启redis缓存
    const openRedisTimeOut = 864000;//redis缓存有效期

    private $id; //自增主键
    private $parentid; //父级ID
    private $orderindex = 0; //排序权重
    private $title; //分类名称
    private $level; //分类等级
    private $status = 1; //状态
    private $storeid; //所属库
    private $create_time; //创建时间
    private $modify_time; //更新时间

    public static function tableName() {
        return 'kl_category';
    }
    public static function getDb()
    {
        return \Yii::$app->db;  // 使用名为 "db" 的应用组件
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return array(
            array(['title','level', 'storeid', 'create_time','modify_time'], 'required', 'on' => ['add']), //add场景应用
            array(['id','title','modify_time'], 'required', 'on' => ['update']), //update场景应用
            array('level', 'in', 'range' => array(1,2,3,4), 'message' => '分类等级格式错误!'),
            array('status', 'in', 'range' => array(0,1), 'message' => '分类状态格式错误!'),
            array('id', 'integer',  'message' => '分类ID格式错误!'),
            array('storeid', 'integer',  'message' => '分类所属库ID格式错误!'),
            array('orderindex', 'integer',  'message' => '分类排序权重格式错误!'),
            array('parentid', 'integer',  'message' => '分类父级分类ID格式错误!'),
            array('id', 'safe'),
        );
    }

    public function scenarios() {
        return [
            'add' => ['parentid', 'title','orderindex', 'level', 'status', 'storeid', 'create_time','modify_time'],
            'update' => ['parentid', 'title','orderindex', 'level', 'status', 'storeid', 'create_time','modify_time'],
        ];
    }

}
