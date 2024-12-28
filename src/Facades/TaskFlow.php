<?php

declare(strict_types=1);

namespace Scheel\TaskFlow\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Scheel\TaskFlow\TaskFlow
 */
final class TaskFlow extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Scheel\TaskFlow\TaskFlow::class;
    }
}
