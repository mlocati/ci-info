<?php

declare(strict_types=1);

namespace CIInfo\Exception;

use CIInfo\Exception;

/**
 * Exception thrown when the execution of a git command failed.
 */
class GitFailedException extends Exception
{
    /**
     * The git command that failed.
     *
     * @var string
     */
    private $gitCommand;

    /**
     * The failure description.
     *
     * @var string
     */
    private $failureDescription;

    /**
     * Initialize the instance.
     *
     * @param string $gitCommand the git command that failed
     * @param string $failureDescription the failure description
     */
    public function __construct(string $gitCommand, string $failureDescription)
    {
        parent::__construct("Error executing the '{$gitCommand}' git command: {$failureDescription}");
        $this->gitCommand = $gitCommand;
        $this->failureDescription = $failureDescription;
    }

    /**
     * Get the git command that failed.
     */
    public function getGitCommand(): string
    {
        return $this->gitCommand;
    }

    /**
     * Get the failure description.
     */
    public function getFailureDescription(): string
    {
        return $this->failureDescription;
    }
}
