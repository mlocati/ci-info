<?php

declare(strict_types=1);

namespace CIInfo\Exception;

use CIInfo\Exception;

/**
 * Exception thrown when more that one driver is applicable to the current environment.
 */
class MultipleApplicableDriversException extends Exception
{
    /**
     * The handles of the applicable drivers.
     *
     * @var string[]
     */
    private $applicableDriverHandles;

    /**
     * Initialize the instance.
     *
     * @param string $notFoundDirectory the handles of the applicable drivers
     */
    public function __construct(array $applicableDriverHandles)
    {
        sort($applicableDriverHandles);
        $applicableDriverHandles = array_values($applicableDriverHandles);
        parent::__construct("More than one driver can be used to inspect the current environment.\nThese are the handles of the applicable drivers:\n  - " . implode("\n  - ", $applicableDriverHandles));
        $this->applicableDriverHandles = $applicableDriverHandles;
    }

    /**
     * Get the handles of the applicable drivers.
     *
     * @return string[]
     */
    public function getApplicableDriverHandles(): array
    {
        return $this->applicableDriverHandles;
    }
}
