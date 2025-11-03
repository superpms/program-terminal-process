<?php

namespace pms\program\terminalProcess\contract;

interface TerminalProcessDriverInterface
{
    const space = '';
    public static function getService(string $serviceAddress): array;
    public static function getServices():false|array;
    public static function getProcess(string $processAddress):mixed;
    public static function active(string $processAddress,string $processInfo,int $keepAliveTime):bool;

}