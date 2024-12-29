<?php

declare(strict_types=1);

namespace Scheel\TaskFlow\Renderer;

use RuntimeException;
use Scheel\TaskFlow\State;
use Scheel\TaskFlow\Task;
use Symfony\Component\Console\Cursor;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

use function config;
use function mb_strimwidth;
use function sprintf;

final class ConsoleRenderer implements Renderer
{
    private readonly Terminal $terminal;

    private readonly Cursor $cursor;

    private ?string $previousMessage = null;

    private readonly int $indent;

    /** @var array<string, string> */
    private array $symbols;

    /** @var array<string, string> */
    private array $colors;

    public function __construct(
        private readonly OutputInterface $output,

    ) {
        $this->terminal = new Terminal;
        $this->cursor = new Cursor($this->output);
        $this->indent = config('task-flow.indent'); //@phpstan-ignore-line
        $this->symbols = config('task-flow.symbols'); //@phpstan-ignore-line
        $this->colors = config('task-flow.colors'); //@phpstan-ignore-line
    }

    public function render(array $tasks): void
    {
        $message = '';
        foreach ($tasks as $task) {
            $message .= $this->formatTask($task);
        }
        if ($this->previousMessage !== null && $this->previousMessage !== '' && $this->previousMessage !== '0') {
            if ($this->previousMessage === $message) {
                return;
            }
            $this->reset($this->previousMessage);
        }
        $this->output->write($message);
        $this->previousMessage = $message;
    }

    private function formatTask(Task $task, int $level = 0, bool $failed = false): string
    {
        if ($failed || $task->getState() === State::Failed) {
            $failed = true;
        }
        $indent = str_repeat(' ', $level * $this->indent);
        $state = $task->getState();
        /** @var string $symbol */
        $symbol = $this->symbols[$state->value] ?? throw new RuntimeException('Invalid state');
        /** @var string $color */
        $color = $this->colors[$state->value] ?? throw new RuntimeException('Invalid state');
        $title = $this->truncateTitle($task->getTitle(), $level);

        $formatted = sprintf(
            '%s<fg=%s>%s %s</>%s',
            $indent,
            $color,
            $symbol,
            $title,
            PHP_EOL
        );
        if ($failed || $task->getState() === State::Running) {
            foreach ($task->getChildren() as $child) {
                $formatted .= $this->formatTask($child, $level + 1, $failed);
            }
        }

        return $formatted;
    }

    private function reset(string $previousMessage): void
    {
        $lineCount = substr_count($previousMessage, PHP_EOL);
        for ($i = 0; $i < $lineCount; $i++) {
            $this->cursor->moveUp();
            $this->cursor->clearLine();
        }
    }

    private function truncateTitle(string $title, int $level): string
    {
        $width = $this->terminal->getWidth() - $level * 2 + 2;

        return mb_strimwidth($title, 0, $width);
    }
}
