<?php

declare(strict_types=1);

namespace Scheel\TaskFlow\Tests;

use App;
use Scheel\TaskFlow\Renderer\ConsoleRenderer;
use Scheel\TaskFlow\Renderer\Renderer;
use Symfony\Component\Console\Output\BufferedOutput;

class TestRenderer
{
    public function __construct(
        private readonly BufferedOutput $buffer,
    ) {}

    public static function register(): self
    {
        $instance = new self(new BufferedOutput);
        /** @var array<string, mixed> $config */
        $config = config('task-flow');
        App::instance(Renderer::class, new ConsoleRenderer($instance->buffer, $config));

        return $instance;
    }

    public function getOutput(): string
    {
        return $this->sanitizeOutput($this->buffer->fetch());
    }

    /**
     * Replaces ANSI escape codes with a RESET string.
     */
    private function sanitizeOutput(string $output): string
    {
        return trim(preg_replace("/(\x1b\[\d+[A-Z])+/", 'RESET'.PHP_EOL, $output) ?? '');
    }
}
