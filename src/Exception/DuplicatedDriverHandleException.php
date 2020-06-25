<?php

declare(strict_types=1);

namespace CIInfo\Exception;

use CIInfo\Exception;

/**
 * Exception thrown when we try to register a driver whose handle is already registered.
 */
class DuplicatedDriverHandleException extends Exception
{
    /**
     * The duplicated driver handle.
     *
     * @var string
     */
    private $duplicatedHandle;

    /**
     * Initialize the instance.
     *
     * @param string $duplicatedHandle the duplicated driver handle
     */
    public function __construct(string $duplicatedHandle)
    {
        parent::__construct("There's already a registered driver with handle '{$duplicatedHandle}'");
        $this->duplicatedHandle = $duplicatedHandle;
    }

    /**
     * Get the duplicated driver handle.
     */
    public function getDuplicatedHandle(): string
    {
        return $this->duplicatedHandle;
    }
}
