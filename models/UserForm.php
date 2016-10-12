<?php

namespace app\models;

/**
 * This is the model class for table "user".
 *
 * @property string $code
 * @property string $name
 * @property integer $population
 */
class UserForm extends \yii\db\ActiveRecord {
    
    const openRedis = true;//是否开启redis缓存
    const openRedisTimeOut = 864000;//redis缓存有效期

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
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }
    public function getAccount() {
        return $this->account;
    }

    public function setAccount($account) {
        $this->account = $account;
    }

        

    public function getName() {
        return $this->name;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getAge() {
        return $this->age;
    }

    public function getSex() {
        return $this->sex;
    }

    public function getPhone() {
        return $this->phone;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getCreate_time() {
        return $this->create_time;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function setAge($age) {
        $this->age = $age;
    }

    public function setSex($sex) {
        $this->sex = $sex;
    }

    public function setPhone($phone) {
        $this->phone = $phone;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setCreate_time($create_time) {
        $this->create_time = $create_time;
    }
    
    public function getUpdate_time() {
        return $this->update_time;
    }

    public function setUpdate_time($update_time) {
        $this->update_time = $update_time;
    }

    
    /**
     * @inheritdoc
     */
    public function rules() {
        return array(
            array(['account','name','password', 'age', 'sex', 'phone', 'email','create_time'], 'required', 'on' => ['add']), //add场景应用
            array(['account','name','password', 'age', 'sex', 'phone', 'email','update_time'], 'required', 'on' => ['update']), //update场景应用
            array(['account', 'password'], 'required', 'on' => ['login']), //login场景应用
            array('account', 'unique', 'message' => '账号已占用!'),
            array('email', 'unique', 'message' => '邮箱已占在!'),
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
