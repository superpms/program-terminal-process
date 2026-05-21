# program-terminal-process 文档入口

这组文档面向开发者，说明 `superpms/program-terminal-process` 如何给 terminal command 增加旁路进程监控、如何把状态写入 Redis、公开类/API 是什么，以及排查时应从哪里进入。

## 先读

1. [接入与注册](guide/setup.md)
2. [监控一个命令](guide/monitor-command.md)
3. [Redis 键空间协议](reference/redis-keyspace.md)
4. [源码清单与包入口](reference/source-inventory.md)

## 按问题读

- 要确认包是否挂载: [接入与注册](guide/setup.md)
- 要让一个 command 被监控: [监控一个命令](guide/monitor-command.md)
- 要查公开注解、service、driver、module: [公开 API](reference/api.md)
- 要核对 composer、bin、config、src 清单: [源码清单与包入口](reference/source-inventory.md)
- 要查 Redis key/value/TTL 结构: [Redis 键空间协议](reference/redis-keyspace.md)
- 要理解从业务命令到 monitor 进程的完整链路: [运行流程](internals/runtime-flow.md)
- 要替换存储或改监控行为: [扩展点](internals/extension-points.md)
- 要排查没有心跳、状态过期或停止失败: [限制与排查](operations/troubleshooting.md)

## 不在这里读

- 平台进程管理 HTTP 接口属于 server 项目业务代码，不属于本 composer 包 API。
- Redis 连接池和通用 Redis helper 见 `program-redis` 文档。
- 业务命令的实际业务逻辑由各业务端 command 自己负责。
