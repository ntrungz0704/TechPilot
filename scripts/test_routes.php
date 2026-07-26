<?php
require_once __DIR__ . '/../config/database.php';

$slugs = [
    'laptop', 'pc', 'monitor', 'mainboard', 'cpu', 'vga', 'ram',
    'storage', 'case', 'cooling', 'psu', 'keyboard', 'mouse', 'chair',
    'headset', 'speaker', 'console', 'accessories', 'office-equipment', 'power-bank'
];

echo "=== TESTING 20 CANONICAL CATEGORY ROUTES ===\n\n";

$passCount = 0;
$failCount = 0;

foreach ($slugs as $slug) {
    $url = "http://127.0.0.1:8000/category/" . $slug;
    $context = stream_context_create(['http' => ['ignore_errors' => true]]);
    $response = @file_get_contents($url, false, $context);
    $httpCode = 0;
    if (isset($http_response_header) && !empty($http_response_header[0])) {
        preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $m);
        $httpCode = (int)($m[1] ?? 0);
    }

    if ($httpCode === 200 && strpos($response, 'TechPilot') !== false) {
        echo "  [PASS] /category/$slug -> HTTP $httpCode (" . strlen($response) . " bytes)\n";
        $passCount++;
    } else {
        echo "  [FAIL] /category/$slug -> HTTP $httpCode\n";
        $failCount++;
    }
}

echo "\nResult: $passCount PASS / $failCount FAIL\n";
if ($failCount > 0) exit(1);
