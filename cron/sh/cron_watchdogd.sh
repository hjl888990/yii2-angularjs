#脚本的监控，保证其不死掉
#!/bin/sh
CRON_DIR=$(cd $(dirname "$0"); pwd)
cmd="/bin/sh $CRON_DIR/cron_watchdog.sh& > /dev/null"
#检测cron_watchdog.sh是否在执行
proc=`/bin/ps xaww | grep -v " grep" | grep -- "cron_watchdog.sh"`
#根据返回结果进行判断脚本是否执行
#test –z 字符串：测试字符串的长度是否为零
if test -z "$proc"
then
    #若不执行，那么就调起命令执行
    eval "$cmd"
fi