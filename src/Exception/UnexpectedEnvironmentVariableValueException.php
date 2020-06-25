<?php

declare(strict_types=1);

namespace CIInfo\Exception;

use CIInfo\Exception;

/**
 * Exception thrown when an environment variable contains an unexpected value.
 */
class UnexpectedEnvironmentVariableValueException extends Exception
{
    /**
     * The name of the problematic environment variable.
     *
     * @var string
     */
    private $environmentVariableName;

    /**
     * The value of the problematic environment variable.
     *
     * @var string
     */
    private $environmentVariableValue;

    /**
     * Initialize the instance.
     *
     * @param string $environmentVariableName the name of the problematic environment variable
     * @param string $environmentVariableValue the value of the problematic environment variable
     */
    public function __construct(string $environmentVariableName, string $environmentVariableValue)
    {
        parent::__construct("The environment variable '{$environmentVariableName}' contains an unexpected value ('{$environmentVariableValue}')");
        $this->environmentVariableName = $environmentVariableName;
        $this->environmentVariableValue = $environmentVariableValue;
    }

    /**
     * Get the name of the problematic environment variable.
     */
    public function getEnvironmentVariableName(): string
    {
        return $this->environmentVariableName;
    }

    /**
     * Get the value of the problematic environment variable.
     */
    public function getEnvironmentVariableValue(): string
    {
        return $this->environmentVariableValue;
    }
}
