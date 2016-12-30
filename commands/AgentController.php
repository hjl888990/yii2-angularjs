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
use app\models\User;


/**
 * 代理服务
 */
class AgentController extends Controller {

    /**
     * swoole 接受后端异步处理请求
     * 
     * dispatch_mode:数据包分发策略。可以选择3种类型，默认为2
      // 1，轮循模式，收到会轮循分配给每一个worker进程
      // 2，固定模式，根据连接的文件描述符分配worker。这样可以保证同一个连接发来的数据只会被同一个worker处理
      // 3，抢占模式，主进程会根据Worker的忙闲状态选择投递，只会投递给处于闲置状态的Worker
      // 4，IP分配，根据TCP/UDP连接的来源IP进行取模hash，分配给一个固定的worker进程, 可以保证同一个来源IP的连接数据总会被分配到同一个worker进程
      // 5，UID分配，需要用户代码中调用$serv->bind() 将一个连接绑定1个uid。然后swoole根据UID的值分配到不同的worker进程
     */
    public function actionService() {
        $socketConfig = Yii::$app->params['swoole_http_service_host'];
        $socketAddress = !empty($socketConfig['ip']) ? $socketConfig['ip'] : '0.0.0.0';
        $socketPort = !empty($socketConfig['port']) ? $socketConfig['port'] : '8000';

        $server = new \swoole_http_server($socketAddress, $socketPort);
        $server->set(array(
            'worker_num' => 8, //工作进程数量
            //daemonize是否作为守护进程,此配置一般配合log_file使用
            'daemonize' => false,
            //max_request处理这么多请求之后,重启worker进程，防止内存泄露。只能用在同步阻塞模式下，如果是纯异步的程序设置了可能会出问题,0不重启。
            'max_request' => 0,
            'dispatch_mode' => 3, //抢占模式
            'debug_mode' => 1,
            'log_file' => '/data/yii2-angularjs/runtime/logs/swoole.log',
            //每隔heartbeat_check_interval秒后遍历一次全部连接，检查最近一次发送数据的时间和当前时间的差
            'heartbeat_check_interval' => 30,
            //如果这个差值大于heartbeat_idle_time，则会强制关闭这个连接，并通过回调onClose通知Server进程。 heartbeat_idle_time的默认值是heartbeat_check_interval的两倍
            'heartbeat_idle_time' => 60,
        ));

        $server->on('Request', function($request, $response) {
            $res = array('ret' => 1, 'errCode' => 0, 'errMsg' => '', 'data' => []);
            $gets = isset($request->get) ? $request->get : [];
            $requestLog = getmypid().'[' . date("Y-m-d H:i:s") . '] request data:' . json_encode($gets) . PHP_EOL;
            echo $requestLog;
            $command = isset($gets['command']) ? base64_decode($gets['command']) : '';
            $token = isset($gets['token']) ? $gets['token'] : '';
            if ($command == '' || 'W5lZWRjYWNoZWZpbGU' != $token) {
                $return_info = 'Access denied!';
                $res['ret'] = 0;
            } else {
                $return_info = 'The request is successful!'.getmypid();
            }
            $res['errMsg'] = $return_info;
            //异步处理请求
            $commands = json_decode($command, true);
            
            $response->end(json_encode($res)); //返回响应给客户端
           // $this->processCommand($commands);
        });


        $server->start();
    }

