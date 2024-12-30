<?php

declare(strict_types=1);

namespace Scheel\TaskFlow;

use Scheel\TaskFlow\Exceptions\TaskFlowException;
use Throwable;

class Task
{
    /** @var ?callable(Context):void */
    private $action;

    private State $state = State::Pending;

    /**
     * @param  callable(Context):void|null  $action
     * @param  Task[]  $children
     */
    public function __construct(
        private string $title,
        ?callable $action = null,
        private readonly array $children = [],
    ) {
        $this->action = $action;
    }

    /** @param  Task[]  $children */
    public static function make(string $title, ?callable $action = null, array $children = []): self
    {
        return new self($title, $action, $children);
    }

    public function getState(): State
    {
        return $this->state;
    }

    public function skip(): void
    {
        if (! $this->state->isFinal()) {
            $this->state = State::Skipped;
            foreach ($this->getDescendants() as $child) {
                $child->skip();
            }
        }
    }

    public function fail(): void
    {
        if (! $this->state->isFinal()) {
            $this->state = State::Failed;
            foreach ($this->getDescendants() as $child) {
                $child->skip();
            }
        }
    }

    /** @return Task[] */
    public function getDescendants(): iterable
    {
        foreach ($this->children as $child) {
            yield $child;
            foreach ($child->getDescendants() as $grandChild) {
                yield $grandChild;
            }
        }
    }

    public function execute(Context $context): void
    {
        if (! $this->action && ! $this->hasChildren()) {
            throw new TaskFlowException('Task has no action and no children');
        }
        $context->setCurrentTask($this);
        $this->state = State::Running;
        $context->render();
        if ($this->action) {
            try {
                call_user_func($this->action, $context);
                if ($this->state === State::Failed) { // @phpstan-ignore-line
                    // Aborted from within the task
                    throw new TaskFlowException("Task '$this->title' failed");
                }
            } catch (TaskFlowException $e) {
                $context->error = $e;
                $context->render();

                return;
            } catch (Throwable $e) {
                $context->error = new TaskFlowException("Task '$this->title' failed", previous: $e);
                $this->fail();
                $context->render();

                return;
            }
            if ($this->state === State::Skipped) { // @phpstan-ignore-line
                $context->render();

                return;
            }
        }

        foreach ($this->children as $child) {
            if ($context->error instanceof TaskFlowException) {
                $child->skip();

                continue;
            }
            $child->execute($context);
        }
        if ($context->error instanceof TaskFlowException) {
            $this->fail();
        } else {
            $this->state = State::Completed;
        }
        $context->render();
    }

    public function hasChildren(): bool
    {
        return $this->children !== [];
    }

    /** @return Task[] */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
