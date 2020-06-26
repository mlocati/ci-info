<?php

declare(strict_types=1);

spl_autoload_register(
    static function ($class): void {
        if (strpos($class, 'CIInfo\\') !== 0) {
            return;
        }
        $file = __DIR__ . DIRECTORY_SEPARATOR . 'src' . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen('CIInfo'))) . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
);
