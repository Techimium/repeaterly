<?php

/**
 * Syntax-checks every JS asset with `node --check`. No npm/ESLint involved;
 * see tests/README.md for why this is the deliberate stopping point for JS
 * coverage in this plugin.
 */

$plugin_root = dirname(__DIR__);
$assets_dir = $plugin_root . '/assets/js';

if (!is_dir($assets_dir)) {
    fwrite(STDOUT, "check-js: no assets/js directory, nothing to check\n");
    exit(0);
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($assets_dir, FilesystemIterator::SKIP_DOTS)
);

$files = [];

foreach ($iterator as $file) {
    if ($file->isFile() && substr($file->getFilename(), -3) === '.js') {
        $files[] = $file->getPathname();
    }
}

sort($files);

if (empty($files)) {
    fwrite(STDOUT, "check-js: no JS files found under assets/js\n");
    exit(0);
}

$failed = [];

foreach ($files as $file) {
    $relative = ltrim(str_replace('\\', '/', str_replace($plugin_root, '', $file)), '/');
    $command = 'node --check ' . escapeshellarg($file) . ' 2>&1';
    exec($command, $output, $exit_code);

    if (0 === $exit_code) {
        fwrite(STDOUT, "PASS: {$relative}\n");
    } else {
        fwrite(STDOUT, "FAIL: {$relative}\n");
        fwrite(STDERR, implode("\n", $output) . "\n");
        $failed[] = $relative;
    }
}

if (!empty($failed)) {
    fwrite(STDERR, sprintf("check-js: %d file(s) failed syntax check: %s\n", count($failed), implode(', ', $failed)));
    exit(1);
}

fwrite(STDOUT, sprintf("check-js: %d file(s) OK\n", count($files)));
exit(0);
