<?php

declare(strict_types=1);

return [
    'indent' => 2,
    'symbols' => [
        'pending' => '…',
        'running' => '▶',
        'completed' => '✓',
        'skipped' => '⏭',
        'failed' => '✗',
    ],
    /** @see https://symfony.com/doc/current/console/coloring.html  */
    'colors' => [
        'pending' => 'gray',
        'running' => 'bright-white',
        'completed' => 'green',
        'skipped' => 'yellow',
        'failed' => 'red',
    ],
];
