<?php

declare(strict_types=1);

$root = getcwd();
$targets = [
    $root . '/resources/views',
    $root . '/app/Livewire',
];

function hasLetters(string $text): bool
{
    return (bool) preg_match('/\p{L}/u', $text);
}

function looksLikeUserText(string $text): bool
{
    $t = trim($text);
    if ($t === '') {
        return false;
    }
    if (!hasLetters($t)) {
        return false;
    }
    if (str_contains($t, '{{') || str_contains($t, '}}')) {
        return false;
    }
    if (str_starts_with($t, '@') || str_starts_with($t, ':') || str_starts_with($t, '$')) {
        return false;
    }
    if ((bool) preg_match('/^&[a-z0-9#]+;$/i', $t)) {
        return false;
    }
    if (preg_match('/^(wire:|x-|http|https)/i', $t)) {
        return false;
    }
    if ((bool) preg_match('/^[a-z0-9_.:-]+$/i', $t)) {
        return false;
    }
    return true;
}

function isStandardKey(string $key): bool
{
    return (bool) preg_match('/^[a-z0-9_]+$/', $key);
}

$untranslated = [];
$nonStandardUsage = [];

foreach ($targets as $dir) {
    if (!is_dir($dir)) {
        continue;
    }

    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($it as $fi) {
        if (!$fi->isFile()) {
            continue;
        }

        $path = $fi->getPathname();
        $isBlade = str_ends_with($path, '.blade.php');
        $isLivewirePhp = str_ends_with($path, '.php') && str_contains(str_replace('\\', '/', $path), '/app/Livewire/');

        if (!$isBlade && !$isLivewirePhp) {
            continue;
        }

        $rel = str_replace('\\', '/', substr($path, strlen($root) + 1));
        $content = (string) file_get_contents($path);
        $lines = preg_split('/\R/u', $content) ?: [];

        // Non-standard translation key usage in helper calls.
        $patterns = [
            '/__\(\s*["\']([^"\']+)["\']/u',
            '/@lang\(\s*["\']([^"\']+)["\']/u',
            '/trans\(\s*["\']([^"\']+)["\']/u',
            '/trans_choice\(\s*["\']([^"\']+)["\']/u',
        ];

        foreach ($lines as $i => $line) {
            $ln = $i + 1;
            foreach ($patterns as $pattern) {
                if (!preg_match_all($pattern, $line, $m)) {
                    continue;
                }
                foreach ($m[1] as $key) {
                    if (!isStandardKey($key)) {
                        $nonStandardUsage[] = [$rel, $ln, $key];
                    }
                }
            }
        }

        if ($isBlade) {
            $audit = preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', '', $content);
            $auditLines = preg_split('/\R/u', (string) $audit) ?: [];

            foreach ($auditLines as $i => $line) {
                $ln = $i + 1;

                if (str_contains($line, "__('") || str_contains($line, '__("') || str_contains($line, '@lang(')) {
                    continue;
                }

                if (preg_match_all('/>([^<]+)</u', $line, $m)) {
                    foreach ($m[1] as $segment) {
                        $text = trim($segment);
                        if (!looksLikeUserText($text)) {
                            continue;
                        }
                        $untranslated[] = [$rel, $ln, 'blade_text', $text];
                    }
                }

                if (preg_match_all('/\b(placeholder|title|alt|aria-label|label)=("|\')([^"\']+)\2/u', $line, $m, PREG_SET_ORDER)) {
                    foreach ($m as $hit) {
                        $val = trim($hit[3]);
                        if (!looksLikeUserText($val)) {
                            continue;
                        }
                        if (str_contains($val, '{{')) {
                            continue;
                        }
                        $untranslated[] = [$rel, $ln, 'blade_attr', $val];
                    }
                }
            }
        }

        if ($isLivewirePhp) {
            foreach ($lines as $i => $line) {
                $ln = $i + 1;
                if (str_contains($line, "__('") || str_contains($line, '__("')) {
                    continue;
                }

                $patterns = [
                    '/\b(?:notifySuccess|notifyError|notifyWarning|notifyInfo)\s*\(\s*(["\'])([^"\']*\p{L}[^"\']*)\1/u',
                    '/\bsession\(\)\s*->\s*flash\s*\(\s*["\'][^"\']+["\']\s*,\s*(["\'])([^"\']*\p{L}[^"\']*)\1/u',
                    '/\baddError\s*\(\s*["\'][^"\']+["\']\s*,\s*(["\'])([^"\']*\p{L}[^"\']*)\1/u',
                    '/\b(title|description|acceptLabel|rejectLabel)\s*:\s*(["\'])([^"\']*\p{L}[^"\']*)\2/u',
                ];

                foreach ($patterns as $p) {
                    if (!preg_match_all($p, $line, $m, PREG_SET_ORDER)) {
                        continue;
                    }
                    foreach ($m as $hit) {
                        $text = trim(end($hit));
                        if (!looksLikeUserText($text)) {
                            continue;
                        }
                        $untranslated[] = [$rel, $ln, 'livewire_php', $text];
                    }
                }
            }
        }
    }
}

$uniq = [];
$finalUntranslated = [];
foreach ($untranslated as $row) {
    $key = implode('|', $row);
    if (isset($uniq[$key])) {
        continue;
    }
    $uniq[$key] = true;
    $finalUntranslated[] = $row;
}

$uniq = [];
$finalNonStandard = [];
foreach ($nonStandardUsage as $row) {
    $key = implode('|', $row);
    if (isset($uniq[$key])) {
        continue;
    }
    $uniq[$key] = true;
    $finalNonStandard[] = $row;
}

usort($finalUntranslated, static fn (array $a, array $b): int => ($a[0] <=> $b[0]) ?: ($a[1] <=> $b[1]));
usort($finalNonStandard, static fn (array $a, array $b): int => ($a[0] <=> $b[0]) ?: ($a[1] <=> $b[1]));

echo 'untranslated_findings=' . count($finalUntranslated) . PHP_EOL;
foreach ($finalUntranslated as [$file, $line, $type, $text]) {
    echo $file . ':' . $line . ':' . $type . ':' . $text . PHP_EOL;
}

echo 'non_standard_key_usage=' . count($finalNonStandard) . PHP_EOL;
foreach ($finalNonStandard as [$file, $line, $key]) {
    echo $file . ':' . $line . ':' . $key . PHP_EOL;
}

exit(($finalUntranslated === [] && $finalNonStandard === []) ? 0 : 1);
