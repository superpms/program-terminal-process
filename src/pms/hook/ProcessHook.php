<?php

namespace pms\hook;

use pms\app\ProcessCommandApp;
use pms\contract\HookAppInterface;
use ReflectionClass;

/**
 * 进程包自注册钩子
 */
class ProcessHook implements HookAppInterface
{
    protected static array $container = [];

    /**
     * 通过命令类默认属性注册进程，同时挂载终端命令。
     * @param class-string<ProcessCommandApp> $commandClass 进程命令类
     */
    public static function mount(string $commandClass): bool
    {
        $commandClass = ltrim($commandClass, '\\');
        if (!is_subclass_of($commandClass, ProcessCommandApp::class)) {
            return false;
        }

        $reflection = new ReflectionClass($commandClass);
        if ($reflection->isAbstract()) {
            return false;
        }
        $properties = $reflection->getDefaultProperties();
        foreach (['uuid', 'name', 'description', 'command', 'classify'] as $field) {
            if (trim($properties[$field]) === '') {
                return false;
            }
        }
        if ($properties['maxSize'] < 1) {
            return false;
        }
        if (!TerminalCommandHook::mount($commandClass)
            && !in_array($commandClass, TerminalCommandHook::audit(), true)) {
            return false;
        }

        static::$container[$properties['uuid']] = [
            'uuid' => $properties['uuid'],
            'name' => $properties['name'],
            'description' => $properties['description'],
            'command' => $properties['command'],
            'class' => $commandClass,
            'max_size' => $properties['maxSize'],
            'classify' => $properties['classify'],
            'source_path' => dirname($reflection->getFileName()),
            'source_type' => static::sourceType($commandClass),
            'source_name' => static::sourceName($commandClass),
        ];
        return true;
    }

    /**
     * 是否已注册指定 uuid 的进程包
     */
    public static function has(string $uuid): bool
    {
        return isset(static::$container[trim($uuid)]);
    }

    /**
     * 读取指定 uuid 的进程包定义
     */
    public static function get(string $uuid): ?array
    {
        $uuid = trim($uuid);
        return static::$container[$uuid] ?? null;
    }

    /**
     * 读取全部已注册进程包定义
     */
    public static function all(): array
    {
        return array_values(static::$container);
    }

    /**
     * 审计已注册进程包
     */
    public static function audit(): array
    {
        return static::$container;
    }

    /**
     * 根据命令类命名空间推导来源类型
     */
    protected static function sourceType(string $commandClass): string
    {
        if (str_starts_with($commandClass, 'kits\\')) {
            return 'kit';
        }
        if (str_starts_with($commandClass, 'app\\')) {
            return 'module';
        }
        if (str_starts_with($commandClass, 'core\\')) {
            return 'core';
        }
        return '';
    }

    /**
     * 根据命令类命名空间推导来源名称
     */
    protected static function sourceName(string $commandClass): string
    {
        $parts = explode('\\', $commandClass);
        if (str_starts_with($commandClass, 'kits\\')) {
            return trim(($parts[1] ?? '') . '/' . ($parts[2] ?? ''), '/');
        }
        if (str_starts_with($commandClass, 'app\\')) {
            return (string)($parts[1] ?? '');
        }
        if (str_starts_with($commandClass, 'core\\')) {
            return 'core';
        }
        return '';
    }
}
