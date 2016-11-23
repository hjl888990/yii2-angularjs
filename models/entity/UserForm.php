<?php

namespace app\models\entity;

/**
 * 
 * This is the model class for table "user".
 * 
 */
class UserForm extends \yii\db\ActiveRecord {

    const openRedis = true; //是否开启redis缓存
    const changePwdSendEmail = true; //修改密码是否发送邮件
    const openRedisTimeOut = 864000; //redis缓存有效期
    const userCreateSetCacheKeys = 'user_create_do';
    const userCreateSetCacheKeysRedo = 'user_create_redo';
    const userCreateHashCacheDetail = 'user_create_detail';
    const userCreateHashCacheLock = 'user_create_lock';
    
    private $id; //自增主键
    private $account; //账号
    private $name; //用户名
    private $password; //密码
    private $confirm_password; //密码
    private $age; //年龄
    private $sex; //性别
    private $phone; //电话
    private $email; //邮箱
    private $create_time; //创建时间
    private $update_time; //更新时间


    public function getAgeType() {
        return 'int';
    }

    public function getSexType() {
        return 'int';
    }
    
    public function getConfirm_password() {
        return $this->confirm_password;
    }

    public function setConfirm_password($confirm_password) {
        $this->confirm_password = $confirm_password;
    }

    
    

    public static function tableName() {
        return 'users';
    }

    public static function getDb() {
        return \Yii::$app->db;  // 使用名为 "db" 的应用组件
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return array(
            array(['account', 'name', 'password', 'confirm_password', 'age', 'sex', 'phone', 'email'], 'required', 'on' => ['add']), //add场景应用
            array(['account', 'name', 'age', 'sex', 'phone', 'email'], 'required', 'on' => ['update']), //update场景应用
            array(['password', 'confirm_password'], 'required', 'on' => ['changePwd']), //update场景应用
            array(['account', 'password'], 'required', 'on' => ['login']), //login场景应用
            array('account', 'unique', 'message' => '账号已占用!', 'on' => ['add', 'update']),
            array('email', 'unique', 'message' => '邮箱已占在!', 'on' => ['add', 'update']),
            array('account', 'string', 'max' => 10, 'min' => 6, 'tooLong' => '账号长度为6-10位字符', 'tooShort' => '账号长度为6-10位字符!'),
            array('name', 'string', 'max' => 10, 'min' => 1, 'tooLong' => '用户名长度为1-10位字符', 'tooShort' => '用户名长度为1-10位字符!'),
            array('password', 'string', 'max' => 20, 'min' => 6, 'tooLong' => '密码长度为6-22位字符', 'tooShort' => '密码长度为6-22位字符!', 'on' => ['add', 'changePwd']),
            array('confirm_password', 'compare', 'compareAttribute' => 'password', 'message' => '请再输入确认密码', 'on' => ['add', 'changePwd']),
            array('email', 'email', 'message' => '邮箱格式错误!'),
            array('phone', 'match', 'pattern' => '/^1[34578]\d{9}$/', 'message' => '手机格式错误!'),
            array('sex', 'in', 'range' => array(1, 2), 'message' => '性别格式错误!'),
            array('age', 'integer', 'min' => 1, 'max' => 200, 'integerOnly' => true, 'tooBig' => '年龄格式错误!', 'tooSmall' => '年龄格式错误!'),
        );
    }

    public function beforeSave($insert = true) {
        if (parent::beforeSave($insert)) {
            if ($this->getIsNewRecord()) {
                $pass = md5($this->getAttribute('password'));
                $params = ['password' => $pass, 'create_time' => time() * 1000];
                $this->setAttributes($params);
                // $this->create_user_id = Yii::app()->user->id;  
            } else {
                $params = ['update_time' => time() * 1000];
                $this->setAttributes($params);
                // $this->update_user_id = Yii::app()->user->id;  
            }
            return true;
        } else {
            return false;
        }
    }

    public function beforeChangePwd() {
        $password = $this->getAttribute('password');
        if (!empty($password)) {
            $pass = md5($this->getAttribute('password'));
            $params ['password'] = $pass;
        }
        $this->setAttributes($params);
    }

    public function scenarios() {
        return [
            'add' => ['account', 'name', 'password', 'confirm_password', 'age', 'sex', 'phone', 'email', 'create_time'],
            'update' => ['account', 'name', 'password', 'age', 'sex', 'phone', 'email', 'update_time'],
            'changePwd' => ['password', 'confirm_password', 'update_time'],
            'login' => ['account', 'password'],
        ];
    }

}
