<?php

namespace app\models\common;

use Yii;

class Func {

    /**
     * 提取cookies信息
     *
     * @return multitype: array
     */
    public static function getCookiesUserInfo($nameArry) {
        
        $cookieArry = array();
        $cookies = Yii::$app->request->cookies;
        if(is_array($nameArry) && !empty($nameArry)){
            foreach ($nameArry as $name){
               if (!$cookies->has(WEBSITENAME.$name)) {
                    return array();
                }else{
                    $cookieArry[$name] = $cookies->getValue(WEBSITENAME.$name, '');
                }
            }
        }else{
            return array();
        }
        return $cookieArry;
    }

    /**
     * 设置cookies信息
     *
     * @param unknown $dataList        	
     */
    public static function setCookiesUserInfo($dataList, $time = 7200) {
        foreach ($dataList as $k => $v) {
            self::setCookiesAte(WEBSITENAME.$k, $v, $time);
        }
    }

    /**
     * 设置cookiese信息
     */
     public static function setCookiesAte($name = '', $value = '', $time = 3600) {
        $cookies = Yii::$app->response->cookies;
        $cookies->add(new \yii\web\Cookie([
            'name' => $name,
            'value' => $value,
            'expire' => time() + $time
        ]));
    }
    
    //设置session
    public static function setSession($sarray) {
        $session = Yii::$app->session;
        $session->open();
        foreach ($sarray as $k => $v) {
            $session[$k] = strtolower($v);
        }
    }

    //获取session
    public static function getSession($skey) {

        $session = Yii::$app->session;
        $session->open();
        $code = null;

        if (!empty($session[$skey])) {
            $code = $session[$skey];
        } 
        return $code;
    }

    /**
     * 清楚session
     */
    public static function clearSession($skey) {
        $session = Yii::$app->session;
        $session->open();
        $name = $skey;
        unset($session[$name]);
    }
    
    


}
