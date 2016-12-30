#!/bin/sh
CRON_DIR=$(cd $(dirname "$0"); pwd)
PID_LOG=$CRON_DIR/../cron_status/
KILL_LOG=$CRON_DIR/../../runtime/logs/cron_kill.log
#用于检测30分钟未进行更新的进程文件。
Minute=30

#PID_LOG
cd "$PID_LOG"
if [ "$?" == 0 ];then
    #若进程文件30分钟没进行更新则认为已经僵死，需要kill并报警
    for pid in `find ./ -mmin +"$Minute"| grep -v /$ | awk -F '/' '{print $2}'`
    do
        if [ "$pid" != '' ];then
            NOW=`date +%Y-%m-%d_%H:%M`
            HOSTNAME=`hostname`
            nl='
            '
            PROCESS=`ps p$pid fuh`
            if [ "$PROCESS" != '' ];then
                PSTACK=`pstack $pid`
                #将pid进程信息输出到tmp.out文件，若2秒之后还在运行再kill此进程
                TMP=`timeout 2 strace -p $pid -o tmp.out`
                STRACE=`cat tmp.out`
                rm tmp.out
            fi
            #组织报警消息
            message="$NOW $HOSTNAME zombie process id $pid $nl$PROCESS$nl$PSTACK$nl$STRACE--"
            echo "$message">>"$KILL_LOG"
            #kill "$pid"
            cd  "$PID_LOG"
            #同时删除进程文件
            rm -r "$pid"
            #进行邮件或者其他的形式将message的内容同步出去
        fi
    done
fi