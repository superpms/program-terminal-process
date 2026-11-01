<?php


use pms\facade\Service;
use pms\hook\TerminalCommandHook;
use pms\program\terminalProcess\command\TerminalCommandProcessMonitorCommand;
use pms\service\TerminalCommandProcessMonitorService;

TerminalCommandHook::mount(
    TerminalCommandProcessMonitorCommand::class
);

// 终端命令进程监控服务
Service::register(TerminalCommandProcessMonitorService::class);
