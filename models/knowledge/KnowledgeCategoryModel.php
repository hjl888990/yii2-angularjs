<?php

namespace app\models\knowledge;

use Yii;
use yii\base\Model;
use yii\data\Pagination;
use app\models\knowledge\KnowledgeCategoryForm;
use services\RedisService;

/**
 * CountrySearch represents the model behind the search form about `app\models\Country`.
 */
class KnowledgeCategoryModel extends Model {

    /**
     * 查询
     * 
     * @param type $searchParams
     * @return type
     */
    public function searchKnowledgeCat($searchParams) {
        $kcatModels = KnowledgeCategoryForm::find()->addSelect('id,parentid,title,orderindex,level,status,storeid,create_time,modify_time');
        $data = $kcatModels->andWhere($searchParams);
        $result = $data->orderBy('orderindex DESC , create_time DESC')->all();
        foreach ($result as $k => $v) {
            $result[$k] = $v->attributes;
        }
        $resultArray['data'] = $result;
        return $resultArray;
    }


    /**
     * 创建
     */
    public function createKnowledgeCat($params) {
        $catForm = new KnowledgeCategoryForm();
        $connection = $catForm->getDb();
        $catForm->scenario = 'add';
        $catForm->setAttributes($params);
        $transaction = $connection->beginTransaction();
        try {
            if ($catForm->validate()) {//验证输入
                $cresult = $catForm->save();
                if ($cresult) {
                    $id = $catForm->attributes['id'];
                    $catForm->setAttributes(array('orderindex'=>$id));
                    $uresult = $catForm->save();
                    if ($uresult) {
                        $transaction->commit();
                    }else{
                       $transaction->rollBack(); 
                    }
                } else {
                    $transaction->rollBack();
                }
                return $cresult;
            } else {
                // 验证失败：$errors 是一个包含错误信息的数组
                $errors = $catForm->errors;
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
    public function updateKnowledgeCat($params) {
        $kcatForm = new KnowledgeCategoryForm();
        $connection = $kcatForm->getDb();
        if (isset($params['id'])) {
            $cat = KnowledgeCategoryForm::findOne((int) $params['id']);
            if (empty($cat)) {
                return false;
            } else {
                $cat->scenario = 'update';
                $cat->setAttributes($params);
                if ($cat->validate()) {//验证输入
                    $transaction = $connection->beginTransaction();
                    $result = $cat->save();
                    if ($result == 1) {
                        $transaction->commit();
                        return true;
                    } else {
                        $transaction->rollBack();
                    }
                } else {
                    // 验证失败：$errors 是一个包含错误信息的数组
                    $errors = $cat->errors;
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


}
