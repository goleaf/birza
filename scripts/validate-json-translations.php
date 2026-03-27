<?php

declare(strict_types=1);

/**
 * Validates JSON-only localization setup for Laravel project:
 * 1) No PHP language files remain under lang/
 * 2) All static translation keys used in code exist in lang/en.json and lang/lt.json
 */

function out(string $message): void
{
    fwrite(STDOUT, $message . PHP_EOL);
}

function err(string $message): void
{
    fwrite(STDERR, $message . PHP_EOL);
}

function collectPhpLangFiles(string $langDir): array
{
    if (!is_dir($langDir)) {
        return [];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($langDir, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $fileInfo) {
        if (!$fileInfo->isFile()) {
            continue;
        }

        if (strtolower($fileInfo->getExtension()) === 'php') {
            $files[] = $fileInfo->getPathname();
        }
    }

    sort($files);

    return $files;
}

function collectUsedTranslationKeys(string $root, array $scanDirs): array
{
    $patterns = [
        '/__\(\s*(["\'])((?:\\\\.|(?!\1).)*)\1(?!\s*\.)/u',
        '/@lang\(\s*(["\'])((?:\\\\.|(?!\1).)*)\1(?!\s*\.)/u',
        '/trans\(\s*(["\'])((?:\\\\.|(?!\1).)*)\1(?!\s*\.)/u',
        '/trans_choice\(\s*(["\'])((?:\\\\.|(?!\1).)*)\1(?!\s*\.)/u',
    ];

    $used = [];

    foreach ($scanDirs as $dir) {
        $path = $root . DIRECTORY_SEPARATOR . $dir;
        if (!is_dir($path)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }

            $filePath = $fileInfo->getPathname();
            if (!preg_match('/\.(php|blade\.php)$/i', $filePath)) {
                continue;
            }

            $content = (string) file_get_contents($filePath);

            foreach ($patterns as $pattern) {
                if (!preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
                    continue;
                }

                foreach ($matches as $match) {
                    $key = stripcslashes($match[2]);
                    if ($key === '' || str_contains($key, '$') || str_contains($key, '\\')) {
                        continue;
                    }

                    $used[$key] = true;
                }
            }
        }
    }

    $keys = array_keys($used);
    sort($keys);

    return $keys;
}

function loadJsonTranslations(string $path): array
{
    if (!is_file($path)) {
        return [];
    }

    $decoded = json_decode((string) file_get_contents($path), true);
    if (!is_array($decoded)) {
        return [];
    }

    return $decoded;
}

function isStandardKey(string $key): bool
{
    return (bool) preg_match('/^[a-z0-9]+(?:_[a-z0-9]+)*$/', $key);
}

$root = getcwd();
$langDir = $root . DIRECTORY_SEPARATOR . 'lang';
$errors = [];

$phpLangFiles = collectPhpLangFiles($langDir);
if ($phpLangFiles !== []) {
    $errors[] = 'Found PHP lang files under lang/:';
    foreach ($phpLangFiles as $file) {
        $errors[] = ' - ' . str_replace($root . DIRECTORY_SEPARATOR, '', $file);
    }
}

$usedKeys = collectUsedTranslationKeys($root, ['app', 'resources', 'routes', 'config', 'database']);
$en = loadJsonTranslations($langDir . DIRECTORY_SEPARATOR . 'en.json');
$lt = loadJsonTranslations($langDir . DIRECTORY_SEPARATOR . 'lt.json');

$invalidUsedKeys = [];
foreach ($usedKeys as $key) {
    if (!isStandardKey($key)) {
        $invalidUsedKeys[] = $key;
    }
}

$invalidEnKeys = [];
foreach (array_keys($en) as $key) {
    if (!isStandardKey($key)) {
        $invalidEnKeys[] = $key;
    }
}

$invalidLtKeys = [];
foreach (array_keys($lt) as $key) {
    if (!isStandardKey($key)) {
        $invalidLtKeys[] = $key;
    }
}

$missingEn = [];
$missingLt = [];
foreach ($usedKeys as $key) {
    if (!array_key_exists($key, $en)) {
        $missingEn[] = $key;
    }
    if (!array_key_exists($key, $lt)) {
        $missingLt[] = $key;
    }
}

if ($missingEn !== []) {
    $errors[] = 'Missing keys in lang/en.json (' . count($missingEn) . '):';
    foreach ($missingEn as $key) {
        $errors[] = ' - ' . $key;
    }
}

if ($missingLt !== []) {
    $errors[] = 'Missing keys in lang/lt.json (' . count($missingLt) . '):';
    foreach ($missingLt as $key) {
        $errors[] = ' - ' . $key;
    }
}

if ($invalidUsedKeys !== []) {
    $errors[] = 'Non-standard used keys (must be word_word) (' . count($invalidUsedKeys) . '):';
    foreach ($invalidUsedKeys as $key) {
        $errors[] = ' - ' . $key;
    }
}

if ($invalidEnKeys !== []) {
    $errors[] = 'Non-standard keys in lang/en.json (' . count($invalidEnKeys) . '):';
    foreach ($invalidEnKeys as $key) {
        $errors[] = ' - ' . $key;
    }
}

if ($invalidLtKeys !== []) {
    $errors[] = 'Non-standard keys in lang/lt.json (' . count($invalidLtKeys) . '):';
    foreach ($invalidLtKeys as $key) {
        $errors[] = ' - ' . $key;
    }
}

out('Translation validation summary:');
out(' - Used static keys: ' . count($usedKeys));
out(' - en.json keys: ' . count($en));
out(' - lt.json keys: ' . count($lt));
out(' - PHP lang files under lang/: ' . count($phpLangFiles));

if ($errors !== []) {
    err('');
    foreach ($errors as $line) {
        err($line);
    }
    exit(1);
}

out('Validation passed.');
exit(0);
