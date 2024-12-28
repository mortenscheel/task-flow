<?php

declare(strict_types=1);

namespace Scheel\TaskFlow\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Scheel\TaskFlow\TaskFlowServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName): string => 'Scheel\\TaskFlow\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_skeleton_table.php.stub';
        $migration->up();
        */
    }

    protected function getPackageProviders($app)
    {
        return [
            TaskFlowServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'TaskFlow' => \Scheel\TaskFlow\Facades\TaskFlow::class,
        ];
    }
}
