<?php

declare(strict_types=1);

namespace Scheel\TaskFlow;

use RuntimeException;
use Scheel\TaskFlow\Exceptions\TaskFlowException;

use function array_key_exists;
use function is_int;

final class Context
{
    public ?TaskFlowException $error = null;

    /**
     * @param  array<array-key, mixed>  $data
     *
     * @internal
     */
    public function __construct(private readonly TaskManager $manager, private array $data = []) {}

    public function setCurrentTask(Task $task): void
    {
        $this->manager->setCurrentTask($task);
    }

    public function skip(): void
    {
        $this->manager->getCurrentTask()->skip();
    }

    public function abort(): void
    {
        $this->manager->getCurrentTask()->fail();
    }

    public function updateTitle(string $title): void
    {
        $this->manager->getCurrentTask()->setTitle($title);
    }

    public function render(): void
    {
        $this->manager->render();
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (! $this->has($key)) {
            return $default;
        }

        return $this->data[$key];
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function increment(string $key, int $amount = 1): void
    {
        if ($this->has($key)) {
            $current = $this->get($key);
            if (! is_int($current)) {
                throw new RuntimeException('Attempt to increment a non-integer value');
            }
            $this->set($key, $current + $amount);
        } else {
            $this->set($key, $amount);
        }
    }
}
