<?php

declare(strict_types=1);

namespace CIInfo\Exception;

use CIInfo\Exception;

/**
 * Exception thrown when a directory couldn't be found.
 */
class DirectoryNotFoundException extends Exception
{
    /**
     * The directory that wasn't found.
     *
     * @var string
     */
    private $notFoundDirectory;

    /**
     * Initialize the instance.
     *
     * @param string $notFoundDirectory the directory that wasn't found
     */
    public function __construct(string $notFoundDirectory)
    {
        parent::__construct("Directory not found: {$notFoundDirectory}");
        $this->notFoundDirectory = $notFoundDirectory;
    }

    /**
     * Get the directory that wasn't found.
     */
    public function getNotFoundDirectory(): string
    {
        return $this->notFoundDirectory;
    }
}
