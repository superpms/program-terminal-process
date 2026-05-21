# 接入与注册

## Composer 安装

```bash
composer require superpms/program-terminal-process
```

`composer.json` 中的依赖:

- PHP `>=8.1`
- `superpms/interpreter-terminal`
- `superpms/program-redis`

自动加载入口:

```json
{
  "autoload": {
    "files": ["bin/autoload.php"],
    "psr-4": {
      "pms\\": "src/pms/"
    }
  }
}
```

## 命令注册

`bin/autoload.php` 执行:

```php
TerminalCommandHook::mount(
    TerminalCommandProcessMonitorCommand::class
);
```

因此包被 Composer 加载后，会向 terminal 命令系统注册 `terminal-process-monitor`。

## Service 注册

自动拉起监控进程不是在 `bin/autoload.php` 中完成的，而是由 `TerminalCommandProcessMonitorService` 完成。项目侧需要把它放进 service 配置:

```php
return [
    \pms\service\TerminalCommandProcessMonitorService::class,
];
```

该 service 的关键属性:

- `$lifecycle = LIFECYCLE_SANDBOX_BOOTED`
- `$hookClass = TerminalLifecycleHook::class`

也就是说，它在 terminal sandbox booted 阶段检查当前 command 类是否带有监控注解。

## Redis 依赖

进程状态写入 Redis，因此项目侧还必须确保 `program-redis` 已接入并且 `config('redis')` 可用。监控包不会自行创建 Redis 配置。

## 当前 server 项目接入点

当前 server 项目在 `server/core/config/service.php` 中注册了:

```php
\pms\service\TerminalCommandProcessMonitorService::class,
```

平台 process 接口通过 `TerminalProcessRedisDriver` 读取、启动和停止进程状态，但这些接口不是本包本身的一部分。
