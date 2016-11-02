<?php

namespace app\models\entity;

/**
 * 
 * This is the model class for table "user".
 * 
 */
class UserForm extends \yii\db\ActiveRecord {
    
    const openRedis = true;//是否开启redis缓存
    const openRedisTimeOut = 864000;//redis缓存有效期
    
    const userCreateSetCacheKeys = 'user_create_do';
    const userCreateSetCacheKeysRedo = 'user_create_redo';
    const userCreateHashCacheDetail = 'user_create_detail';
    const userCreateHashCacheLock = 'user_create_lock';

    private $id; //自增主键
    private $account; //账号
    private $name; //用户名
    private $password; //密码
    private $age; //年龄
    private $sex; //性别
    private $phone; //电话
    private $email; //邮箱
    private $create_time; //创建时间
    private $update_time; //更新时间

    public static function tableName() {
        return 'users';
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
            array(['account','name','password', 'age', 'sex', 'phone', 'email'], 'required', 'on' => ['add']), //add场景应用
            array(['account','name','password', 'age', 'sex', 'phone', 'email'], 'required', 'on' => ['update']), //update场景应用
            array(['account', 'password'], 'required', 'on' => ['login']), //login场景应用
            array('account', 'unique', 'message' => '账号已占用!','on' => ['add','update']),
            array('email', 'unique', 'message' => '邮箱已占在!','on' => ['add','update']),
            array('account', 'string', 'max' => 10, 'min' => 6, 'tooLong' => '账号长度为6-10位字符', 'tooShort' => '账号长度为6-10位字符!'),
            array('name', 'string', 'max' => 10, 'min' => 1, 'tooLong' => '用户名长度为1-10位字符', 'tooShort' => '用户名长度为1-10位字符!'),
            array('password', 'string', 'max' => 20, 'min' => 6, 'tooLong' => '密码长度为6-22位字符', 'tooShort' => '密码长度为6-22位字符!'),
            array('email', 'email', 'message' => '邮箱格式错误!'),
            array('phone', 'match', 'pattern' => '/^1[34578]\d{9}$/', 'message' => '手机格式错误!'),
            array('sex', 'in', 'range' => array(1, 2), 'message' => '性别格式错误!'),
            array('age', 'integer', 'min' => 1, 'max' => 200, 'integerOnly' => true, 'tooBig' => '年龄格式错误!', 'tooSmall' => '年龄格式错误!'),
            array('id', 'safe'),
        );
    }
    
    public function beforeSave()  
    {  
        if(parent::beforeSave(true)){  
            if($this->getIsNewRecord()){ 
                $pass = md5($this->getAttribute('password'));
                $params = ['password'=>$pass,'create_time'=>time() * 1000];
                $this->setAttributes($params);
                // $this->create_user_id = Yii::app()->user->id;  
            }else{
                $pass = md5($this->getAttribute('password'));
                $params = ['password'=>$pass,'update_time'=>time() * 1000];
                $this->setAttributes($params);
               // $this->update_user_id = Yii::app()->user->id;  
            }  
            return true;  
        }else{  
            return false;  
        }  
    }  

    
    
   public function getUsers()
   {
      return $this->hasMany(UserForm::className(), array('id' => 'id'));
   }

    public function scenarios() {
        return [
            'add' => ['account','name', 'password', 'age', 'sex', 'phone', 'email', 'create_time'],
            'update' => ['account','name','password', 'age', 'sex', 'phone', 'email','update_time'],
            'login'=>['account', 'password'],
        ];
    }
}
