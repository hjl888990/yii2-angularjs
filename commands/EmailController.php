<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\services\RedisService;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class EmailController extends Controller {

    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     */
    public function actionSendEmail() {
        try {
            $redis = new RedisService();
            $emailDoRedisListKey = Yii::$app->params['emailDoRedisListKey'];
            $emailDoRedisDetailKey = Yii::$app->params['emailDoRedisDetailKey'];
            $emailReDoRedisListKey = Yii::$app->params['emailReDoRedisListKey'];
            $isExist = $redis->exists($emailDoRedisListKey);
            if ($isExist) {
                $nums = $redis->lLen($emailDoRedisListKey);
                if ($nums > 0) {
                    $emailId = $redis->rPop($emailDoRedisListKey);
                    $emailJson = $redis->hGet($emailDoRedisDetailKey,$emailId);
                    if(!empty($emailJson)){
                        $emailArr = json_decode($emailJson,true);
                        $ret = $this->sendEmail($emailArr);
                        if($ret){
                            $redis->hDel($emailDoRedisDetailKey,$emailId);
                        }else{
                            throw new \Exception('邮件发送失败');
                        } 
                    }

                }
            }
        } catch (\Exception $exc) {
            $redis->lPush($emailReDoRedisListKey, $emailId);
            Yii::error($exc->getMessage(),'email');
            echo $exc->getMessage();
        }
    }
      
    protected function sendEmail($emailArr) {
        $mail = Yii::$app->mailer->compose();
        $mail->setTo($emailArr['sendTo']);
        $mail->setSubject($emailArr['subject']);
        $mail->setHtmlBody($emailArr['htmlBody']);    //发布可以带html标签的文本
        if ($mail->send()){
            Yii::info($emailArr['sendTo'] . '《' . $emailArr['subject'] . '》邮件发送成功', 'email');
            return true;
        }
        else {
            return false;
        }
    }

}
