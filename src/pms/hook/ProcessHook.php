<?php

namespace pms\hook;

use pms\contract\HookAppInterface;
use ReflectionClass;

/**
 * 进程包自注册钩子
 */
class ProcessHook implements HookAppInterface
{
    protected static array $container = [];

    /**
     * 挂载进程包定义类，并同步挂载其命令类
     * @param string $definitionClass 提供 definition(): array 的定义类
     */
    public static function mount(string $definitionClass): bool
    {
        if (!class_exists($definitionClass) || !method_exists($definitionClass, 'definition')) {
            return false;
        }

        $definition = $definitionClass::definition();
        if (!is_array($definition)) {
            return false;
        }

        $uuid = trim((string)($definition['uuid'] ?? ''));
        if ($uuid === '') {
            return false;
        }

        foreach (['name', 'description', 'command', 'class'] as $field) {
            if (trim((string)($definition[$field] ?? '')) === '') {
                return false;
            }
        }

        $maxSize = (int)($definition['max_size'] ?? 0);
        if ($maxSize < 1) {
            return false;
        }

        $commandClass = ltrim((string)$definition['class'], '\\');
        if (!class_exists($commandClass)) {
            return false;
        }

        if (class_exists(TerminalCommandHook::class)) {
            if (!TerminalCommandHook::mount($commandClass)) {
                $commands = TerminalCommandHook::audit();
                if (!in_array($commandClass, $commands, true)) {
                    return false;
                }
            }
        }

        $sourcePath = '';
        $reflection = new ReflectionClass($definitionClass);
        $fileName = $reflection->getFileName();
        if (is_string($fileName) && $fileName !== '') {
            $sourcePath = dirname($fileName);
        }

        static::$container[$uuid] = [
            'uuid' => $uuid,
            'name' => (string)$definition['name'],
            'description' => (string)$definition['description'],
            'command' => trim((string)$definition['command']),
            'class' => $commandClass,
            'max_size' => $maxSize,
            'definition_class' => $definitionClass,
            'source_path' => $sourcePath,
            'source_type' => static::sourceType($definitionClass),
            'source_name' => static::sourceName($definitionClass),
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
     * 根据定义类命名空间推导来源类型
     */
    protected static function sourceType(string $definitionClass): string
    {
        if (str_starts_with($definitionClass, 'kits\\')) {
            return 'kit';
        }
        if (str_starts_with($definitionClass, 'app\\')) {
            return 'module';
        }
        if (str_starts_with($definitionClass, 'core\\')) {
            return 'core';
        }
        return '';
    }

    /**
     * 根据定义类命名空间推导来源名称
     */
    protected static function sourceName(string $definitionClass): string
    {
        $parts = explode('\\', $definitionClass);
        if (str_starts_with($definitionClass, 'kits\\')) {
            return trim(($parts[1] ?? '') . '/' . ($parts[2] ?? ''), '/');
        }
        if (str_starts_with($definitionClass, 'app\\')) {
            return (string)($parts[1] ?? '');
        }
        if (str_starts_with($definitionClass, 'core\\')) {
            return 'core';
        }
        return '';
    }
}
