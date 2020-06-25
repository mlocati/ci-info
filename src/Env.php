<?php

declare(strict_types=1);

namespace CIInfo;

use CIInfo\Exception\MissingEnvironmentVariableException;

/**
 * Class that wraps the environment variables.
 */
class Env
{
    /**
     * The current environment,.
     *
     * @var static|null
     */
    private static $curremt;

    /**
     * The list of environment variables (array keys are the environment variable names, array values are the environment variable values).
     *
     * @var array|string[]
     */
    private $variables;

    /**
     * @param array $variables the list of environment variables (array keys are the environment variable names, array values are the environment variable values)
     */
    public function __construct(array $variables)
    {
        $this->variables = $variables;
    }

    /**
     * Get the current environment.
     *
     * @return static
     */
    public static function getCurrent(): self
    {
        if (!isset(static::$curremt)) {
            static::$curremt = new static(getenv());
        }

        return static::$curremt;
    }

    /**
     * Get the value of an environment variable.
     *
     * @param string|null $onUnset what should be returned if the variable is not set
     */
    public function get(string $name, ?string $onUnset = ''): ?string
    {
        if (array_key_exists($name, $this->variables)) {
            return (string) $this->variables[$name];
        }

        return $onUnset;
    }

    /**
     * Get the value of an environment variable.
     *
     * @throws \CIInfo\Exception\MissingEnvironmentVariableException if the environment variable is not set or it's empty
     */
    public function getNotEmpty(string $name): string
    {
        $value = $this->get($name);
        if ($value === '') {
            throw new MissingEnvironmentVariableException($name);
        }

        return $value;
    }

    /**
     * Add/replaces certain variables, creating a new instance of this class.
     *
     * @return static
     */
    public function with(array $overrides): self
    {
        return new static($overrides + $this->variables);
    }

    /**
     * Removed certain variables, creating a new instance of this class.
     *
     * @param string[] $names the names of the variables to be removed
     *
     * @return static
     */
    public function without(array $names): self
    {
        return new static(array_diff_key($this->variables, array_flip($names)));
    }

    /**
     * Update the values of the environment variables by replacing placeholders.
     *
     * @param array $valueMap array keys are the placeholders to be replaced, array values are the placeholder values
     *
     * @return static
     */
    public function withPlaceholders(array $placeholders): self
    {
        $variables = [];
        foreach ($this->variables as $key => $value) {
            $variables[$key] = strtr($value, $placeholders);
        }

        return new static($variables);
    }
}
