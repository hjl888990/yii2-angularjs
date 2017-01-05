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
use yii\db\Connection;

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
        $socketConfig = Yii::$app->params['swoole_service_host'];
        $socketAddress = isset($socketConfig['ip']) ? $socketConfig['ip'] : '0.0.0.0';
        $socketPort = isset($socketConfig['port']) ? $socketConfig['port'] : '8001';

        $server = new \swoole_http_server($socketAddress, $socketPort);
        $server->set(array(
            'worker_num' => 4, //工作进程数量
            'task_worker_num' => 8,//异步任务进程数量
            //daemonize是否作为守护进程,此配置一般配合log_file使用
            'daemonize' => false,
            //max_request处理这么多请求之后,重启worker进程，防止内存泄露。只能用在同步阻塞模式下，如果是纯异步的程序设置了可能会出问题,0不重启。
            'max_request' => 0,
            'dispatch_mode' => 3, //抢占模式，主进程会根据Worker的忙闲状态选择投递，只会投递给处于闲置状态的Worker
            'debug_mode' => 1,
            'log_file' => '/data/yii2-angularjs/runtime/logs/swoole.log',
            //每隔heartbeat_check_interval秒后遍历一次全部连接，检查最近一次发送数据的时间和当前时间的差
            'heartbeat_check_interval' => 30,
            //如果这个差值大于heartbeat_idle_time，则会强制关闭这个连接，并通过回调onClose通知Server进程。 heartbeat_idle_time的默认值是heartbeat_check_interval的两倍
            'heartbeat_idle_time' => 60,
        ));

        $server->on('Request', function($request, $response)  use ($server) {
            $res = array('ret' => 1, 'errCode' => 0, 'errMsg' => '', 'data' => []);
            $gets = isset($request->get) ? $request->get : [];
            $msg = "request data:" . json_encode($gets);
            $this->printMsg($msg, $server->worker_pid);
            $command = isset($gets['command']) ? base64_decode($gets['command']) : '';
            $commands = json_decode($command, true);
            //$this->processCommand($commands);//同步
            //异步处理请求
            $taskId = $server->task($commands);
            $msg =  "Dispath AsyncTask: id=$taskId";
            $this->printMsg($msg, $server->worker_pid);
            //返回响应给客户端
            $response->end(json_encode($res)); 
            
        });
        
        //处理异步任务
        $server->on('task', function ($server, $task_id, $from_id, $data) {
            $msg =  "New AsyncTask[id=$task_id]";
            $this->printMsg($msg, $server->worker_pid);
            $result = $this->processCommand($data);
            //返回任务执行的结果
            $data = json_encode($data,true);
            $resultMsg = "$data ==> $result";
            $server->finish($resultMsg);
        });

        //处理异步任务的结果
        $server->on('finish', function ($server, $task_id, $data) {
            $msg =  "AsyncTask[$task_id] Finish: $data";
            $this->printMsg($msg, $server->worker_pid);
        });

        $server->start();
    }

    /**
     * html5_websocket-->swoole_websocket_server
     * 运营页面
     */
    public function actionWebService() {
        $socketConfig = Yii::$app->params['swoole_service_host'];
        $socketAddress = isset($socketConfig['ip']) ? $socketConfig['ip'] : '0.0.0.0';
        $socketPort = isset($socketConfig['port']) ? $socketConfig['port'] : '8002';

        $server = new \swoole_websocket_server($socketAddress, $socketPort);
        $server->set(array(
            'worker_num' => 2, //工作进程数量
            //daemonize是否作为守护进程,此配置一般配合log_file使用
            'daemonize' => false,
            //max_request处理这么多请求之后,重启worker进程，防止内存泄露。只能用在同步阻塞模式下，如果是纯异步的程序设置了可能会出问题,0不重启。
            'max_request' => 0,
            'dispatch_mode' => 2, //固定模式，根据连接的文件描述符分配worker。这样可以保证同一个连接发来的数据只会被同一个worker处理
            'debug_mode' => 1,
            'log_file' => '/data/yii2-angularjs/runtime/logs/swoole.log',
            //每隔heartbeat_check_interval秒后遍历一次全部连接，检查最近一次发送数据的时间和当前时间的差
            'heartbeat_check_interval' => 30,
            //如果这个差值大于heartbeat_idle_time，则会强制关闭这个连接，并通过回调onClose通知Server进程。 heartbeat_idle_time的默认值是heartbeat_check_interval的两倍
            'heartbeat_idle_time' => 60,
        ));

        $server->on('open', function (\swoole_websocket_server $server, $request) {
            $newFd = $request->fd;
            $msg = "server: handshake success with fd{$newFd}";
            $this->printMsg($msg, $server->worker_pid);
        });

        $server->on('message', function (\swoole_websocket_server $server, $request) {
            $type = $request->data;
            $worker_pid = $server->worker_pid;
            try {
                switch ($type) {
                    case 'timer_tick':
                        //每隔2000ms触发一次
                        $GLOBALS['timer_id'] = \swoole_timer_tick(2000, function ($worker_pid) {
                            $msg = 'swoole_timer_tick run';
                            $this->printMsg($msg, $worker_pid);
                        });
                        $msg = "swoole timer tick {$GLOBALS['timer_id']} start";
                        $this->printMsg($msg, $worker_pid);
                        $server->push($request->fd, "swoole timer tick {$GLOBALS['timer_id']} start");
                        break;
                    case 'timer_clear':
                        if (isset($GLOBALS['timer_id'])) {
                            $msg = "swoole timer tick {$GLOBALS['timer_id']} clear";
                            $this->printMsg($msg, $server->worker_pid);
                            $server->push($request->fd, "swoole timer tick {$GLOBALS['timer_id']} clear");
                            \swoole_timer_clear($GLOBALS['timer_id']);
                        }
                        break;
                }
            } catch (\Exception $exc) {
                $msg = "Exception errorMsg:" . $exc->getMessage();
                $this->printMsg($msg, $server->worker_pid);
                $server->push($request->fd, $msg);
                $server->close($request->fd, true);
            }
        });

        //监听WebSocket连接关闭事件
        $server->on('close', function ($server, $fd) {
            $msg = "client-{$fd} is closed";
            $this->printMsg($msg, $server->worker_pid);
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
            $params = $commands['params'];
            switch ($cmd) {
                case 'addUser':
                    $result = $this->addUser($params);
                    break;
            }
            //释放资源链接
            $this->closeResource($cmd);
        } catch (\Exception $exc) {
            $result = $exc->getMessage();
        }
        return $result;
    }

    protected function addUser($user) {
        $userModel = new User();
        $result = $userModel->createUser($user);
        return $result;
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
        if (in_array($cmd, ['addUser'])) {
            $db = Yii::$app->getDb();
            $db->close();
            
        }
        
        //释放库存redis
        if (in_array($cmd, ['addUser'])) {
            $redis = Yii::$app->getRedis();
            $redis->close();
        }

    }

    /**
     * 输入信息
     * @param type $msg 信息
     * @param type $pid 进程号
     */
    protected function printMsg($msg, $pid) {
        echo "[" . date("Y-m-d H:i:s") . "]" . $msg . ".【pid:{$pid}】" . PHP_EOL;
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
