<?php

namespace pms\program\terminalProcess\command;


use pms\app\TerminalCommandProcessRedisApp;

class TerminalCommandProcessMonitorCommand extends TerminalCommandProcessRedisApp
{
    protected array $validate = [
        'p' => [
            'type' => COMMAND_ARGUMENT_TYPE,
            'des' => 'pid',
            'required' => true
        ],
        'u' => [
            'type' => COMMAND_ARGUMENT_TYPE,
            'des' => '服务标识',
        ],
        'expired' => [
            'type' => COMMAND_ARGUMENT_TYPE,
            'des' => '保持活动间隔',
            'default' => 20
        ]
    ];


    public function entry(): void
    {
        $p = (int)$this->input->getArgument('p');
        $u = $this->input->getArgument('u');
        $expired = (int)$this->input->getArgument('expired');
        $this->taskUUID = $u;
        $this->keepAliveInterval = $expired;
        $this->processStart($p);

        $this->output::writeJsonArray([
            "监听开始" => date("Y-m-d H:i:s", time()),
            "PID" => $p,
            "任务标识" => $u,
            "保持活动间隔" => $expired
        ]);
        while (true) {
            if (has_process($p)) {
                $this->heartbeat(6);
            } else {
                $this->output::writeLn("-----");
                $this->output::writeJsonArray([
                    "监听结束" => date("Y-m-d H:i:s", time()),
                    "任务标识" => $u,
                    "监听进程死亡与" => $expired . "秒内"
                ]);
                die;
            }
            sleep(5);
        }

    }

}