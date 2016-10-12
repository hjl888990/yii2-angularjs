<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\Pagination;
use app\models\UserForm;
use services\RedisService;

/**
 * CountrySearch represents the model behind the search form about `app\models\Country`.
 */
class UserActiveRecordModel extends Model {

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
        if (!UserForm::openRedis) {//不开启缓存
            $userForm = UserForm::find()->addSelect('account,age,create_time,email,id,name,phone,sex')->andWhere($searchParams)->limit(1)->one();
            $result = '';
            if (!empty($userForm)) {
                $result = $userForm->attributes;
            }else{
                Yii::error('获取用户信息失败,用户信息为空'. json_encode($searchParams), 'user');
            }
            return $result;
        } else {//开启缓存
            $redisService = new RedisService();
            $userRedisExists = $redisService->exists('user_' . $searchParams['id']); //先读redis缓存
            if ($userRedisExists == 1) {
                $userRedisResult = $redisService->hmGet('user_' . $searchParams['id'], array('account','name','id', 'email', 'age', 'phone', 'sex', 'create_time'));
                $userRedisResult['age'] = (int)$userRedisResult['age'];
                $userRedisResult['sex'] = (int)$userRedisResult['sex'];
                return $userRedisResult;
            } else {//读数据库，并存入redis缓存内
                $userForm = UserForm::find()->addSelect('account,age,create_time,email,id,name,phone,sex')->andWhere($searchParams)->limit(1)->one();
                $result = array();
                if (empty($userForm)) {
                    return $result;
                } else {
                    $result = $userForm->attributes;
                    if ($userRedisExists !== false) {
                        $redisService = new RedisService();
                        
                        $redisService->hmSet('user_' . $result['id'], $result); //存入redis缓存内
                        $redisService->setTimeOut('user_' . $result['id'], UserForm::openRedisTimeOut); //有效期10天
                    }
                    return $result;
                }
            }
        }
    }

    /**
     * 创建
     */
    public function createUser($params) {
        $userForm = new UserForm();
        $connection = $userForm->getDb();
        $userForm->scenario = 'add';
        $userForm->setAttributes($params);
        $transaction = $connection->beginTransaction();
        try {
            if ($userForm->validate()) {//验证输入
                $result = $userForm->save();
                if ($result) {
                    $transaction->commit();
                    if (UserForm::openRedis) {//开启缓存
                        $id = $userForm->attributes['id'];
                        $redisService = new RedisService();
                        $params['id'] = $id;
                        $params['password'] = null;
                        $redisService->hmSet('user_' . $id, $params); //存入redis缓存内
                        $redisService->setTimeOut('user_' . $id,UserForm::openRedisTimeOut);//有效期10天
                    }
                } else {
                    $transaction->rollBack();
                }
                return $result;
            } else {
                // 验证失败：$errors 是一个包含错误信息的数组
                $errors = $userForm->errors;
                return $errors;
            }
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            $transaction->rollBack();
        }
    }

    /**
     * 更新
     */
    public function updateUser($params) {
        $userForm = new UserForm();
        $redisService = new RedisService();
        $connection = $userForm->getDb();
        if (isset($params['id'])) {
            $user = UserForm::findOne((int) $params['id']);
            if (empty($user)) {
                return false;
            } else {
                $user->scenario = 'update';
                $user->setAttributes($params);
                if ($user->validate()) {//验证输入
                    if (UserForm::openRedis) {//开启缓存
                        $userRedisExists = $redisService->exists('user_' . $params['id']); //检测redis缓存
                        if ($userRedisExists == 1) {
                            $redisService->delete('user_' . $params['id']); //删除
                        }
                    }
                    $transaction = $connection->beginTransaction();
                    $result = $user->save();
                    if ($result == 1) {
                        $transaction->commit();
                        if (UserForm::openRedis) {//开启缓存
                            $redisService->hmSet('user_' . $params['id'], $params); //存入redis缓存内
                            $redisService->setTimeOut('user_' . $params['id'],UserForm::openRedisTimeOut);//有效期10天
                        }
                        return true;
                    } else {
                        $transaction->rollBack();
                    }
                } else {
                    // 验证失败：$errors 是一个包含错误信息的数组
                    $errors = $user->errors;
                    return $errors;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * 删除
     */
    public function deleteUser($params) {
        if (UserForm::openRedis) {//开启缓存
            $redisService = new RedisService();
            $userRedisExists = $redisService->exists('user_' . $params['id']); //先读redis缓存
            if ($userRedisExists == 1) {
                $redisService->delete('user_' . $params['id']);
            }
        }
        $userForm = UserForm::find()->addSelect('id')->andWhere($params)->one();
        if (!empty($userForm)) {
            $delResult = $userForm->delete();
            if ($delResult == 1) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
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
