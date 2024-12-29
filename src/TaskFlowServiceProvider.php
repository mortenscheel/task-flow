<?php

declare(strict_types=1);

namespace Scheel\TaskFlow;

use Illuminate\Support\ServiceProvider;
use Scheel\TaskFlow\Renderer\ConsoleRenderer;
use Scheel\TaskFlow\Renderer\Renderer;
use Symfony\Component\Console\Output\ConsoleOutput;

final class TaskFlowServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/task-flow.php', 'task-flow');
        $this->app->bind(Renderer::class, fn (): ConsoleRenderer => new ConsoleRenderer(
            (new ConsoleOutput)->getErrorOutput()
        ));
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/task-flow.php' => config_path('task-flow.php'),
            ], 'config');
            $this->publishesMigrations([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'migrations');
        }
    }
}
