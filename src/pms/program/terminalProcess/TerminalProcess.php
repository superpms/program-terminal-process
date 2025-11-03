<?php

namespace pms\program\terminalProcess;

use pms\CfgOptions;
use pms\program\terminalProcess\contract\TerminalProcessDriverInterface;
use pms\program\terminalProcess\driver\ProcessRedisDriver;

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
class TerminalProcess extends CfgOptions
{

    /**
     * @var TerminalProcessDriverInterface $processDriver
     */
    protected string $processDriver = ProcessRedisDriver::class;

    public function __construct(string $serviceName, int $pid = null)
    {
        $this->space = $this->processDriver::space;
        $this->service_name = $serviceName;
        $this->service_address = $this->processDriver::createServiceAddress($serviceName);
        $this->keep_alive_interval = 20;
        $this->active_time = 0;
        $this->start_time = 0;
        if ($pid !== null) {
            $this->pid = $pid;
        } else {
            $this->pid = getmypid();
        }
    }

    public function start(): static
    {
        if ($this->start_time === 0) {
            $this->start_time = time();
            $this->address = $this->processDriver::createProcessAddress($this->service_name, $this->pid);
        }
        return $this;
    }

    public function active(): bool
    {
        $this->active_time = time();
        return $this->processDriver::active($this->address, json_encode($this->toArray(), 320),$this->keep_alive_interval);
    }

    public static function restore(array $data): static
    {
        $terminalProcess = new static($data['SERVICE_NAME'], $data['PID']);
        foreach ($data as $k => $v){
            $terminalProcess->$k = $v;
        }
        return $terminalProcess;
    }

}