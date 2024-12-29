<?php

declare(strict_types=1);

namespace Scheel\TaskFlow\Renderer;

use Scheel\TaskFlow\Task;

interface Renderer
{
    /** @param Task[] $tasks */
    public function render(array $tasks): void;
}
