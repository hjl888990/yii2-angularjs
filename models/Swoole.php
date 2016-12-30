<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\Pagination;
use app\models\entity\UserForm;
use app\models\common\Func;
use app\services\RedisService;
use app\models\exception\OPException;

/**
 * CountrySearch represents the model behind the search form about `app\models\Country`.
 */
class Swoole extends Model {
    
    /**
     * 添加异步执行任务
     */
    public function syncAddTask($command) {
        $commands = ['command' => $command, 'params' => []];
        $commands = json_encode($commands);
        $host = Yii::$app->params['webSiteUrl'];
        $url = $host . '/swoole?command=' . base64_encode($commands) . '&token=W5lZWRjYWNoZWZpbGU';
        $url = str_replace('https://', 'http://', $url);
        $res = $this->getCurl($url);
        return $res;
    }
    
    public function getCurl($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOBODY, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        $contents = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $result = '';
        if ($httpCode == '200') {
            if (empty($error)) {
                $result = json_decode($contents, true);
                if ($result['ret'] == 1) {
                    $result = ['ret' => 1, 'content' => $result['errMsg'], 'http_code' => $httpCode];
                } else {
                    $result = ['ret' => 0, 'content' => $result['errMsg'], 'http_code' => $httpCode];
                }
            } else {
                $result = ['ret' => 0, 'content' => $error, 'http_code' => $httpCode];
            }
        } else {
            if(!empty($error)){
                $result = ['ret' => 0, 'content' => $error, 'http_code' => $httpCode];
            }else{
                $result = ['ret' => 0, 'content' => 'curl '.$url.' http_code:'.$httpCode, 'http_code' => $httpCode];
            }
            
        }
        return $result;
    }

}
