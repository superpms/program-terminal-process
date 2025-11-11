<?php


use pms\hook\TerminalCommandHook;
use pms\program\terminalProcess\command\TerminalCommandProcessMonitorCommand;

TerminalCommandHook::mount(
    'terminal-process-monitor',
    TerminalCommandProcessMonitorCommand::class
);