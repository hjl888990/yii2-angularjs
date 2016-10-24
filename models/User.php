<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\Pagination;
use app\models\entity\UserForm;
use app\models\common\Func;
use services\RedisService;
use app\models\exception\OPException;

/**
 * CountrySearch represents the model behind the search form about `app\models\Country`.
 */
class User extends Model {
    
    /**
     * ִ执行登录
     */
    public function login($params) {
        $isLogin = true;
        $userForm = new UserForm();
        $userForm->scenario = 'login';
        $userForm->setAttributes($params);
        if ($userForm->validate()) {//验证输入
            if(isset($params['password'])){
                $params['password'] = md5($params['password']);
            }
            $result = $this->checkUser($params);
            if (!empty($result)) {
                $result ['timestamp'] = time();
                Func::setSession($result);
                $isLogin = true;
            } else {
                $isLogin = false;
            }
        }else{
            throw new \Exception($userForm->getFirstError());
        }
        return $isLogin;
    }

    /**
     * 分页查询
     * 
     * @param type $searchParams
     * @return type
     */
    public function searchUserList($searchParams, $page, $pageSize) {
        $userModels = UserForm::find()->addSelect('account,name,age,create_time,update_time,email,id,phone,sex');
        if (isset($searchParams['startTime']) && !empty($searchParams['startTime'])) {
            $userModels->andWhere(['>=', 'create_time', $searchParams['startTime']]);
            unset($searchParams['startTime']);
        }
        if (isset($searchParams['endTime']) && !empty($searchParams['endTime'])) {
            $userModels->andWhere(['<=', 'create_time', $searchParams['endTime']]);
            unset($searchParams['endTime']);
        }
        if (isset($searchParams['email']) && !empty($searchParams['email'])) {
            $userModels->andWhere(['llike', 'email', $searchParams['email']]);
            unset($searchParams['email']);
        }
        $data = $userModels->andWhere($searchParams);
        $pages = new Pagination(['totalCount' => $data->count(1), 'page' => ((int) $page - 1), 'pageSize' => (int) $pageSize]);
        $result = $data->offset($pages->offset)->limit($pages->limit)->orderBy('create_time DESC')->all();
        foreach ($result as $k => $v) {
            $result[$k] = $v->attributes;
        }
        $pager['currentPage'] = $page;
        $pager['pageSize'] = $pageSize;
        $pager['totalRecord'] = $pages->totalCount;
        $resultArray['pager'] = $pager;
        $resultArray['data'] = $result;
        return $resultArray;
    }
    /**
     * 分页查询
     * 
     * @param type $searchParams
     * @return type
     */
    public function searchUserListBySql($searchParams, $page, $pageSize) {
        $dataSql = 'select * from users um inner join ( select id from users where 1=1';
        $countSql = 'select count(1) count from users where 1=1';
        foreach ($searchParams as $k => $v) {
            if (!empty($v)) {
                switch ($k) {
                    case 'startTime':
                        $dataSql.=" and create_time >=" . $v;
                        $countSql.=" and create_time >=" . $v;
                        break;
                    case 'endTime':
                        $dataSql.=" and create_time <=" . $v;
                        $countSql.=" and create_time <=" . $v;
                        break;
                    case 'email':
                        $dataSql.=" and email like '" . $v . "%'";
                        $countSql.=" and email like '" . $v . "%'";
                        break;
                    default:
                        $dataSql.=" and ".$k . " = '" . $v."'";
                        $countSql.=" and ".$k . " = '" . $v."'";
                        break;
                }
            }
        }
        $countCommand = UserForm::getDb()->createCommand($countSql);
        $countResult = $countCommand->queryOne();
        if(((int) $page - 1)*(int) $pageSize > $countResult['count']){
            $dataResult = array();
        }else {
            $dataSql.=' ORDER BY `create_time` DESC LIMIT ' . (int) $pageSize . ' OFFSET ' . ((int) $page - 1) * (int) $pageSize . ' ) page on page.id=um.id;';
            $dataCommand = UserForm::getDb()->createCommand($dataSql);
            $dataResult = $dataCommand->queryAll();
        }
        $pager['currentPage'] = $page;
        $pager['pageSize'] = $pageSize;
        $pager['totalRecord'] = $countResult['count'];
        $resultArray['pager'] = $pager;
        $resultArray['data'] = $dataResult;
        return $resultArray;
    }

    /**
     * 详情查询
     */
    public function searchUserDetail($searchParams) {
            $userForm = UserForm::find()->addSelect('account,age,create_time,email,id,name,phone,sex')->andWhere($searchParams)->limit(1)->one();
            $result = '';
            if (!empty($userForm)) {
                $result = $userForm->attributes;
            }else{
                throw new OPException(OPException::ERR_USER_NOT_EXIST);
            }
            return $result;
    }

    /**
     * 创建
     */
    public function createUser($params) {
        $userForm = new UserForm();
        $userForm->scenario = 'add';
        $userForm->setAttributes($params);
        if ($userForm->validate()) {//验证输入
            $result = $userForm->save();
            if (!$result) {
                throw new \Exception('用户创建失败');
            }
            return $result;
        } else {
            throw new \Exception($userForm->getFirstError());
        }
    }

    /**
     * 更新
     */
    public function updateUser($params) {
        $user = UserForm::findOne((int) $params['id']);
        if (empty($user)) {
            throw new OPException(OPException::ERR_USER_NOT_EXIST);
        } else {
            $user->scenario = 'update';
            $user->setAttributes($params);
            if ($user->validate()) {//验证输入
                $result = $user->save();
                if ($result == 1) {
                    return true;
                } else {
                    throw new \Exception('更新用户信息失败');
                }
            } else {
                throw new \Exception($user->getFirstError());
            }
        }
    }

    /**
     * 删除
     */
    public function deleteUser($params) {
        $userForm = UserForm::find()->addSelect('id')->andWhere($params)->one();
        if (!empty($userForm)) {
            $delResult = $userForm->delete();
            if ($delResult == 1) {
                return true;
            } else {
                throw new \Exception('删除用户信息失败');
            }
        } else {
            throw new OPException(OPException::ERR_USER_NOT_EXIST);
        }
    }
    
    /**
     * 清楚所有缓存
     */
    public function clearRedis() {
        $redisService = new RedisService();
        $redisService->deleteByPrefix('user_');
        return true;
    }
    
    /**
     * 登录判断
     * @param type $searchParams
     * @return type
     */
    public function checkUser($searchParams) {
        $userForm = UserForm::find()->addSelect('account,name,age,create_time,email,id,name,phone,sex')->andWhere($searchParams)->one();
        $result = '';
        if (!empty($userForm)) {
            $result = $userForm->attributes;
        }
        return $result;
    }

}
