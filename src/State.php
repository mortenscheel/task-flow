<?php

declare(strict_types=1);

namespace Scheel\TaskFlow;

enum State: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Completed = 'completed';
    case Skipped = 'skipped';
    case Failed = 'failed';

    public function isFinal(): bool
    {
        return match ($this) {
            self::Completed, self::Skipped, self::Failed => true,
            default => false,
        };
    }
}
