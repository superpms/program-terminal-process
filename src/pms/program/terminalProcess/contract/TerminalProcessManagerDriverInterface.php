<?php

namespace pms\program\terminalProcess\contract;

interface TerminalProcessManagerDriverInterface
{

    /**
     * 命名空间
     */
    const space = "";

    /**
     * 获取驱动
     * @return TerminalProcessDriverInterface
     */
    static function createProcessDriver(): TerminalProcessDriverInterface;


    /**
     * 创建服务地址
     * @param string $serviceName
     * @return string
     */
    public static function createServiceAddress(string $serviceName): string;


    /**
     * 创建进程地址
     * @param string $serviceName
     * @param string $pid
     * @return string
     */
    public static function createProcessAddress(string $serviceName, string $pid): string;


    /**
     * 获取指定服务
     * @param string $serviceName
     * @return array
     */
    public static function getService(string $serviceName): array;


    /**
     * 获取所有服务
     * @return false|array
     */
    public static function getServices(): false|array;


    /**
     * 获取指定进程
     * @param string $serviceName
     * @param string $pid
     * @return mixed
     */
    public static function getProcess(string $serviceName, string $pid): mixed;
}