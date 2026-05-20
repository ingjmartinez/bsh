<?php

declare(strict_types=1);

/**
 * Guard against mojibake and invalid UTF-8 in source files.
 *
 * Usage:
 *   php tools/check_source_encoding.php
 */

$root = dirname(__DIR__);

$scanDirs = [
    'app',
    'bootstrap',
    'config',
    'database',
    'resources',
    'routes',
    'tests',
];

$allowedExtensions = [
    '.php',
    '.blade.php',
    '.js',
    '.ts',
    '.tsx',
    '.vue',
    '.css',
    '.scss',
    '.sass',
    '.json',
];

$excludeSegments = [
    DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR,
    DIRECTORY_SEPARATOR . 'node_modules' . DIRECTORY_SEPARATOR,
    DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR,
    DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR,
    DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR,
    DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR,
];

$mojibakeTokens = [
    'Ã¡', 'Ã©', 'Ã­', 'Ã³', 'Ãº', 'Ã±',
    'Ã', 'Ã‰', 'Ã', 'Ã“', 'Ãš', 'Ã‘',
    'Â¿', 'Â¡', 'Â©', 'Â°',
    'â€“', 'â€”', 'â€˜', 'â€™', 'â€œ', 'â€', 'â€¢', 'â€¦',
    'ðŸ', 'ï¸',
    '�',
];

$issues = [];

foreach ($scanDirs as $dir) {
    $absoluteDir = $root . DIRECTORY_SEPARATOR . $dir;

    if (!is_dir($absoluteDir)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($absoluteDir, FilesystemIterator::SKIP_DOTS)
    );

    /** @var SplFileInfo $fileInfo */
    foreach ($iterator as $fileInfo) {
        if (!$fileInfo->isFile()) {
            continue;
        }

        $path = $fileInfo->getPathname();

        if (shouldSkipPath($path, $excludeSegments)) {
            continue;
        }

        if (!hasAllowedExtension($path, $allowedExtensions)) {
            continue;
        }

        $contents = @file_get_contents($path);

        if ($contents === false) {
            $issues[] = [
                'file' => normalizePath($path, $root),
                'line' => 1,
                'reason' => 'No se pudo leer el archivo.',
            ];

            continue;
        }

        if (str_starts_with($contents, "\xEF\xBB\xBF")) {
            $issues[] = [
                'file' => normalizePath($path, $root),
                'line' => 1,
                'reason' => 'Tiene BOM UTF-8. Guarda el archivo como UTF-8 sin BOM.',
            ];
        }

        if (!mb_check_encoding($contents, 'UTF-8')) {
            $issues[] = [
                'file' => normalizePath($path, $root),
                'line' => 1,
                'reason' => 'No es UTF-8 valido.',
            ];

            // Avoid line scanning if byte stream is already invalid UTF-8.
            continue;
        }

        $lines = preg_split('/\R/u', $contents) ?: [$contents];

        foreach ($lines as $index => $line) {
            foreach ($mojibakeTokens as $token) {
                if ($token !== '' && str_contains($line, $token)) {
                    $issues[] = [
                        'file' => normalizePath($path, $root),
                        'line' => $index + 1,
                        'reason' => 'Posible mojibake detectado (' . $token . ').',
                    ];
                }
            }
        }
    }
}

if ($issues === []) {
    fwrite(STDOUT, "Encoding check OK. No se detectaron problemas.\n");
    exit(0);
}

fwrite(STDERR, "Encoding check FAILED. Se detectaron problemas:\n\n");

foreach ($issues as $issue) {
    fwrite(STDERR, '- ' . $issue['file'] . ':' . $issue['line'] . ' => ' . $issue['reason'] . "\n");
}

fwrite(STDERR, "\nSugerencia: reabre el archivo como UTF-8 y corrige caracteres corruptos antes de commit.\n");
exit(1);

function normalizePath(string $path, string $root): string
{
    $normalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    $normalizedRoot = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $root), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

    if (str_starts_with($normalized, $normalizedRoot)) {
        return substr($normalized, strlen($normalizedRoot));
    }

    return $normalized;
}

function shouldSkipPath(string $path, array $excludeSegments): bool
{
    foreach ($excludeSegments as $segment) {
        if (str_contains($path, $segment)) {
            return true;
        }
    }

    return false;
}

function hasAllowedExtension(string $path, array $extensions): bool
{
    foreach ($extensions as $extension) {
        if (str_ends_with($path, $extension)) {
            return true;
        }
    }

    return false;
}
