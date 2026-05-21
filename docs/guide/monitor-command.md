# 监控一个命令

## 添加注解

```php
use pms\annotate\TerminalCommandProcessMonitor;
use pms\app\TerminalCommandApp;

#[TerminalCommandProcessMonitor('daily-settlement', 60)]
class DailySettlementCommand extends TerminalCommandApp
{
    protected string $name = 'daily-settlement';

    public function entry(): void
    {
        while (true) {
            // do work
            sleep(10);
        }
    }
}
```

注解参数:

| 参数 | 类型 | 说明 |
| --- | --- | --- |
| `taskUUID` | string | 服务标识，会进入 Redis 服务维度 |
| `keepAliveInterval` | int | Redis 状态 TTL 和心跳间隔基准 |

`TerminalCommandProcessMonitorService` 要求注解至少有两个参数，否则抛出 `TerminalCommandProcessMonitor 注解 参数错误`。

## 启动后发生什么

业务命令进入 terminal sandbox 后:

1. service 读取 command 类上的 `TerminalCommandProcessMonitor`
2. 取得当前业务命令 pid
3. 创建 runtime 日志目录 `/terminal/log`
4. 调用 `call_php_script()` 启动监控命令

启动命令格式:

```bash
php pms terminal-process-monitor <pid> <taskUUID> <keepAliveInterval>
```

监控进程 pid 文件路径格式:

```text
<runtime>/terminal/log/<pid>.<taskUUID>.monitor.pid
```

## 监控命令行为

`terminal-process-monitor` 接收三个位置参数:

| 参数名 | 说明 |
| --- | --- |
| `p` | 被监控 pid |
| `u` | 服务标识 |
| `expired` | 保持活动间隔，默认 `20` |

命令会:

1. 创建 `TerminalProcessRedisModule`
2. 调用 `start()` 初始化进程地址和开始时间
3. 循环检查 `has_process($pid)`
4. 目标 pid 存活时调用 `heartbeat(6)`
5. 目标 pid 消失时输出结束信息并退出

循环间隔固定为 `sleep(5)`。

## 读取状态

```php
use pms\program\terminalProcess\TerminalProcessRedisDriver;

$serviceAddress = TerminalProcessRedisDriver::createServiceAddress('daily-settlement');
$keys = TerminalProcessRedisDriver::getService($serviceAddress);
$process = TerminalProcessRedisDriver::getProcess($keys[0] ?? '');
```

`getProcess()` 返回 `TerminalProcessRedisModule` 对象或 `null`。
