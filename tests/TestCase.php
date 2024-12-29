<?php

declare(strict_types=1);

namespace Scheel\TaskFlow\Tests;

use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;
use Scheel\TaskFlow\Facades\TaskFlow;
use Scheel\TaskFlow\TaskFlowServiceProvider;

class TestCase extends Orchestra
{
    use WithWorkbench;

    public function getEnvironmentSetUp($app): void {}

    /** @return class-string[] */
    protected function getPackageProviders($app): array
    {
        return [
            TaskFlowServiceProvider::class,
        ];
    }

    /** @return array<string, class-string> */
    protected function getPackageAliases($app): array
    {
        return [
            'TaskFlow' => TaskFlow::class,
        ];
    }
}
