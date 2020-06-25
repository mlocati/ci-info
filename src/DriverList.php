<?php

declare(strict_types=1);

namespace CIInfo;

use Generator;

/**
 * Class that manages the list of CI drivers.
 */
class DriverList
{
    /**
     * The list of the registered drivers.
     *
     * Array keys are the driver handles, array values are the driver instances.
     *
     * @var \CIInfo\Driver[]|array
     */
    private $drivers = [];

    /**
     * Initialize the instance.
     *
     * @param bool $registerBuiltinDrivers should we register the drivers found in the Driver directory?
     *
     * @throws \CIInfo\Exception in case of problems
     */
    public function __construct(bool $registerBuiltinDrivers = true)
    {
        if ($registerBuiltinDrivers === true) {
            $this->registerBuiltinDrivers();
        }
    }

    /**
     * Get the list of the registered drivers.
     *
     * @return \CIInfo\Driver[] array keys are the driver handles, array values are the driver instances
     */
    public function getDrivers(): array
    {
        return $this->drivers;
    }

    /**
     * Get the driver with a specific handle.
     *
     * @param string $handle
     *
     * @return \CIInfo\Driver|null return NULL if there's no driver with the specified handle
     */
    public function getDriverByHandle(string $handle): ?Driver
    {
        return isset($this->drivers[$handle]) ? $this->drivers[$handle] : null;
    }

    /**
     * Register a new driver.
     *
     * @throws \CIInfo\Exception\DuplicatedDriverHandleException if another driver with the same handle is already registered
     *
     * @return $this
     */
    public function registerDriver(Driver $driver): self
    {
        $handle = $driver->getHandle();
        if (isset($this->drivers[$handle])) {
            throw new Exception\DuplicatedDriverHandleException($handle);
        }
        $this->drivers[$handle] = $driver;

        return $this;
    }

    /**
     * Register the drivers found in the built-in Driver directory.
     *
     * @throws \CIInfo\Exception\DirectoryNotFoundException if the built-in drivers directory doesn't exist
     * @throws \CIInfo\Exception\DirectoryNotReadableException if the built-in drivers directory is not readable
     * @throws \CIInfo\Exception\DuplicatedDriverHandleException in case of duplicated driver handles
     *
     * @return $this
     */
    public function registerBuiltinDrivers(): self
    {
        return $this->registerDriversInDirectory(__DIR__ . '/Driver', 'CIInfo\\Driver');
    }

    /**
     * Register the drivers found in a Driver directory.
     *
     * @throws \CIInfo\Exception\DirectoryNotFoundException if $path isn't an existing directory
     * @throws \CIInfo\Exception\DirectoryNotReadableException if $path is not readable
     * @throws \CIInfo\Exception\DuplicatedDriverHandleException in case of duplicated driver handles
     *
     * @return $this
     */
    public function registerDriversInDirectory(string $path, string $namespace): self
    {
        $namespacePrefix = trim($namespace, '\\');
        if ($namespacePrefix !== '') {
            $namespacePrefix .= '\\';
        }
        foreach ($this->listPHPFilesInFolder($path) as $baseClass) {
            $fqClass = $namespacePrefix . $baseClass;
            if (class_exists($fqClass)) {
                $instance = new $fqClass();
                if ($instance instanceof Driver) {
                    $this->registerDriver($instance);
                }
            }
        }

        return $this;
    }

    /**
     * Get the driver for specified environment.
     *
     * @param \CIInfo\Env|null $env The environment, if null we'll use the current one
     *
     * @throws \CIInfo\Exception\MultipleApplicableDriversException if more that one driver is valid for the current environment
     */
    public function getDriverForEnvironment(?Env $env = null): ?Driver
    {
        if ($env === null) {
            $env = Env::getCurrent();
        }
        $applicableDrivers = array_filter(
            $this->getDrivers(),
            function (Driver $driver) use ($env): bool {
                return $driver->isCurrent($env);
            }
        );
        switch (count($applicableDrivers)) {
            case 0:
                return null;
            case 1:
                return array_pop($applicableDrivers);
            default:
                throw new Exception\MultipleApplicableDriversException(array_keys($applicableDrivers));
        }
    }

    /**
     * List the PHP files in a specific folder and return their names without the .php extension.
     *
     * @throws \CIInfo\Exception\DirectoryNotFoundException if $path isn't an existing directory
     * @throws \CIInfo\Exception\DirectoryNotReadableException if $path is not readable
     *
     * @return \Generator|string[]
     */
    protected function listPHPFilesInFolder(string $path): Generator
    {
        $path = rtrim(str_replace('/', DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
        if (!is_dir($path)) {
            throw new Exception\DirectoryNotFoundException($path);
        }
        if (!is_readable($path)) {
            throw new Exception\DirectoryNotReadableException($path);
        }
        foreach (glob($path . DIRECTORY_SEPARATOR . '*.php') as $file) {
            yield basename($file, '.php');
        }
    }
}
