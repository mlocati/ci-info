<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

define('CIINFO_TEST_ROOTDIR', str_replace(DIRECTORY_SEPARATOR, '/', dirname(__DIR__)));

define('CIINFO_TEST_TMPDIR', str_replace(DIRECTORY_SEPARATOR, '/', __DIR__) . '/tmp');
if (is_dir(CIINFO_TEST_TMPDIR)) {
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(CIINFO_TEST_TMPDIR, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($items as $item) {
        if ($item->isDir()) {
            set_error_handler(function () {}, -1);
            rmdir($item->getPathname());
            restore_error_handler();
        } else {
            set_error_handler(function () {}, -1);
            unlink($item->getPathname());
            restore_error_handler();
        }
    }
    unset($item, $items);
} else {
    mkdir(CIINFO_TEST_TMPDIR);
}
