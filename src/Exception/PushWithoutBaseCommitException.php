<?php

declare(strict_types=1);

namespace CIInfo\Exception;

use CIInfo\Exception;

/**
 * Exception thrown when the base commit if a push event is not available.
 */
abstract class PushWithoutBaseCommitException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
