<?php

declare(strict_types=1);

function readJson(string $path): array
{
    if (!is_file($path)) {
        return [];
    }

    $decoded = json_decode((string) file_get_contents($path), true);

    return is_array($decoded) ? $decoded : [];
}

function writeJson(string $path, array $data): void
{
    ksort($data, SORT_STRING);
    $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($encoded)) {
        throw new RuntimeException('Failed to encode JSON: ' . $path);
    }

    file_put_contents($path, $encoded . PHP_EOL);
}

function normalizeKey(string $key, bool $prefixMode = false): string
{
    $source = trim($key);
    $endsWithSeparator = (bool) preg_match('/[._]$/', $source);

    $normalized = strtolower($source);
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalized);
    if (is_string($ascii) && $ascii !== '') {
        $normalized = $ascii;
    }

    $normalized = preg_replace('/[^a-z0-9]+/i', '_', $normalized) ?? '';
    $normalized = strtolower($normalized);
    $normalized = preg_replace('/_+/', '_', $normalized) ?? '';
    $normalized = trim($normalized, '_');

    if ($normalized === '') {
        $normalized = 'translation_key';
    }

    if ($prefixMode && $endsWithSeparator && !str_ends_with($normalized, '_')) {
        $normalized .= '_';
    }

    return $normalized;
}

function mergeValue(array &$target, string $key, mixed $value): void
{
    $incoming = is_string($value) ? $value : '';
    if (!array_key_exists($key, $target)) {
        $target[$key] = $incoming;
        return;
    }

    $existing = is_string($target[$key]) ? $target[$key] : '';
    if (trim($existing) === '' && trim($incoming) !== '') {
        $target[$key] = $incoming;
    }
}

function rewriteTranslationCalls(string $content, array &$keyMap, array &$en, array &$lt, int &$replaceCount): string
{
    $patterns = [
        '/__\(\s*(["\'])([^"\']+)\1(\s*\.)?/u',
        '/@lang\(\s*(["\'])([^"\']+)\1(\s*\.)?/u',
        '/trans\(\s*(["\'])([^"\']+)\1(\s*\.)?/u',
        '/trans_choice\(\s*(["\'])([^"\']+)\1(\s*\.)?/u',
    ];

    foreach ($patterns as $pattern) {
        $content = preg_replace_callback(
            $pattern,
            static function (array $m) use (&$keyMap, &$en, &$lt, &$replaceCount): string {
                $oldKey = stripcslashes($m[2]);
                if ($oldKey === '' || str_contains($oldKey, '$') || str_contains($oldKey, '\\')) {
                    return $m[0];
                }

                $isPrefix = isset($m[3]) && trim((string) $m[3]) !== '';

                if (isset($keyMap[$oldKey])) {
                    $newKey = $keyMap[$oldKey];
                    if ($isPrefix && !str_ends_with($newKey, '_')) {
                        $newKey .= '_';
                    }
                } else {
                    $newKey = normalizeKey($oldKey, $isPrefix);
                    $keyMap[$oldKey] = $newKey;

                    if (!$isPrefix) {
                        if (!array_key_exists($newKey, $en)) {
                            $en[$newKey] = $oldKey;
                        }
                        if (!array_key_exists($newKey, $lt)) {
                            $lt[$newKey] = $oldKey;
                        }
                    }
                }

                if ($newKey === $oldKey) {
                    return $m[0];
                }

                $replaceCount++;
                $quote = $m[1];

                return str_replace($quote . $m[2] . $quote, $quote . $newKey . $quote, $m[0]);
            },
            $content
        ) ?? $content;
    }

    return $content;
}

$root = getcwd();
$enPath = $root . '/lang/en.json';
$ltPath = $root . '/lang/lt.json';

$en = readJson($enPath);
$lt = readJson($ltPath);

$allKeys = array_values(array_unique(array_merge(array_keys($en), array_keys($lt))));
sort($allKeys, SORT_STRING);

