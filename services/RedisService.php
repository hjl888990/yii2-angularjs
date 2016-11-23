<?php

namespace app\services;

use Yii;

/**
 * 读取redis服务
 */
class RedisService {

    /**
     * $key: hjl_*
     * 获取指定前缀的rediskey
     */
    public function getKeys($keyPrefix) {
        $keys = Yii::$app->redis->executeCommand('KEYS', [$keyPrefix]);
        return $keys;
    }

    /**
     * 获取key的redis数据
     */
    public function get($key) {
        $result = Yii::$app->redis->executeCommand('GET', [$key]);
        return $result;
    }

    /**
     * 批量获取array(key1,key2····）的redis数据
     */
    public function mget($keyArray) {
        $result = Yii::$app->redis->executeCommand('MGET', $keyArray);
        return $result;
    }

    /**
     * 向redis添加元素$key—>$value
     */
    public function set($key, $value) {
        return Yii::$app->redis->executeCommand('SET', [$key, $value]);
    }

    /**
     * 批量向redis添加元素array(key1=>value1,key2=>value2····)
     */
    public function mset($kvArray) {
        $msetData = array();
        foreach ($kvArray as $k => $v) {
            $msetData[] = $k;
            $msetData[] = $v;
        }
        return Yii::$app->redis->executeCommand('MSET', $msetData);
    }

    /**
     * 向redis添加元素$key—>$value,过期时间$time_out(单位秒)
     */
    public function setexData($key, $value, $time_out) {
        return Yii::$app->redis->executeCommand('SETEX', [$key, $time_out, $value]);
    }

    /**
     * 向redis元素$key自增
     */
    public function incr($key) {
        return Yii::$app->redis->executeCommand('INCR', [$key]);
    }

    /**
     * 判断$key是否存在
     * return 1存在；0不存在
     */
    public function exists($key) {
        $result = Yii::$app->redis->executeCommand('EXISTS', [$key]);
        return $result;
    }

    /**
     * 设置$key的生存时间，单位秒
     */
    public function setTimeOut($key, $timeout) {
        $result = Yii::$app->redis->executeCommand('EXPIRE', [$key, $timeout]);
        return $result;
    }

    /**
     * delete  删除指定key的值
     */
    public function delete($key) {
        return Yii::$app->redis->executeCommand('DEL', [$key]);
    }

    /**
     * 根据前缀key删除所有
     * @param type $keyPrefix
     */
    public function deleteByPrefix($keyPrefix) {
        $keys = Yii::$app->redis->executeCommand('KEYS', [$keyPrefix . '*']);
        if (!empty($keys) && is_array($keys)) {
            foreach ($keys as $key) {
                $this->delete($key);
            }
        }
    }

    /**
     * 移除生存时间到期的key
     */
    public function persist($key) {
        return Yii::$app->redis->executeCommand('PERSIST', [$key]);
    }

    /**
     * 开始事物
     */
    public function multi() {
        return Yii::$app->redis->executeCommand('MULTI');
    }

    /**
     * 结束事物
     */
    public function exec() {
        return Yii::$app->redis->executeCommand('EXEC');
    }

    
    
    /*************************Hash操作********************** */
    /**
     * hSet
     * $redis->hSet('hashkey', 'key', 'value');
     * 向名称为hashkey的hash中添加元素key—>hello
     */
    public function hSet($hashkey, $key, $value) {
        return Yii::$app->redis->executeCommand('HSET', [$hashkey, $key, $value]);
    }

    /**
     * hmSet
     * $redis->hmSet('hashkey',array('key1'=>'v1','key2'=>'v2'));
     * 向名称为hashkey的hash中批量添加元素
     */
    public function hmSet($hashkey, $kvArray) {
        $hmset = array();
        $hmset[] = $hashkey;
        foreach ($kvArray as $k => $v) {
            $hmset[] = $k;
            $hmset[] = $v;
        }
        return Yii::$app->redis->executeCommand('HMSET', $hmset);
    }

    /**
     * hGet
     * $redis->hGet('hashkey', 'key');
     * 返回名称为h的hashkey中key对应的value
     */
    public function hGet($hashkey, $key) {
        $result = Yii::$app->redis->executeCommand('HGET', [$hashkey, $key]);
        return $result;
    }

