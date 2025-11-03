<?php


\pms\hook\TerminalCommandHook::mount(
    'terminal-process-monitor',
    \pms\program\terminalProcess\command\TerminalCommandProcessMonitorCommand::class
);