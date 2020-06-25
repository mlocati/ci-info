<?php

declare(strict_types=1);

namespace CIInfo\Exception;

use CIInfo\Exception;

/**
 * Exception thrown when a directory couldn't be read.
 */
class DirectoryNotReadableException extends Exception
{
    /**
     * The directory that isn't readable.
     *
     * @var string
     */
    private $notReadableDirectory;

    /**
     * Initialize the instance.
     *
     * @param string $notReadableDirectory the directory that isn't readable
     */
    public function __construct(string $notReadableDirectory)
    {
        parent::__construct("Directory not readable: {$notReadableDirectory}");
        $this->notReadableDirectory = $notReadableDirectory;
    }

    /**
     * Get the directory that isn't readable.
     */
    public function getNotReadableDirectory(): string
    {
        return $this->notReadableDirectory;
    }
}