    /**
     * hmGet
     * $redis->hmGet('hashkey', array(key1', 'key2'));
     * 返回名称为hashkey的hash中key1,key2对应的value
     */
    public function hmGet($hashkey, $kArray) {
        $hmset = array();
        $data = array();
        $hmset[] = $hashkey;
        foreach ($kArray as $k => $v) {
            $hmset[] = $v;
        }
        $result = Yii::$app->redis->executeCommand('HMGET', $hmset);
        if (!empty($result)) {
            foreach ($kArray as $k => $v) {
                $data[$v] = $result[$k];
            }
        }
        return $data;
    }

    /**
     * hLen
     * $redis->hLen('hashkey');
     * 返回名称为hashkey的hash中元素个数
     */
    public function hLen($hashkey) {
        $result = Yii::$app->redis->executeCommand('HLEN', [$hashkey]);
        return $result;
    }

    /**
     * hExists
     * $redis->hExists('hashkey', 'key');
     * 名称为hashkey的hash中是否存在键名字为key的域
     * return 1存在；0不存在
     */
    public function hExists($hashkey, $key) {
        $result = Yii::$app->redis->executeCommand('HEXISTS', [$hashkey, $key]);
        return $result;
    }

    /**
     * hDelKey
     * $redis->hDel('hashkey', 'key');
     * 删除名称为hashkey的hash中键为key的域
     */
    public function hDel($hashkey, $key) {
        Yii::$app->redis->executeCommand('HDEL', [$hashkey, $key]);
    }

    /**
     * hKeys
     * $redis->hKeys('hashkey');
     * 返回名称为hashkey的hash中所有键
     */
    public function hKeys($hashkey) {
        $result = Yii::$app->redis->executeCommand('HKEYS', [$hashkey]);
        return $result;
    }

    /**
     * hVals
     * $redis->hVals('hashkey');
     * 返回名称为hashkey的hash中所有值
     */
    public function hVals($hashkey) {
        $result = Yii::$app->redis->executeCommand('HVALS', [$hashkey]);
        return $result;
    }

    /**
     * hGetAll
     * $redis->hGetAll('hashkey');
     * 返回名称为hashkey的hash中所有值
     */
    public function hGetAll($hashkey) {
        return Yii::$app->redis->executeCommand('HGETALL', [$hashkey]);
    }

    
    
    /** ***********************SET操作********************** */
    /**
     * setAdd
     * $redis->setAdd('setkey','value');
     * 向键值为setkey的set中插入value值，失败返回false
     */
    public function setAdd($setkey, $value) {
        return Yii::$app->redis->executeCommand('SADD', [$setkey, $value]);
    }
    
    /**
     * getScard
     * $redis->sCard('setkey'');
     * 返回key为$setkey的数量
     */
    public function getScard($setkey) {
        return Yii::$app->redis->executeCommand('SCARD', [$setkey]);
    }
    
    /**
     * getSrandmember
     * $redis->srandMember('setkey','count');
     * 返回key为$setkey的指定数量的值
     **/
    public function getSrandmember($setkey,$count = 1) {
        return Yii::$app->redis->executeCommand('SRANDMEMBER', [$setkey,$count]);
    }
    
    /**
     * srem
     * $redis->srem('setkey','value');
     * 移除key=$setkey,value=$value的值
     **/
    public function srem($setkey, $value) {
        return Yii::$app->redis->executeCommand('SREM', [$setkey, $value]);
    }
    

    /** ***********************LIST操作********************** */
    
    /**
     * lPush
     * $redis->lPush('listKey','value');
     * 插入一个值到列表左边,如果列表不存在,新建一个列表
     * 返回当前列表长度
     **/
    public function lPush($listKey, $value) {
        return Yii::$app->redis->executeCommand('LPUSH', [$listKey, $value]);
    }
    
    /**
     * lLen
     * $redis->lLen('listKey');
     * 返回当前列表长度
     **/
    public function lLen($listKey) {
        return Yii::$app->redis->executeCommand('LLEN', [$listKey]);
    }
    
    /**
     * rPop
     * $redis->rPop('listKey');
     * 删除并返回列表的最后一个值
     **/
    public function rPop($listKey) {
        return Yii::$app->redis->executeCommand('RPOP', [$listKey]);
    }
    
    
}