$usedNormalized = [];
$keyMap = [];
foreach ($allKeys as $oldKey) {
    $base = normalizeKey($oldKey);
    $candidate = $base;
    $i = 2;

    while (isset($usedNormalized[$candidate]) && $usedNormalized[$candidate] !== $oldKey) {
        $candidate = $base . '_' . $i;
        $i++;
    }

    $usedNormalized[$candidate] = $oldKey;
    $keyMap[$oldKey] = $candidate;
}

$manualAliases = [
    'admin.create_admin' => 'admin_create_admin',
    'admin.create_new_admin' => 'admin_create_new_admin',
    'common.all_rights_reserved' => 'common_all_rights_reserved',
    'error_solutions.ai_solution_notice' => 'error_solutions_ai_solution_notice',
    'error_solutions.read_more' => 'error_solutions_read_more',
    'error_solutions.refresh_page' => 'error_solutions_refresh_page',
    'error_solutions.solution_executed' => 'error_solutions_solution_executed',
    'error_solutions.solution_provided_by' => 'error_solutions_solution_provided_by',
    'footer.contact_us' => 'footer_contact_us',
    'footer.languages' => 'footer_languages',
    'footer.quick_links' => 'footer_quick_links',
    'footer.tagline' => 'footer_tagline',
    'mail.laravel_logo' => 'mail_laravel_logo',
    'pagination.go_to_page' => 'pagination_go_to_page',
];

foreach ($manualAliases as $old => $new) {
    $keyMap[$old] = $new;
}

$normalizedEn = [];
$normalizedLt = [];
foreach ($allKeys as $oldKey) {
    $newKey = $keyMap[$oldKey] ?? normalizeKey($oldKey);
    mergeValue($normalizedEn, $newKey, $en[$oldKey] ?? '');
    mergeValue($normalizedLt, $newKey, $lt[$oldKey] ?? '');
}

$seedTranslations = [
    'admin_create_admin' => ['en' => 'Create Admin', 'lt' => 'Sukurti administratorių'],
    'admin_create_new_admin' => ['en' => 'Create New Admin', 'lt' => 'Sukurti naują administratorių'],
    'common_all_rights_reserved' => ['en' => 'All rights reserved.', 'lt' => 'Visos teisės saugomos.'],
    'error_solutions_ai_solution_notice' => ['en' => 'This solution was generated by AI and may require review.', 'lt' => 'Šis sprendimas sugeneruotas dirbtinio intelekto ir gali reikėti peržiūros.'],
    'error_solutions_read_more' => ['en' => 'Read more', 'lt' => 'Skaityti daugiau'],
    'error_solutions_refresh_page' => ['en' => 'Refresh page', 'lt' => 'Atnaujinti puslapį'],
    'error_solutions_solution_executed' => ['en' => 'Solution executed successfully.', 'lt' => 'Sprendimas sėkmingai įvykdytas.'],
    'error_solutions_solution_provided_by' => ['en' => 'Solution provided by', 'lt' => 'Sprendimą pateikė'],
    'footer_contact_us' => ['en' => 'Contact Us', 'lt' => 'Susisiekite su mumis'],
    'footer_languages' => ['en' => 'Languages', 'lt' => 'Kalbos'],
    'footer_quick_links' => ['en' => 'Quick Links', 'lt' => 'Greitos nuorodos'],
    'footer_tagline' => ['en' => 'Your trusted meat trading platform.', 'lt' => 'Jūsų patikima mėsos prekybos platforma.'],
    'mail_laravel_logo' => ['en' => 'Laravel Logo', 'lt' => 'Laravel logotipas'],
    'pagination_go_to_page' => ['en' => 'Go to page :page', 'lt' => 'Eiti į puslapį :page'],
    'maintenance_system_maintenance' => ['en' => 'System maintenance', 'lt' => 'Sistemos priežiūra'],
    'maintenance_system_update_in_progress' => ['en' => 'System update in progress', 'lt' => 'Vyksta sistemos atnaujinimas'],
    'maintenance_dear_visitors' => ['en' => 'Dear visitors,', 'lt' => 'Gerbiami lankytojai,'],
    'maintenance_update_description' => ['en' => 'We are currently performing important system updates. We are deploying new features and optimizing system performance to ensure an even better and more convenient user experience. We apologize for temporary inconvenience.', 'lt' => 'Šiuo metu atliekame svarbius sistemos atnaujinimo darbus. Diegiame naujus funkcionalumus ir optimizuojame sistemos veikimą, kad galėtume užtikrinti dar geresnę ir patogesnę naudojimosi patirtį. Atsiprašome už laikinus nepatogumus.'],
    'maintenance_installed_improvements' => ['en' => 'Installed improvements', 'lt' => 'Diegiami patobulinimai'],
    'maintenance_improved_performance' => ['en' => 'Faster system performance', 'lt' => 'Pagreitintas sistemos veikimas'],
    'maintenance_enhanced_security' => ['en' => 'Enhanced security', 'lt' => 'Sustiprintas saugumas'],
    'maintenance_new_features' => ['en' => 'New features', 'lt' => 'Naujos funkcijos'],
    'maintenance_what_we_improve' => ['en' => 'What we are improving', 'lt' => 'Ką patobuliname'],
    'maintenance_updated_user_interface' => ['en' => 'Updated user interface', 'lt' => 'Atnaujinta vartotojo sąsaja'],
    'maintenance_stabler_operation' => ['en' => 'More stable operation', 'lt' => 'Stabilesnis veikimas'],
    'maintenance_optimized_system' => ['en' => 'Optimized system', 'lt' => 'Optimizuota sistema'],
    'maintenance_update_duration' => ['en' => 'Update duration', 'lt' => 'Atnaujinimo trukmė'],
    'maintenance_update_duration_description' => ['en' => 'We are trying to complete the work as quickly as possible. Thank you for your patience!', 'lt' => 'Stengiamės darbus užbaigti kuo greičiau. Dėkojame už kantrybę!'],
];

