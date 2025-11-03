<?php

namespace pms\program\terminalProcess;

use pms\module\TerminalProcessModule;

/**
 * @property int $space 内存空间;
 * @property int $service_name;
 * @property int $service_address 服务内存地址;
 * @property int $keep_alive_interval 保持活动间隔;
 * @property int $pid;
 * @property int $address 进程内存地址;
 * @property int $start_time;
 * @property int $active_time;
 * @method $this setKeepAliveInterval(int $keepAliveInterval)
 */
class TerminalProcessRedisModule extends TerminalProcessModule
{

    protected string $processDriver = TerminalProcessRedisDriver::class;


}