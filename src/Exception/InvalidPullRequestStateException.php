<?php

declare(strict_types=1);

namespace CIInfo\Exception;

use CIInfo\Exception;

/**
 * Exception thrown when the detected pull request state is not valid.
 */
class InvalidPullRequestStateException extends Exception
{
    /**
     * The reason why the detection failed.
     *
     * @var string
     */
    private $reason;

    public function __construct(string $reason)
    {
        parent::__construct("Failed to detect the pull request state:\n{$reason}");
        $this->reason = $reason;
    }

    /**
     * Get the reason why the detection failed.
     */
    public function getReason(): string
    {
        return $this->reason;
    }
}