foreach ($seedTranslations as $key => $values) {
    if (!isset($normalizedEn[$key]) || trim((string) $normalizedEn[$key]) === '') {
        $normalizedEn[$key] = $values['en'];
    }

    if (!isset($normalizedLt[$key]) || trim((string) $normalizedLt[$key]) === '' || $normalizedLt[$key] === $normalizedEn[$key]) {
        $normalizedLt[$key] = $values['lt'];
    }
}

$scanDirs = ['app', 'resources', 'routes', 'config', 'database'];
$changedFiles = 0;
$totalReplacements = 0;

foreach ($scanDirs as $dir) {
    $basePath = $root . DIRECTORY_SEPARATOR . $dir;
    if (!is_dir($basePath)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($basePath, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $fileInfo) {
        if (!$fileInfo->isFile()) {
            continue;
        }

        $path = $fileInfo->getPathname();
        if (!preg_match('/\.(php|blade\.php)$/i', $path)) {
            continue;
        }

        $content = (string) file_get_contents($path);
        $localCount = 0;
        $updated = rewriteTranslationCalls($content, $keyMap, $normalizedEn, $normalizedLt, $localCount);

        if ($updated === $content) {
            continue;
        }

        file_put_contents($path, $updated);
        $changedFiles++;
        $totalReplacements += $localCount;
    }
}

$allNormalizedKeys = array_values(array_unique(array_merge(array_keys($normalizedEn), array_keys($normalizedLt))));
sort($allNormalizedKeys, SORT_STRING);
foreach ($allNormalizedKeys as $key) {
    if (!array_key_exists($key, $normalizedEn)) {
        $normalizedEn[$key] = $key;
    }
    if (!array_key_exists($key, $normalizedLt)) {
        $normalizedLt[$key] = $normalizedEn[$key];
    }
}

writeJson($enPath, $normalizedEn);
writeJson($ltPath, $normalizedLt);

echo 'keys_before=' . count($allKeys) . PHP_EOL;
echo 'keys_after=' . count($allNormalizedKeys) . PHP_EOL;
echo 'changed_files=' . $changedFiles . PHP_EOL;
echo 'replacements=' . $totalReplacements . PHP_EOL;

