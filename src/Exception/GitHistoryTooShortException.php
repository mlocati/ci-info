<?php

declare(strict_types=1);

namespace CIInfo\Exception;

use CIInfo\Exception;

/**
 * Exception thrown when the git history is too short (see git clone --depth).
 */
class GitHistoryTooShortException extends Exception
{
    public function __construct()
    {
        parent::__construct('The git history seems too short. You may want to try to increase it (see the --depth option of the git clone command)');
    }
}
