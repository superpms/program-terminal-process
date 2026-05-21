# superpms/program-terminal-process

`program-terminal-process` 是 PMS terminal 命令的进程监控 program 包。它通过注解识别需要监控的 CLI 命令，旁路启动 `terminal-process-monitor` 命令，对目标 pid 做存活检测，并把服务/进程状态按固定键空间写入 Redis。

## 安装与挂载

```bash
composer require superpms/program-terminal-process
```

包通过 `composer.json` 的 `autoload.files` 自动执行 `bin/autoload.php`，将 `pms\program\terminalProcess\command\TerminalCommandProcessMonitorCommand` 注册到 `TerminalCommandHook`。

要让业务命令启动时自动拉起监控进程，项目侧还需要在 service 配置中启用:

```php
\pms\service\TerminalCommandProcessMonitorService::class,
```

当前 server 项目在 `server/core/config/service.php` 中已经注册该 service。

## 快速使用

给 terminal command 类增加注解:

```php
use pms\annotate\TerminalCommandProcessMonitor;
use pms\app\TerminalCommandApp;

#[TerminalCommandProcessMonitor('my-service', 30)]
class MyWorkerCommand extends TerminalCommandApp
{
    public function entry(): void
    {
        while (true) {
            // work
            sleep(5);
        }
    }
}
```

命令启动并进入 terminal sandbox 后，service 会拉起:

```bash
php pms terminal-process-monitor <pid> <taskUUID> <keepAliveInterval>
```

监控命令会持续写 Redis 心跳，直到目标 pid 不存在。

## 主要模块

- `pms\annotate\TerminalCommandProcessMonitor`: 标记需要旁路监控的 terminal command
- `pms\service\TerminalCommandProcessMonitorService`: 在 terminal 生命周期中识别注解并拉起监控进程
- `pms\program\terminalProcess\command\TerminalCommandProcessMonitorCommand`: 实际执行 pid 存活检测和心跳续写的命令
- `pms\program\terminalProcess\TerminalProcessRedisModule`: Redis 存储实现的进程状态模块
- `pms\program\terminalProcess\TerminalProcessRedisDriver`: Redis 键空间、读取、写入和聚合入口
- `pms\app\TerminalCommandProcessRedisApp`: 给监控命令使用的 Redis 进程模块 app 基类

## Docs 导航

- [文档入口](docs/00-index.md)
- [接入与注册](docs/guide/setup.md)
- [监控一个命令](docs/guide/monitor-command.md)
- [公开 API](docs/reference/api.md)
- [Redis 键空间协议](docs/reference/redis-keyspace.md)
- [运行流程](docs/internals/runtime-flow.md)
- [扩展点](docs/internals/extension-points.md)
- [限制与排查](docs/operations/troubleshooting.md)

## 注意事项

- 本包依赖 `superpms/interpreter-terminal` 和 `superpms/program-redis`。
- 自动监控链需要 command hook、service 注册、Redis 配置三者都可用。
- Redis key 的 TTL 就是进程状态的存活边界；看不到 key 可能只是心跳过期。
- 包内注册的是 `terminal-process-monitor` 监控命令，不是业务进程管理后台；后台读取/启动/停止属于 server 项目业务入口。
