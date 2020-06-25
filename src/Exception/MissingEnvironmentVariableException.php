<?php

declare(strict_types=1);

namespace CIInfo\Exception;

use CIInfo\Exception;

/**
 * Exception thrown when an expected environment variable is missing or is empty.
 */
class MissingEnvironmentVariableException extends Exception
{
    /**
     * The name of the empty/missing environment variable.
     *
     * @var string
     */
    private $environmentVariableName;

    /**
     * Initialize the instance.
     *
     * @param string $notFoundDirectory the name of the empty/missing environment variable
     */
    public function __construct(string $environmentVariableName)
    {
        parent::__construct("The environment variable '{$environmentVariableName}' is missing or is empty");
        $this->environmentVariableName = $environmentVariableName;
    }

    /**
     * Get the name of the empty/missing environment variable.
     */
    public function getEnvironmentVariableName(): string
    {
        return $this->environmentVariableName;
    }
}
