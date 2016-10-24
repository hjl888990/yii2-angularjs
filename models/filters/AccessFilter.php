<?php
/**
 * 访问过滤器
 */
namespace app\models\filters;

use Yii;
use \yii\base\ActionFilter;
use app\models\Filters;
use app\models\common\Response;
use app\models\exception\OPException;

class AccessFilter extends ActionFilter{

    public function beforeAction($action)
    {
        try {
            $session = Yii::$app->session;
            $session->open();
            $account = $session->get('account');
            if ($account == NULL) {
                throw new OPException(OPException::ERR_NOLOGIN);
            } else {
                return parent::beforeAction($action);
            }
        } catch (\Exception $exc) {
            Response::outputFailed($exc->getCode(), $exc->getMessage());
        }
        
    }

    public function afterAction($action, $result)
    {
        return parent::afterAction($action, $result);
    }
}
