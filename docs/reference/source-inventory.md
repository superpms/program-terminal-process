# 源码清单与包入口

本文记录 `superpms/program-terminal-process` 的包级入口、自动加载声明和一方源码文件，作为功能覆盖核查的基准。

## composer.json

| 项 | 值 |
| --- | --- |
| `autoload.files` | `bin/autoload.php` |
| `autoload.psr-4` | `pms\\` -> `src/pms/` |
| `extra.pms` | 无 |
| `bin` | 无 |

## bin 文件

| 文件 | 作用 |
| --- | --- |
| `bin/autoload.php` | Composer files 入口，通过 `TerminalCommandHook::mount()` 注册 `TerminalCommandProcessMonitorCommand` |

## resource/config.php

无。本包依赖项目侧 service 注册和 `program-redis` 的 `config('redis')`，不投影独立配置。

## src 一方源码

| 文件 | 公开功能面 |
| --- | --- |
| `src/pms/annotate/TerminalCommandProcessMonitor.php` | PHP Attribute；用于标记 terminal command 需要旁路进程监控，参数为 `taskUUID` 和 `keepAliveInterval` |
| `src/pms/service/TerminalCommandProcessMonitorService.php` | lifecycle service；在 `LIFECYCLE_SANDBOX_BOOTED` 检查注解并调用 `terminal-process-monitor` 监控当前 pid |
| `src/pms/app/TerminalCommandProcessRedisApp.php` | Redis 版 terminal process app 基类；创建 `TerminalProcessRedisModule` |
| `src/pms/program/terminalProcess/TerminalProcessRedisDriver.php` | Redis 存储 driver；定义 `process:list:` 键空间，提供服务扫描、进程恢复和状态续写 |
| `src/pms/program/terminalProcess/TerminalProcessRedisModule.php` | Redis 版进程状态模块；指定 `TerminalProcessRedisDriver` |
| `src/pms/program/terminalProcess/command/TerminalCommandProcessMonitorCommand.php` | `terminal-process-monitor` 命令；监控目标 pid，循环心跳，目标进程死亡后退出 |

## 覆盖入口

- 接入、命令注册和 service 注册见 [接入与注册](../guide/setup.md)。
- 给业务命令添加监控见 [监控一个命令](../guide/monitor-command.md)。
- 注解、service、command、app、module、driver 见 [公开 API](api.md)。
- Redis key/value/TTL 协议见 [Redis 键空间协议](redis-keyspace.md)。
- 完整运行链见 [运行流程](../internals/runtime-flow.md)。
- 替换 driver、app 或心跳策略见 [扩展点](../internals/extension-points.md)。
