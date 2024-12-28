<?php

declare(strict_types=1);

namespace Scheel\TaskFlow;

use Illuminate\Support\ServiceProvider;

final class TaskFlowServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        parent::register();
        $this->mergeConfigFrom(__DIR__.'/../config/task-flow.php', 'task-flow');
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
            // $this->publishes([
            //     __DIR__.'/../resources/views' => resource_path('views/scheel/task-flow'),
            // ], 'views');
        }

        // $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'task-flow');
    }
}
