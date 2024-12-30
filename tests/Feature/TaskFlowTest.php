<?php

declare(strict_types=1);

namespace Scheel\TaskFlow\Tests\Feature;

use Illuminate\Support\Facades\Config;
use RuntimeException;
use Scheel\TaskFlow\Context;
use Scheel\TaskFlow\Exceptions\TaskFlowException;
use Scheel\TaskFlow\Facades\TaskFlow;
use Scheel\TaskFlow\Renderer\NullRenderer;
use Scheel\TaskFlow\Renderer\Renderer;
use Scheel\TaskFlow\Task;
use Scheel\TaskFlow\Tests\TestRenderer;

use function app;
use function expect;
use function it;
use function Laravel\Prompts\info;

it('fails if no tasks are provided', function (): void {
    TaskFlow::run([]);
})->throws(TaskFlowException::class, 'No tasks provided');

it('can run with NullRenderer', function (): void {
    app()->instance(Renderer::class, new NullRenderer);
    TaskFlow::run([
        Task::make('Task 1', fn (Context $context): null => $context->interactive(fn (): null => null)),
    ]);
    expect(true)->toBeTrue();
});

it('can render tasks correctly', function (array $tasks, string $expected, array $config = []): void {
    Config::set($config);
    $buffered = TestRenderer::register();
    try {
        TaskFlow::run($tasks);
    } catch (TaskFlowException) {
    }
    expect($buffered->getOutput())->toBe(trim($expected));
})->with('rendering');
dataset('rendering', [
    'simple-skip' => fn (): array => [
        [
            Task::make('Task 1', fn (): null => null),
            Task::make('Task 2', fn (Context $ctx): null => $ctx->skip()),
        ],
        '
▶ Task 1
… Task 2
RESET
✓ Task 1
… Task 2
RESET
✓ Task 1
▶ Task 2
RESET
✓ Task 1
⏭ Task 2',
    ],
    'dynamic-title' => fn (): array => [
        [
            Task::make('Foo', fn (Context $ctx): null => $ctx->updateTitle('Bar')),
        ],
        '
▶ Foo
RESET
▶ Bar
RESET
✓ Bar',
    ],
    'nested-abort' => fn (): array => [
        [
            Task::make('Task 1', fn (): null => null),
            Task::make('Task 2', children: [
                Task::make('Task 2.1', fn (): null => null),
                Task::make('Task 2.2', children: [
                    Task::make('Task 2.2.1', fn () => throw new RuntimeException('Whoops!')),
                ]),
            ]),
            Task::make('Task 3', fn (): null => null),
        ],
        '
▶ Task 1
… Task 2
… Task 3
RESET
✓ Task 1
… Task 2
… Task 3
RESET
✓ Task 1
▶ Task 2
  … Task 2.1
  … Task 2.2
… Task 3
RESET
✓ Task 1
▶ Task 2
  ▶ Task 2.1
  … Task 2.2
… Task 3
RESET
✓ Task 1
▶ Task 2
  ✓ Task 2.1
  … Task 2.2
… Task 3
RESET
✓ Task 1
▶ Task 2
  ✓ Task 2.1
  ▶ Task 2.2
    … Task 2.2.1
… Task 3
RESET
✓ Task 1
▶ Task 2
  ✓ Task 2.1
  ▶ Task 2.2
    ▶ Task 2.2.1
… Task 3
RESET
✓ Task 1
▶ Task 2
  ✓ Task 2.1
  ▶ Task 2.2
    ✗ Task 2.2.1
… Task 3
RESET
✓ Task 1
▶ Task 2
  ✓ Task 2.1
  ✗ Task 2.2
    ✗ Task 2.2.1
… Task 3
RESET
✓ Task 1
✗ Task 2
  ✓ Task 2.1
  ✗ Task 2.2
    ✗ Task 2.2.1
… Task 3
RESET
✓ Task 1
✗ Task 2
  ✓ Task 2.1
  ✗ Task 2.2
    ✗ Task 2.2.1
⏭ Task 3',
    ],
    'context-flow' => [
        [
            Task::make('1. Enable task 2', fn (Context $ctx) => $ctx->set('task_2', true)),
            Task::make('2. Disable task 3', function (Context $ctx): void {
                if ($ctx->get('task_2')) {
                    $ctx->set('task_3', 'skip!');
                    $ctx->set('task_4_title', 'Hello world!');
                }
            }),
            Task::make('3. Disable task 3', function (Context $ctx): void {
                if ($ctx->get('task_3') === 'skip!') {
                    $ctx->skip();
                }
            }),
            Task::make('4. Update title', function (Context $ctx): void {
                if ($title = $ctx->get('task_4_title')) {
                    $ctx->updateTitle($title);
                }
            }),
        ],
        '
▶ 1. Enable task 2
… 2. Disable task 3
… 3. Disable task 3
… 4. Update title
RESET
✓ 1. Enable task 2
… 2. Disable task 3
… 3. Disable task 3
… 4. Update title
RESET
✓ 1. Enable task 2
▶ 2. Disable task 3
… 3. Disable task 3
… 4. Update title
RESET
✓ 1. Enable task 2
✓ 2. Disable task 3
… 3. Disable task 3
… 4. Update title
RESET
✓ 1. Enable task 2
✓ 2. Disable task 3
▶ 3. Disable task 3
… 4. Update title
RESET
✓ 1. Enable task 2
✓ 2. Disable task 3
⏭ 3. Disable task 3
… 4. Update title
RESET
✓ 1. Enable task 2
✓ 2. Disable task 3
⏭ 3. Disable task 3
▶ 4. Update title
RESET
✓ 1. Enable task 2
✓ 2. Disable task 3
⏭ 3. Disable task 3
▶ Hello world!
RESET
✓ 1. Enable task 2
✓ 2. Disable task 3
⏭ 3. Disable task 3
✓ Hello world!',
    ],
    'custom-config' => fn (): array => [
        [
            Task::make('Task 1', fn (): null => null),
            Task::make('Task 2', children: [
                Task::make('Task 2.1', fn (Context $ctx): null => $ctx->skip()),
                Task::make('Task 2.2', children: [
                    Task::make('Task 2.2.1', fn (): null => null),
                    Task::make('Task 2.2.2', fn () => throw new RuntimeException('Whoops!')),
                ]),
            ]),
            Task::make('Task 3', fn (): null => null),
        ],
        '
🏃‍♂️️ Task 1
💤 Task 2
💤 Task 3
RESET
🎉 Task 1
💤 Task 2
💤 Task 3
RESET
🎉 Task 1
🏃‍♂️️ Task 2
     💤 Task 2.1
     💤 Task 2.2
💤 Task 3
RESET
🎉 Task 1
🏃‍♂️️ Task 2
     🏃‍♂️️ Task 2.1
     💤 Task 2.2
💤 Task 3
RESET
🎉 Task 1
🏃‍♂️️ Task 2
     ⏩ Task 2.1
     💤 Task 2.2
💤 Task 3
RESET
🎉 Task 1
🏃‍♂️️ Task 2
     ⏩ Task 2.1
     🏃‍♂️️ Task 2.2
          💤 Task 2.2.1
          💤 Task 2.2.2
💤 Task 3
RESET
🎉 Task 1
🏃‍♂️️ Task 2
     ⏩ Task 2.1
     🏃‍♂️️ Task 2.2
          🏃‍♂️️ Task 2.2.1
          💤 Task 2.2.2
💤 Task 3
RESET
🎉 Task 1
🏃‍♂️️ Task 2
     ⏩ Task 2.1
     🏃‍♂️️ Task 2.2
          🎉 Task 2.2.1
          💤 Task 2.2.2
💤 Task 3
RESET
🎉 Task 1
🏃‍♂️️ Task 2
     ⏩ Task 2.1
     🏃‍♂️️ Task 2.2
          🎉 Task 2.2.1
          🏃‍♂️️ Task 2.2.2
💤 Task 3
RESET
🎉 Task 1
🏃‍♂️️ Task 2
     ⏩ Task 2.1
     🏃‍♂️️ Task 2.2
          🎉 Task 2.2.1
          😭 Task 2.2.2
💤 Task 3
RESET
🎉 Task 1
🏃‍♂️️ Task 2
     ⏩ Task 2.1
     😭 Task 2.2
          🎉 Task 2.2.1
          😭 Task 2.2.2
💤 Task 3
RESET
🎉 Task 1
😭 Task 2
     ⏩ Task 2.1
     😭 Task 2.2
          🎉 Task 2.2.1
          😭 Task 2.2.2
💤 Task 3
RESET
🎉 Task 1
😭 Task 2
     ⏩ Task 2.1
     😭 Task 2.2
          🎉 Task 2.2.1
          😭 Task 2.2.2
⏩ Task 3',
        [
            'task-flow.indent' => 5,
            'task-flow.symbols.pending' => '💤',
            'task-flow.symbols.running' => '🏃‍♂️️',
            'task-flow.symbols.completed' => '🎉',
            'task-flow.symbols.failed' => '😭',
            'task-flow.symbols.skipped' => '⏩',
        ],
    ],
    'interactive' => [
        [
            Task::make('Interactive', fn (Context $context): mixed => $context->interactive(function (): void {
                info('Hello world');
            })),
        ],
        '
▶ Interactive
RESET
▶ Interactive
RESET
✓ Interactive',
    ],
]);
