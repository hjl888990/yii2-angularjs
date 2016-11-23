<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\models\entity\UserForm;
use app\services\RedisService;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UserController extends Controller {

    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     */
    public function actionAddUsers() {
        try {
            $r = new RedisService();
            $isExist = $r->exists(UserForm::userCreateSetCacheKeys);
            if (!$isExist) {
                ;
            } else {
                $nums = $r->getScard(UserForm::userCreateSetCacheKeys);
                if ($nums > 0) {
                    $accounts = $r->getSrandmember(UserForm::userCreateSetCacheKeys, 50); //一次取50条
                    if (is_array($accounts)) {
                        $accountDetails = $r->hmGet(UserForm::userCreateHashCacheDetail, $accounts);

                        $accountArr = [];
                        foreach ($accounts as $key => $value) {
                            if (array_key_exists($value, $accountDetails)) {
                                $accountDetail = json_decode($accountDetails[$value], true);
                                unset($accountDetail['id']);
                                $accountArr[] = array_values($accountDetail);
                            } else {
                                $result = $r->setAdd(UserForm::userCreateSetCacheKeysRedo, $value);
                                if ($result) {
                                    $r->srem(UserForm::userCreateSetCacheKeys, $value);
                                }
                            }
                        }

                        if (!empty($accountArr)) {
                            $n = Yii::$app->db->createCommand()->batchInsert('users', ['account', 'name', 'age', 'sex', 'email', 'password', 'phone', 'create_time', 'update_time'], $accountArr)->execute();
                        }

                        if ($n = count($accountArr)) {
                            foreach ($accountArr as $key => $value) {
                                $r->srem(UserForm::userCreateSetCacheKeys, $value[0]);
                                $r->hDel(UserForm::userCreateHashCacheDetail, $value[0]);
                            }
                        } else {
                            foreach ($accountArr as $key => $value) {
                                $result = $r->setAdd(UserForm::userCreateSetCacheKeysRedo, $value[0]);
                                if ($result) {
                                    $r->srem(UserForm::userCreateSetCacheKeys, $value[0]);
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage(),'shell_user');
            echo $exc->getMessage();
        }
    }

    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     */
    public function actionAddUser() {
        try {
            $r = new RedisService();
            $isExist = $r->exists(UserForm::userCreateSetCacheKeys);
            if ($isExist) {
                $nums = $r->getScard(UserForm::userCreateSetCacheKeys);
                if ($nums > 0) {
                    $accounts = $r->getSrandmember(UserForm::userCreateSetCacheKeys, 1); //一次取1条
                    if (count($accounts) > 0) {
                        $account = $accounts[0];

                        $accountLock = $r->hSet(UserForm::userCreateHashCacheLock, $account,1); //加锁
                        if ($accountLock) {
                            $accountArr = [];
                            $accountDetail = $r->hGet(UserForm::userCreateHashCacheDetail, $account);
                            if (!empty($accountDetail)) {
                                $accountDetailArr = json_decode($accountDetail, true);
                                unset($accountDetailArr['id']);
                                $accountArr[] = array_values($accountDetailArr);
                            } else {
                                $result = $r->setAdd(UserForm::userCreateSetCacheKeysRedo, $account);
                                if ($result) {
                                    $r->srem(UserForm::userCreateSetCacheKeys, $account);
                                }
                                $r->hDel(UserForm::userCreateHashCacheLock, $account); //解锁    
                                throw new \Exception('account ' . $account . ' detail is null.');
                            }

                            if (!empty($accountArr)) {
                                $result = Yii::$app->db->createCommand()->batchInsert('users', ['account', 'name', 'age', 'sex', 'email', 'password', 'phone', 'create_time', 'update_time'], $accountArr)->execute();
                            }

                            if ($result) {
                                $r->srem(UserForm::userCreateSetCacheKeys, $account);
                                $r->hDel(UserForm::userCreateHashCacheDetail, $account);
                            } else {
                                $result = $r->setAdd(UserForm::userCreateSetCacheKeysRedo, $account);
                                if ($result) {
                                    $r->srem(UserForm::userCreateSetCacheKeys, $account);
                                }
                            }
                            $r->hDel(UserForm::userCreateHashCacheLock, $account); //解锁    
                        }
                    }
                }
            }
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage(),'shell_user');
            echo $exc->getMessage();
        }
    }
    
    
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     */
    public function actionAddUserRedo() {
        try {
            $r = new RedisService();
            $isExist = $r->exists(UserForm::userCreateSetCacheKeysRedo);
            if ($isExist) {
                $nums = $r->getScard(UserForm::userCreateSetCacheKeysRedo);
                if ($nums > 0) {
                    $accounts = $r->getSrandmember(UserForm::userCreateSetCacheKeysRedo, 1); //一次取1条
                    if (count($accounts) > 0) {
                        $account = $accounts[0];

                        $accountLock = $r->hSet(UserForm::userCreateHashCacheLock, $account,1); //加锁
                        if ($accountLock) {
                            $accountArr = [];
                            $accountDetail = $r->hGet(UserForm::userCreateHashCacheDetail, $account);
                            if (!empty($accountDetail)) {
                                $accountDetailArr = json_decode($accountDetail, true);
                                unset($accountDetailArr['id']);
                                $accountArr[] = array_values($accountDetailArr);
                            } else {
                                $r->hDel(UserForm::userCreateHashCacheLock, $account); //解锁    
                                throw new Exception('account ' . $account . ' detail is null.');
                            }

                            if (!empty($accountArr)) {
                                $result = Yii::$app->db->createCommand()->batchInsert('users', ['account', 'name', 'age', 'sex', 'email', 'password', 'phone', 'create_time', 'update_time'], $accountArr)->execute();
                            }

                            if ($result) {
                                $r->srem(UserForm::userCreateSetCacheKeysRedo, $account);
                                $r->hDel(UserForm::userCreateHashCacheDetail, $account);
                            } else {
                                $result = $r->setAdd(UserForm::userCreateSetCacheKeysRedo, $account);
                                if ($result) {
                                    $r->srem(UserForm::userCreateSetCacheKeysRedo, $account);
                                }
                            }
                            $r->hDel(UserForm::userCreateHashCacheLock, $account); //解锁    
                        }
                    }
                }
            }
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage(),'shell_user');
            echo $exc->getMessage();
        }
    }

}
