<?php
/**
 * Comprehensive Repository PHP Lint Script — TechPilot
 * Lints all .php files across app/, config/, public/, scripts/, tests/
 */

$directories = [
    __DIR__ . '/../app',
    __DIR__ . '/../config',
    __DIR__ . '/../public',
    __DIR__ . '/../scripts',
    __DIR__ . '/../tests'
];

$errors = [];
$totalFiles = 0;

foreach ($directories as $dir) {
    if (!is_dir($dir)) continue;

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $totalFiles++;
            $filePath = $file->getPathname();
            $output = [];
            $returnCode = 0;
            exec('php -l ' . escapeshellarg($filePath), $output, $returnCode);
            if ($returnCode !== 0) {
                $errors[] = [
                    'file' => $filePath,
                    'error' => implode("\n", $output)
                ];
            }
        }
    }
}

echo "=== TechPilot Global PHP Lint Report ===\n";
echo "Total PHP Files Linted: $totalFiles\n";
echo "Syntax Errors Found: " . count($errors) . "\n\n";

if (!empty($errors)) {
    echo "ERRORS:\n";
    foreach ($errors as $e) {
        echo "File: " . $e['file'] . "\n";
        echo "Error:\n" . $e['error'] . "\n----------------------------------------\n";
    }
    exit(1);
} else {
    echo "100% CLEAN — Zero syntax errors across the entire repository!\n";
}
