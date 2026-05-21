# 运行流程

## 包加载阶段

1. Composer 加载 `bin/autoload.php`
2. `TerminalCommandHook::mount(TerminalCommandProcessMonitorCommand::class)`
3. terminal 命令系统获得 `terminal-process-monitor` 命令

## 业务命令启动阶段

1. 用户或系统启动某个 `TerminalCommandApp`
2. terminal sandbox booted
3. `TerminalCommandProcessMonitorService::entry()` 被 terminal lifecycle 调用
4. service 通过 `class_annotate_attrs()` 查找 `TerminalCommandProcessMonitor`
5. 没有注解则不做任何事
6. 有注解则读取 `[taskUUID, keepAliveInterval]`
7. service 使用当前 pid 拉起外部 monitor 进程

## Monitor 命令阶段

1. `terminal-process-monitor` 解析 `p`、`u`、`expired`
2. 设置 `$taskUUID = $u`
3. 设置 `$keepAliveInterval = $expired`
4. `processStart($p)` 创建 `TerminalProcessRedisModule`
5. `TerminalProcessModule::start()` 设置进程地址
6. 循环检查目标 pid
7. pid 存活则 `heartbeat(6)`
8. pid 不存在则输出结束信息并退出

## 写入 Redis 阶段

`heartbeat()` 判断需要续写时调用 `active()`:

```text
TerminalProcessRedisModule
  -> TerminalProcessModule::active()
  -> TerminalProcessRedisDriver::active()
  -> RDb::set(address, json, keepAliveInterval)
```

每次实际写入都会刷新 Redis TTL。

## 平台读取阶段

server 项目里的平台 process 接口使用本包 driver:

- `TerminalProcessRedisDriver::createServiceAddress($uuid)`
- `TerminalProcessRedisDriver::getService($serviceAddress)`
- `TerminalProcessRedisDriver::getProcess($address)`

这些接口把 Redis 投影作为运行态状态来源；进程配置、最大启动数量、启动命令等仍属于 server 项目自己的业务模型。
