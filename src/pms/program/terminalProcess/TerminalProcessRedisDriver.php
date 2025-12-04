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
            $process = static::getProcess($processAddress);
            if (empty($process)) {
                continue;
            }
            $key = $process->service_name;
            if (!isset($info[$key])) {
                $info[$key] = [];
            }
            $info[$key][] = $process;
        }
        return $info;
    }

    public static function getProcess(string $processAddress): mixed
    {
        $tmp = RDb::get(RDb::clearPrefix($processAddress));
        if (empty($tmp)) {
            return null;
        }
        /**
         * 还原进程容器
         */
        return TerminalProcessRedisModule::restore(json_decode($tmp, true));
    }

    public static function active(string $processAddress, string $processInfo, int $keepAliveTime): bool
    {
        return RDb::set($processAddress, $processInfo, $keepAliveTime);
    }


}