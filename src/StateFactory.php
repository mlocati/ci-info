<?php

declare(strict_types=1);

namespace CIInfo;

/**
 * Class that detects the current CI state.
 */
class StateFactory
{
    /**
     * The instance of the class that holds the environment variables.
     *
     * Array keys are the environment variable names, array values are the environment variable values.
     *
     * @var \CIInfo\Env
     */
    private $env;

    /**
     * The instance of the class that manages the CI drivers.
     *
     * @var \CIInfo\DriverList
     */
    private $driversList;

    /**
     * Initialize the instance.
     *
     * @param \CIInfo\Env|null $env the instance of the class that holds the environment variables (if NULL we'll use the current ones)
     * @param \CIInfo\DriverList|null the instance of the class that manages the CI drivers (if NULL we'll build it)
     *
     * @throws \CIInfo\Exception in case of problems
     */
    public function __construct(?Env $env = null, ?DriverList $driverList = null)
    {
        $this->env = $env ?: Env::getCurrent();
        $this->driversList = $driverList ?: new DriverList();
    }

    /**
     * Get the current CI state.
     *
     * @throws \CIInfo\Exception in case of errors
     */
    public function getCurrentState(): State
    {
        $driver = $this->getDriverList()->getDriverForEnvironment($this->getEnv());
        if ($driver === null) {
            throw new Exception\NoApplicableDriverException();
        }

        return $driver->getState($this->getEnv());
    }

    /**
     * Get the instance of the class that manages the CI drivers.
     */
    protected function getDriverList(): DriverList
    {
        return $this->driversList;
    }

    /**
     * Get the instance of the class that holds the environment variables.
     */
    protected function getEnv(): Env
    {
        return $this->env;
    }
}
