<?php

declare(strict_types=1);

namespace Scheel\TaskFlow;

use Scheel\TaskFlow\Exceptions\TaskFlowException;
use Scheel\TaskFlow\Renderer\Renderer;
use Throwable;

class TaskManager
{
    /** @var Task[] */
    private array $tasks = [];

    private Task $currentTask;

    public function __construct(
        private readonly Renderer $renderer,
    ) {}

    public function setCurrentTask(Task $task): void
    {
        $this->currentTask = $task;
    }

    public function getCurrentTask(): Task
    {
        return $this->currentTask;
    }

    public function render(): void
    {
        $this->renderer->render($this->tasks);
    }

    /**
     * @param  Task[]  $tasks
     * @param  array<array-key, mixed>  $contextData
     *
     * @throws Throwable
     */
    public function run(
        array $tasks,
        array $contextData = [],
    ): Context {
        if ($tasks === []) {
            throw new TaskFlowException('No tasks provided');
        }
        $this->tasks = $tasks;
        $context = new Context($this, $contextData);
        /** @var ?TaskFlowException $error */
        $error = null;
        foreach ($tasks as $task) {
            if ($error) {
                // Skip all tasks after the first error
                $task->skip();

                continue;
            }
            $task->execute($context);
            if ($context->error instanceof TaskFlowException) {
                $error = $context->error;
            }
        }
        $this->render();
        if ($error) {
            throw $error;
        }

        return $context;
    }
}
