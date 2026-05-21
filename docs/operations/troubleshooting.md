# 限制与排查

## 没有 Redis 进程 key

按顺序检查:

1. `program-terminal-process` 是否被 Composer 加载
2. `bin/autoload.php` 是否成功注册 `terminal-process-monitor`
3. 项目 service 配置是否包含 `TerminalCommandProcessMonitorService`
4. 业务 command 类是否带 `TerminalCommandProcessMonitor(taskUUID, keepAliveInterval)`
5. Redis 配置和 `RDb` 是否可用
6. monitor 进程 pid 文件和日志是否生成在 runtime `/terminal/log`

## Key 很快消失

这通常说明 TTL 到期没有续写。检查:

- 目标 pid 是否仍然存在
- `keepAliveInterval` 是否过短
- monitor 命令是否异常退出
- Redis 写入是否失败

状态 key 过期是设计行为，不需要手工清理。

## 同一服务多条进程

进程地址包含 pid:

```text
process:list:<serviceName>:<pid>
```

所以同一个 `serviceName` 下出现多条记录表示多个进程实例正在运行。是否允许多实例由业务侧或平台进程配置决定，不由本包判断。

## 停止进程相关

本包不提供 stop API。当前 server 项目平台入口读取 `TerminalProcessRedisDriver::getProcess($address)` 后执行 `kill $process->PID`。停止失败时应检查 server 项目的 HTTP handler、系统权限和 pid 是否仍存在。

## 常见异常

| 现象 | 来源 | 处理 |
| --- | --- | --- |
| `TerminalCommandProcessMonitor 注解 参数错误` | service 读取注解参数少于 2 个 | 补齐 `taskUUID` 和 `keepAliveInterval` |
| Redis 连接异常 | `program-redis` / ext-redis / 配置 | 先排查 Redis 包配置 |
| `terminal-process-monitor` 命令不存在 | `bin/autoload.php` 未执行或 hook 不可用 | 检查 Composer autoload files 与 terminal hook |
| `getProcess()` 返回 `null` | Redis key 空或已过期 | 检查心跳、TTL 和 key 地址 |

## 限制

- 监控对象是本机 pid，跨机器进程状态需要由部署和 Redis key 命名策略另外保证。
- service 只识别类上的 `TerminalCommandProcessMonitor` 注解，不会自动监控所有 terminal command。
- monitor 命令使用固定 5 秒循环间隔。
- 当前 Redis value 是模块对象数组 JSON，不是稳定对外业务 DTO；外部展示应通过 driver/module 读取，不要手写解析协议后直接依赖所有字段。
