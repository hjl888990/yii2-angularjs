#!/bin/bash
#该脚本需在bash版本>=4中执行
#输出当前地址
CRON_DIR=$(cd $(dirname "$0"); pwd)
#执行进程监控脚本的命令
zombie_alert_cmd="/bin/sh $CRON_DIR/cron_zombie_alert.sh& > /dev/null"
#获取php进程并发数配置
CRON_COUNT_INI=$CRON_DIR/../config/cron_count.ini
echo $CRON_COUNT_INI

#定义数组deamon_map
declare -A deamon_map

#key 为cron_count里的key value为命令脚本地址
deamon_map["addUser"]="$CRON_DIR/../task/addUser.php"

while true; do
    #循环执行deamon_map里的命令
    #${!arr[@]} 用于返回数组array的所有下标
    for deamon_count_key in "${!deamon_map[@]}" ; do
        echo $deamon_count_key
        #计算出配置文件里面php进程的并发数
        SUM=`grep "^$deamon_count_key *=" "$CRON_COUNT_INI" | awk '{print $3}'`
        #若在cron_count.ini中不存在，则默认赋值队列并发数1
        if ! (echo $SUM | egrep -q '^[0-9]+$'); then
            SUM=1   
        fi
        php_script="${deamon_map["$deamon_count_key"]}"
        #计算当前运行中的php进程数目
        proc=`/bin/ps xaww | grep -v " grep" | grep "$php_script" |wc -l`
        current_count=$proc
        #若小于进程的配置数，则进行调起
        if [ $current_count -lt "$SUM" ];then
            need_to_open_count=`expr $SUM - $current_count`
            while [ $need_to_open_count -gt 0 ]
            do
                php "$php_script" &
                (( need_to_open_count-- ))
            done
        fi
    done

    #php进程的监控与消息通知
    eval "$zombie_alert_cmd"
    sleep 1
done