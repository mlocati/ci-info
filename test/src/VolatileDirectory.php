<?php

declare(strict_types=1);

namespace CIInfo\Test;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

class VolatileDirectory
{
    /**
     * The path of this volatile directory.
     *
     * @var string
     */
    protected $path;

    /**
     * @throws \RuntimeException
     */
    public function __construct()
    {
        $prefix = CIINFO_TEST_TMPDIR . '/';
        for ($i = 0;; $i++) {
            $path = "{$prefix}volatile-{$i}-" . uniqid();
            set_error_handler(function () {}, -1);
            $ok = mkdir($path);
            restore_error_handler();
            if ($ok) {
                break;
            }
            if ($i > 100) {
                throw new RuntimeException('Volatile directory creation failed');
            }
        }
        $this->path = $path;
    }

    /**
     * Clear and delete this volatile directory.
     */
    public function __destruct()
    {
        $path = $this->path;
        $this->path = null;
        if ($path !== null) {
            set_error_handler(function () {}, -1);
            try {
                if (is_dir($path)) {
                    for ($retry = true, $cycle = 0; $retry && $cycle <= 3; $cycle++) {
                        $retry = false;
                        $items = new RecursiveIteratorIterator(
                            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
                            RecursiveIteratorIterator::CHILD_FIRST
                        );
                        foreach ($items as $item) {
                            if ($item->isDir()) {
                                $deleted = rmdir($item->getPathname());
                            } else {
                                $deleted = unlink($item->getPathname());
                            }
                            if (!$deleted) {
                                $retry = true;
                            }
                        }
                        if ($retry) {
                            clearstatcache(true);
                            usleep(100);
                        }
                    }
                    rmdir($path);
                }
            } finally {
                restore_error_handler();
            }
        }
    }

    /**
     * Get the absolute path of this volatile directory.
     *
     * @param bool $nativeDirectorySeparator set to false to always have '/' as directory separator, false for the current OS directory separator
     */
    public function getPath(bool $nativeDirectorySeparator = false): string
    {
        return $nativeDirectorySeparator ? str_replace('/', DIRECTORY_SEPARATOR, $this->path) : $this->path;
    }
}
