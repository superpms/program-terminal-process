<?php

namespace pms\program\terminalProcess\middleware;

use pms\annotate\TerminalCommandBootMonitor;
use pms\app\TerminalCommandInternalBlockingApp;
use pms\contract\LifecycleInterface;
use \ReflectionClass;
class TerminalCommandMiddleware implements LifecycleInterface
{

    public static function start($commandName, $argv, $bootOptions, $commandLIst, ReflectionClass $class, object $obj)
    {
        // 当前class 是否继承自 TerminalCommandInternalBlockingApp
        $needOutsideProcessMonitor = true;
        if(!$obj instanceof TerminalCommandInternalBlockingApp){
            $commandProcessMonitor = annotate_attrs($class, TerminalCommandBootMonitor::class,true);
            if(empty($commandProcessMonitor)){
                $needOutsideProcessMonitor = false;
            }
        }
        if($needOutsideProcessMonitor){
            static::needOutsideProcessMonitor($class,$obj);
        }

    }

    protected static function needOutsideProcessMonitor(ReflectionClass $class,object $obj){

    }

}