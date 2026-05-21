# 公开 API

## TerminalCommandProcessMonitor

命名空间: `pms\annotate`

```php
#[TerminalCommandProcessMonitor(string $taskUUID, int $keepAliveInterval)]
```

这是 PHP attribute，用于标记 terminal command 需要旁路进程监控。构造函数本身不保存属性；service 通过 `ReflectionAttribute::getArguments()` 读取参数。

## TerminalCommandProcessMonitorService

命名空间: `pms\service`

关键成员:

| 成员 | 值 | 说明 |
| --- | --- | --- |
| `$lifecycle` | `LIFECYCLE_SANDBOX_BOOTED` | terminal sandbox booted 后执行 |
| `$hookClass` | `TerminalLifecycleHook::class` | 接入 terminal lifecycle |
| `entry(...)` | static | 检查 command 类注解 |

命中注解后会调用受保护方法 `needOutsideProcessMonitor()`，并通过 `call_php_script()` 拉起监控命令。

## TerminalCommandProcessMonitorCommand

命名空间: `pms\program\terminalProcess\command`

命令名:

```text
terminal-process-monitor
```

参数:

| 参数 | 类型 | 必填 | 默认 | 说明 |
| --- | --- | --- | --- | --- |
| `p` | argument | 是 | 无 | 被监控 pid |
| `u` | argument | 否 | 无 | 服务标识 |
| `expired` | argument | 否 | `20` | 保持活动间隔 |

`entry()` 会设置 `$taskUUID`、`$keepAliveInterval`，调用 `processStart($p)`，之后循环心跳。

## TerminalCommandProcessRedisApp

命名空间: `pms\app`

继承 `interpreter-terminal` 的 `TerminalCommandProcessAppBasic`，实现:

```php
protected function createProcessModule(string $taskUUID, ?int $pid = null): TerminalProcessModule
```

返回 `TerminalProcessRedisModule`。

## TerminalProcessRedisModule

命名空间: `pms\program\terminalProcess`

继承 `pms\module\TerminalProcessModule`，指定:

```php
protected string $processDriver = TerminalProcessRedisDriver::class;
```

继承可用方法:

| 方法 | 说明 |
| --- | --- |
| `__construct(string $serviceName, ?int $pid = null)` | 初始化服务名、pid、地址基础信息 |
| `setKeepAliveInterval(int $keepAliveInterval)` | 设置心跳 TTL |
| `start()` | 设置 `start_time` 和进程地址 |
| `active()` | 写入 Redis 状态 |
| `heartbeat(int $threshold = 3)` | 接近过期时才续写 |
| `restore(array $data)` | 从数组恢复模块对象 |

## TerminalProcessRedisDriver

命名空间: `pms\program\terminalProcess`

| 方法/常量 | 说明 |
| --- | --- |
| `space = 'process:list:'` | Redis key 固定前缀 |
| `createServiceAddress(string $serviceName)` | 继承自 driver module，生成服务地址 |
| `createProcessAddress(string $serviceName, string $pid)` | 继承自 driver module，生成进程地址 |
| `getService(string $serviceAddress)` | 扫描某服务下的进程 key |
| `getServices()` | 扫描全部进程并按服务名聚合 |
| `getProcess(string $processAddress)` | 读取并恢复单个进程 |
| `active(string $processAddress, string $processInfo, int $keepAliveTime)` | 写入状态并设置 TTL |
