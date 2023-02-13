#bin/bash

ps -ef|grep hyperf|grep -v grep|awk '{print $2}'|xargs kill -9