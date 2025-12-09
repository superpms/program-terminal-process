<?php


use pms\hook\TerminalCommandHook;
use pms\program\terminalProcess\command\TerminalCommandProcessMonitorCommand;

TerminalCommandHook::mount(
    TerminalCommandProcessMonitorCommand::class
);