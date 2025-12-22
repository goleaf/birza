<?php

/**
 * Cross-platform storage cleanup script
 * Detects OS and runs the appropriate cleanup script
 */

$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

if ($isWindows) {
    // Windows: run batch file
    $script = __DIR__ . DIRECTORY_SEPARATOR . 'clean-storage.bat';
    $command = escapeshellarg($script);
    passthru($command, $exitCode);
} else {
    // Unix/Linux/Mac: run shell script
    $script = __DIR__ . DIRECTORY_SEPARATOR . 'clean-storage.sh';
    $command = 'bash ' . escapeshellarg($script);
    passthru($command, $exitCode);
}

exit($exitCode);

