# 扩展点

## 替换存储驱动

`interpreter-terminal` 定义了 `TerminalProcessDriverModuleInterface`。当前包提供 Redis 实现:

```php
class TerminalProcessRedisDriver extends TerminalProcessDriverModule
```

如果要接入其他存储，应新增 driver 实现接口中的方法:

- `createServiceAddress()`
- `createProcessAddress()`
- `getService()`
- `getServices()`
- `getProcess()`
- `active()`

然后新增对应 module，继承 `TerminalProcessModule` 并指定 `$processDriver`。

## 替换命令 app 基类

`TerminalCommandProcessRedisApp` 只做一件事: 创建 `TerminalProcessRedisModule`。如果有新存储，可新增类似 app 基类:

```php
abstract class TerminalCommandProcessXxxApp extends TerminalCommandProcessAppBasic
{
    protected function createProcessModule(string $taskUUID, ?int $pid = null): TerminalProcessModule
    {
        return new TerminalProcessXxxModule($taskUUID, $pid);
    }
}
```

## 改监控触发条件

监控是否拉起由 `TerminalCommandProcessMonitorService` 控制。当前触发条件是 command 类带 `TerminalCommandProcessMonitor` 注解。

可扩展方向:

- 支持额外注解参数
- 过滤某些 command
- 改变 monitor 日志路径
- 改变 `call_php_script()` 启动参数

变更时要保持两个参数的基本协议，否则现有 command 注解会失效。

## 改心跳策略

当前 monitor 命令:

- `sleep(5)`
- 每轮 pid 存活时调用 `heartbeat(6)`
- TTL 使用 `expired` 参数

如果要改变写入频率，优先改 `TerminalCommandProcessMonitorCommand`，不要直接改 Redis key/value 协议。

## 与平台管理入口的边界

本 composer 包只提供进程运行态投影能力。平台上的进程配置、启动数量限制、启动/停止 HTTP 接口由 server 项目实现。扩展这些业务能力时，应在 server 项目对应 command/service/http 层修改，而不是把业务规则写进 composer 包。
