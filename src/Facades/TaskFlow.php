<?php

declare(strict_types=1);

namespace Scheel\TaskFlow\Facades;

use Illuminate\Support\Facades\Facade;
use Scheel\TaskFlow\TaskManager;

/**
 * @see TaskManager
 */
final class TaskFlow extends Facade
{
    protected static $cached = false;

    protected static function getFacadeAccessor(): string
    {
        return TaskManager::class;
    }
}
