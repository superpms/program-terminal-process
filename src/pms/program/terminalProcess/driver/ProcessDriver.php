<?php

namespace pms\program\terminalProcess\driver;

use pms\program\terminalProcess\contract\TerminalProcessDriverInterface;

abstract class ProcessDriver implements TerminalProcessDriverInterface
{


    /**
     * 创建服务地址
     * @param string $serviceName
     * @return string
     */
    public static function createServiceAddress(string $serviceName): string
    {
        return static::space . $serviceName . ':';
    }

    /**
     * 创建进程地址
     * @param string $serviceName
     * @param string $pid
     * @return string
     */
    public static function createProcessAddress(string $serviceName, string $pid): string
    {
        return static::createServiceAddress($serviceName) . $pid;
    }
}