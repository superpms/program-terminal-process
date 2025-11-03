<?php

namespace pms\annotate;

/**
 * 终端命令进程监控
 */
#[\Attribute] class TerminalCommandProcessMonitor{

    /**
     * @param string $taskUUID 任务ID
     * @param int $keepAliveInterval 检测间隔
     */
    public function __construct(string $taskUUID,int $keepAliveInterval){}
}