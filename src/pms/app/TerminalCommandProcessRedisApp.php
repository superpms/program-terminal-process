<?php

namespace pms\app;

use pms\app\basic\TerminalCommandProcessAppBasic;
use pms\module\TerminalProcessModule;
use pms\program\terminalProcess\TerminalProcessRedisModule;

abstract class TerminalCommandProcessRedisApp extends TerminalCommandProcessAppBasic
{
    protected function createProcessModule(string $taskUUID, int $pid = null):TerminalProcessModule
    {
        return new TerminalProcessRedisModule($taskUUID, $pid);
    }

}