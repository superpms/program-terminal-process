# Redis 键空间协议

## Key 格式

`TerminalProcessRedisDriver::space` 固定为:

```text
process:list:
```

服务地址:

```text
process:list:<serviceName>:
```

进程地址:

```text
process:list:<serviceName>:<pid>
```

如果 Redis 配置启用了 prefix，底层 ext-redis 会自动给真实 Redis key 加 prefix。包内读取进程时会对 scan 得到的 key 调用 `RDb::clearPrefix()`。

## Value 结构

`TerminalProcessModule::active()` 写入:

```php
json_encode($this->toArray(), 320)
```

对象字段来自 `TerminalProcessModule`。该模块继承 `OptionsAccessCfg`，实际 `toArray()` 输出键会被转为大写:

| 字段 | 说明 |
| --- | --- |
| `SPACE` | key 前缀空间 |
| `SERVICE_NAME` | 服务标识，即注解的 `taskUUID` 或监控命令的 `u` |
| `SERVICE_ADDRESS` | 服务地址 |
| `KEEP_ALIVE_INTERVAL` | TTL/心跳间隔 |
| `PID` | 被监控 pid |
| `ADDRESS` | 进程地址 |
| `START_TIME` | 监控开始时间 |
| `ACTIVE_TIME` | 最近一次实际写入心跳时间 |

恢复时，`TerminalProcessRedisDriver::getProcess()` 会 JSON 解码并调用:

```php
TerminalProcessRedisModule::restore($data)
```

## TTL 语义

`TerminalProcessRedisDriver::active()` 本质是:

```php
RDb::set($processAddress, $processInfo, $keepAliveTime);
```

因此 Redis key 的过期时间就是进程状态的存活边界。目标 pid 死亡后，监控命令退出，不再续写；旧 key 会在 TTL 到期后自然消失。

## 心跳阈值

`heartbeat($threshold)` 的逻辑:

```text
nextTime = active_time + keep_alive_interval - threshold
nextTime > now: 不写 Redis
nextTime <= now: active()
```

监控命令固定调用 `heartbeat(6)`，循环间隔是 5 秒。这避免每轮循环都写 Redis。

## 聚合读取

- `getService($serviceAddress)` 扫描 `$serviceAddress . "*"`，返回 key 列表
- `getServices()` 扫描 `process:list:*`，逐个恢复进程后按 `service_name` 属性分组；属性读取会映射到内部的 `SERVICE_NAME`
- `getProcess($processAddress)` 读取单个 key，空值返回 `null`

这个包没有单独的 Redis 服务列表 key；服务视图是扫描进程 key 后聚合出来的。
