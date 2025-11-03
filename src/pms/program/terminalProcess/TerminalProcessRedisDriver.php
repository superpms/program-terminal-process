<?php

namespace pms\program\terminalProcess;

use pms\facade\RDb;
use pms\module\TerminalProcessDriverModule;

class TerminalProcessRedisDriver extends TerminalProcessDriverModule
{
    const space = 'process:list:';

    public static function getService(string $serviceAddress): array
    {
        return RDb::scanX($serviceAddress . "*");
    }

    /**
     * @return false|array
     */
    public static function getServices(): false|array
    {
        $processAddressAll = RDb::scanX(static::space . '*');
        if ($processAddressAll === false) {
            return false;
        }
        $info = [];
        foreach ($processAddressAll as $processAddress) {
            $tmp = RDb::get($processAddress);
            if (empty($tmp)) {
                continue;
            }
            /**
             * 还原进程容器
             */
            $process = TerminalProcessRedisModule::restore(json_decode($tmp, true));
            $key = $process->service_name;
            if (!isset($info[$key])) {
                $info[$key] = [];
            }
            $info[$key][] = $tmp;
        }
        return $info;
    }

    public static function getProcess(string $processAddress): mixed
    {
        return RDb::get($processAddress);
    }

    public static function active(string $processAddress, string $processInfo, int $keepAliveTime): bool
    {
        return RDb::set($processAddress, $processInfo, $keepAliveTime);
    }


}