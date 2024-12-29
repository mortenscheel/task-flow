<?php

declare(strict_types=1);

namespace Scheel\TaskFlow\Renderer;

final class NullRenderer implements Renderer
{
    public function render(array $tasks): void
    {
        // Do nothing
    }
}
