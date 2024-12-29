<?php

declare(strict_types=1);

namespace Scheel\TaskFlow\Tests\Feature;

use Scheel\TaskFlow\Context;
use Scheel\TaskFlow\Exceptions\TaskFlowException;
use Scheel\TaskFlow\State;
use Scheel\TaskFlow\Task;

use function it;

it('can execute a task from closure', function (): void {
    $executed = false;
    $task = Task::make('test', function () use (&$executed): void {
        $executed = true;
    });
    $task->execute(app(Context::class));
    expect($task->getState())->toBe(State::Completed)
        ->and($executed)->toBeTrue();
});

it('can execute a task from an invokable class', function (): void {
    $class = new class
    {
        public bool $executed = false;

        public function __invoke(): void
        {
            $this->executed = true;
        }
    };
    $task = Task::make('test', $class);
    $task->execute(app(Context::class));
    expect($task->getState())->toBe(State::Completed)
        ->and($class->executed)->toBeTrue();
});

it('can execute a task with only children', function (): void {
    $child1Executed = false;
    $child2Executed = false;
    $task = Task::make('parent1', children: [
        Task::make('child1', function () use (&$child1Executed): void {
            $child1Executed = true;
        }),
        Task::make('child2', function () use (&$child2Executed): void {
            $child2Executed = true;
        }),
    ]);
    $task->execute(app(Context::class));
    expect($task->getState())->toBe(State::Completed)
        ->and($child1Executed)->toBeTrue()
        ->and($child2Executed)->toBeTrue();
});

it('can execute a task with parent and child task', function (): void {
    $parentExecuted = false;
    $childExecuted = false;
    $task = Task::make('parent1', function () use (&$parentExecuted): void {
        $parentExecuted = true;
    }, children: [
        Task::make('child', function () use (&$childExecuted): void {
            $childExecuted = true;
        }),
    ]);
    $task->execute(app(Context::class));
    expect($task->getState())->toBe(State::Completed)
        ->and($parentExecuted)->toBeTrue()
        ->and($childExecuted)->toBeTrue();
});

it('can skip a task', function (): void {
    $childExecuted = false;
    $task = Task::make('test', function (Context $context): void {
        $context->skip();
    }, children: [
        Task::make('child', function () use (&$childExecuted): void {
            $childExecuted = true;
        }),
    ]);
    $task->execute(app(Context::class));
    expect($task->getState())->toBe(State::Skipped)
        ->and($childExecuted)->toBeFalse();
});

it('fails if there are no action or children', function (): void {
    $task = Task::make('test');
    $task->execute(app(Context::class));
})->throws(TaskFlowException::class, 'Task has no action and no children');

it('can manually fail a task', function (): void {
    $childExecuted = false;
    $grandGrandChild = Task::make('grandgrandchild', fn (): null => null);
    $grandChild = Task::make('grandchild', fn (): null => null, [$grandGrandChild]);
    $failingChild = Task::make('will fail', fn (Context $context) => $context->abort(), [$grandChild]);
    $nextChild = Task::make('will not run', function () use (&$childExecuted): void {
        $childExecuted = true;
    });
    $task = Task::make('test', children: [$failingChild, $nextChild]);
    $task->execute(app(Context::class));
    expect($task->getState())->toBe(State::Failed)
        ->and($failingChild->getState())->toBe(State::Failed)
        ->and($grandChild->getState())->toBe(State::Skipped)
        ->and($grandGrandChild->getState())->toBe(State::Skipped)
        ->and($nextChild->getState())->toBe(State::Skipped)
        ->and($childExecuted)->toBeFalse();
});
