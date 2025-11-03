<?php

namespace pms\service;

use pms\annotate\TerminalCommandProcessMonitor;
use pms\app\ServiceApp;
use pms\facade\Path;
use ReflectionAttribute;
use ReflectionClass;

class TerminalCommandProcessMonitorService extends ServiceApp
{
    public static string $lifecycle = LIFECYCLE_SANDBOX_BOOTED;

    public static string $hookClass = \pms\hook\TerminalLifecycleHook::class;

    public static function start($commandName, $argv, $bootOptions, $commandLIst, ReflectionClass $class, $obj)
    {
        $needOutsideProcessMonitor = annotate_attrs($class, TerminalCommandProcessMonitor::class, true);
        if (!empty($needOutsideProcessMonitor)) {
            static::needOutsideProcessMonitor($needOutsideProcessMonitor, $class);
        }
    }

    protected static function needOutsideProcessMonitor(ReflectionAttribute $attribute, ReflectionClass $class): void
    {
        $arg = $attribute->getArguments();
        if (count($arg) < 2) {
            throw new \Exception("TerminalCommandProcessMonitor 注解 参数错误");
        }
        $taskUUID = $arg[0];
        $keepAliveInterval = $arg[1];
        $root = Path::getRoot();
        $myPid = getmypid();
        $logPath = Path::getRuntime('/terminal/log');
        dir_create($logPath);
        call_php_script($root, "php pms terminal-process-monitor {$myPid} {$taskUUID} {$keepAliveInterval}", "{$logPath}/{$myPid}.$taskUUID.monitor.pid");
    }

}