    /**
     * html5_websocket-->swoole_websocket_server
     * 运营页面
     */
    public function actionWebService() {
        $socketConfig = Config::getParam('socketServer');
        $socketAddress = !empty($socketConfig['ip']) ? $socketConfig['ip'] : '0.0.0.0';
        $swooleWebPort = Config::getParam('swooleWebPort');
        $socketPort = empty($swooleWebPort) ? 8001 : $swooleWebPort;

        $address = !empty($this->ip) ? $this->ip : $socketAddress;
        $port = !empty($this->port) ? $this->port : $socketPort;

        $server = new \swoole_websocket_server($address, $port);
        $server->set(array(
            'worker_num' => 8, //工作进程数量
            //daemonize是否作为守护进程,此配置一般配合log_file使用
            'daemonize' => true,
            //max_request处理这么多请求之后,重启worker进程，防止内存泄露。只能用在同步阻塞模式下，如果是纯异步的程序设置了可能会出问题,0不重启。
            'max_request' => 0,
            'dispatch_mode' => 3, //抢占模式
            'debug_mode' => 1,
            'log_file' => '/data/yii2-angularjs/runtime/logs/swoole.log',
            //每隔heartbeat_check_interval秒后遍历一次全部连接，检查最近一次发送数据的时间和当前时间的差
            'heartbeat_check_interval' => 30,
            //如果这个差值大于heartbeat_idle_time，则会强制关闭这个连接，并通过回调onClose通知Server进程。 heartbeat_idle_time的默认值是heartbeat_check_interval的两倍
            'heartbeat_idle_time' => 60,
        ));

        $server->on('open', function (\swoole_websocket_server $server, $request) {
            $newFd = $request->fd;
            //客户端referfers检测
            $header = $request->header;
            $referers = str_replace('http://', '', $header['origin']);
            $referers = str_replace('https://', '', $referers);
            $access_referers = Config::getParam('access_referer');
            if (isset($access_referers) && !empty($access_referers)) {//referer检测
                if (!in_array($referers, $access_referers)) {
                    $refererMsg = "server: handshake faile with fd{$newFd},referers illegal." . PHP_EOL;
                    $server->push($newFd, $refererMsg);
                    $requestLog = '[' . date("Y-m-d H:i:s") . ']' . $refererMsg . PHP_EOL;
                    echo $requestLog;
                    $server->close($newFd, true);
                } else {
                    $requestLog = '[' . date("Y-m-d H:i:s") . "]server: handshake success with fd{$newFd}" . PHP_EOL;
                    echo $requestLog;
                }
            }
        });

        $server->on('message', function (\swoole_websocket_server $server, $request) {
            $msg = $request->data;
            //例子：'xmanTools/readLog/sh_xman_cache/1' 格式：类/方法/参数1/参数2
            $msgArr = explode('/', $msg);
            if (count($msgArr) < 2) {
                $server->push($request->fd, 'request' . $msg . ' format error.'); //格式错误
                $server->close($request->fd, true);
            } else {
                try {
                    $params = $msgArr;
                    unset($params[0]);
                    unset($params[1]);
                    $params = array_values($params);

                    switch ($msgArr[0]) {
                        case 'xmanTools':
                            $models = new XmanTools();
                            $result = $models->$msgArr[1]($params); //读取指定文件指定行的日志
                            break;
                        default :
                            throw new \Exception("There is no models name [$msgArr[0]]");
                    }

                    if ($result == 'exit') {//结束符，主动关闭连接
                        $server->close($request->fd, true);
                    } else {
                        $server->push($request->fd, $result); //返回信息给客户端
                    }
                } catch (\Exception $exc) {
                    $errorMsg = '<font color="crimson">' . $exc->getMessage() . '</font>';
                    $server->push($request->fd, $errorMsg);
                    $server->close($request->fd, true);
                }
            }
        });

        $server->on('close', function ($ser, $fd) {
            echo "client {$fd} closed\n";
        });

        $server->start();
    }

    /**
     * process command
     * @param $commands
     */
    public function processCommand($commands) {
        try {
            $cmd = $commands['command'];
            switch ($cmd) {
                case 'addUser':
                    $this->addUser();
                    break;
            }
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage(),'swoole_service');
        }
    }
    
    protected function addUser() {
        $r = new RedisService();
        $account = $r->incr('account');
        if ($account === false) {
            throw new \Exception('get account error.');
        }
        $user = [
            "name" => "dasda",
            "password" => "231312",
            "confirm_password" => "231312",
            "email" => $account . "@qq.com",
            "age" => "12",
            "sex" => "1",
            "phone" => "13627009379",
            "account" => $account
        ];
        $userModel = new User();
        $result = $userModel->createUser($user);
        
        if(!$result){
            throw new \Exception('add user error.data:'.  json_encode($user));
        }
    }

    /**
     * 清楚任务锁
     * @param type $cmd
     */
    protected function cleanProcessLock($cmd) {
        $processDoing = \Yii::$app->redis->executeCommand('GET', ['swoole_process_doing_' . $cmd]);
        if (!empty($processDoing)) {
            \Yii::$app->redis->executeCommand('DEL', ['swoole_process_doing_' . $cmd]);
        }
    }

    /**
     * 释放mysql、redis链接资源
     */
    protected function closeResource($cmd) {
        //释放xman数据库链接
        if (in_array($cmd, ['xman_cache', 'version'])) {
            $db = new DB();
            $db->close();
        }

        //释放库存redis
        if (in_array($cmd, ['stock'])) {
            $redisStocks = [];
            $redisStockConfig = Config::getParam('redis_stock');
            foreach ($redisStockConfig as $key => $value) {
                foreach ($value as $k => $v) {
                    $redisStocks[] = $v;
                }
            }

            if (!empty($redisStocks)) {
                foreach ($redisStocks as $stock) {
                    $redis = RedisPool::getInstance($stock['host'], $stock['port']);
                    $redis->close();
                }
            }
        }
    }

    /*
     * runShellCmd 运行shell命令
     * @param type $type
     * @param type $shLogFile
     * @param type $source
     * @return type
     * 
     */

    protected function runShellCmd($type, $shLogFile, $source) {
        $res = '';
        try {
            switch ($type) {
                case 'restart_swoole_websocket_server':
                    $res = shell_exec("ps -ef|grep agent/web-service|grep -v grep|cut -c 9-15|xargs kill -9;php /data/xman/yii agent/web-service"); //重启进程
                    file_put_contents($shLogFile, 'restart swoole_websocket_server finish.' . PHP_EOL, FILE_APPEND);
                    break;
                case 'restart_swoole_http_server':
                    //提前写入日志和清楚锁
                    $this->cleanProcessLock('shell_cmd');
                    file_put_contents($shLogFile, 'restart restart_swoole_http_server finish.' . PHP_EOL . 'exit', FILE_APPEND);
                    $res = shell_exec("ps -ef|grep agent/service|grep -v grep|cut -c 9-15|xargs kill -9;php /data/xman/yii agent/service"); //重启进程
                    break;
                default :
                    throw new \Exception("There is no models name [$type]");
            }
        } catch (\Exception $exc) {
            throw new \Exception($exc->getMessage());
        }
        return $res;
    }

}
