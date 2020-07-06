<?php

declare(strict_types=1);

namespace CIInfo\Exception;

/**
 * Exception thrown when the list of environment variables is not complete.
 */
class PushToNewBranchException extends PushWithoutBaseCommitException
{
    public function __construct()
    {
        parent::__construct('This is a branch creation event: no commit range is available.');
    }
}
