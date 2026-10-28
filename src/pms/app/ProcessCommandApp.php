<?php

namespace pms\app;

/**
 * 由平台管理的常驻进程命令，元数据通过类默认属性读取。
 */
abstract class ProcessCommandApp extends TerminalCommandApp
{
    /** @var string 进程唯一标识，与监控服务标识一致 */
    protected string $uuid = '';

    /** @var int 最大同时运行数量 */
    protected int $maxSize = 1;

    /** @var string 完整启动命令，包含解释器和运行参数 */
    protected string $command = '';

    /** @var string 所属应用分类值 */
    protected string $classify = '';
}
