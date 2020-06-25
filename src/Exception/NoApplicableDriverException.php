<?php

declare(strict_types=1);

namespace CIInfo\Exception;

use CIInfo\Exception;

/**
 * Exception thrown when no applicable CI driver has been found for the current environment.
 */
class NoApplicableDriverException extends Exception
{
    public function __construct()
    {
        parent::__construct('No applicable CI driver has been found for the current environment');
    }
}
