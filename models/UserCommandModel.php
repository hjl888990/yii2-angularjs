<?php

namespace app\models;

use yii\base\Model;
use app\models\UserForm;
use app\models\DbModels;
use services\RedisService;

/**
 * CountrySearch represents the model behind the search form about `app\models\Country`.
 */
class UserCommandModel extends Model {
    /*     * *
     * 分页查询
     */

    public function searchUserList($searchParams, $page, $pageSize) {
        $connection = \Yii::$app->db;
        $DbModels = new DbModels();
        $queryParams = array();
        foreach ($searchParams as $k => $v) {
            if (!empty($v) && $k == 'name') {
                $queryParams['equal']['name'] = $v;
            }
            if (!empty($v) && $k == 'sex') {
                $queryParams['equal']['sex'] = $v;
            }
            if (!empty($v) && $k == 'email') {
                $queryParams['llike']['email'] = $v;
            }
            if (!empty($v) && $k == 'startTime') {
                $queryParams['between']['create_time']['startTime'] = $v;
            }
            if (!empty($v) && $k == 'endTime') {
                $queryParams['between']['create_time']['endTime'] = $v;
            }
        }
        $result = $DbModels->queryListByTableName($connection, 'user', array('id', 'name', 'email', 'age', 'phone', 'sex', 'create_time'), $queryParams, $page, $pageSize, 'ORDER BY create_time DESC', 'id');
        return $result;
    }

    /*     * *
     * 详情查询
     */

    public function searchUserDetail($searchParams) {
        $connection = \Yii::$app->db;
        $DbModels = new DbModels();
        $redisService = new RedisService();
        $userRedisExists = $redisService->exists('user_' . $searchParams['name']); //先读redis缓存
        if ($userRedisExists == 1) {
            $userRedisResult = $redisService->hmGet('user_' . $searchParams['name'], array('name', 'email', 'age', 'phone', 'sex', 'create_time'));
            return $userRedisResult;
        } else {//读数据库，并存入redis缓存内
            $result = $DbModels->queryDetailByTableName($connection, 'user', array('name', 'email', 'age', 'phone', 'sex', 'create_time'), $searchParams);
            if (!empty($result)) {
                $redisService = new RedisService();
                $redisService->hmSet('user_' . $result['name'], $result);
            }
            return $result;
        }
    }

    /**     *
     * 创建
     */
    public function createUser($params) {
        $connection = \Yii::$app->db;
        $DbModels = new DbModels();
        $userForm = new UserForm();
        $returnId = true; //返回自增ID
        $userForm->scenario = 'add';
        $userForm->setAttributes($params);
        $transaction = $connection->beginTransaction();
        try {
            if ($userForm->validate()) {//验证输入
                $insertSql = 'insert user (name, password,age,sex,phone,email,create_time) values (:name,:password,:age,:sex,:phone,:email,:create_time);';
                $result = $DbModels->execute($connection, $insertSql, $params, $returnId);
                //返回自增ID
                // $selectIncreaseId = 'SELECT LAST_INSERT_ID();';
                // $id = $DbModels->selectBySql($connection,$selectIncreaseId);
                if ($result) {
                    $transaction->commit();
                    $redisService = new RedisService();
                    unset($params['password']);
                    $redisService->hmSet('user_' . $params['name'], $params); //存入redis缓存内
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

    /*     * *
     * 更新
     */

    public function updateUser($params) {
        $connection = \Yii::$app->db;
        $redisService = new RedisService();
        $userRedisExists = $redisService->exists('user_' . $params['name']); //先删redis缓存
        if ($userRedisExists == 1) {
            $redisService->delete('user_' . $params['name']);
        }

        $DbModels = new DbModels();
        $sql = 'update user set ';
        foreach ($params as $k => $v) {
            if (!empty($v) && $k != 'name') {
                $sql .= $k . '=:' . $k . ',';
            }
        }
        if (substr($sql, 0 - strlen(',')) == ',') {
            $sql = substr($sql, 0, strlen($sql) - strlen(','));
        }
        $sql .= ' where name=:name';
        $result = $DbModels->execute($connection, $sql, $params);
        if ($result === 1) {//存入redis缓存内
            $redisService = new RedisService();
            unset($params['password']);
            $redisService->hmSet('user_' . $params['name'], $params);
        }
        return $result;
    }

    /*     * *
     * 删除
     */

    public function deleteUser($params) {
        $connection = \Yii::$app->db;
        $redisService = new RedisService();
        $userRedisExists = $redisService->exists('user_' . $params['name']); //先删redis缓存
        if ($userRedisExists == 1) {
            $redisService->delete('user_' . $params['name']);
        }

        $DbModels = new DbModels();
        $sql = 'delete from user where ';
        foreach ($params as $k => $v) {
            if (!empty($v)) {
                $sql .= $k . '=:' . $k . ' and';
            }
        }
        if (substr($sql, 0 - strlen('and')) == 'and') {
            $sql = substr($sql, 0, strlen($sql) - strlen('and'));
        }
        $result = $DbModels->execute($connection, $sql, $params);
        return $result;
    }

}
