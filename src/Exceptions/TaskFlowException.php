<?php

declare(strict_types=1);

namespace Scheel\TaskFlow\Exceptions;

use RuntimeException;
use Throwable;

class TaskFlowException extends RuntimeException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
