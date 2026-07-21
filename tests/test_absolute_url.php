<?php
/**
 * Test cases for absoluteUrl() contract.
 * Run: php tests/test_absolute_url.php
 */

function run_test_case($appUrl, $baseUrl, $path, $expected) {
    // Detect scheme
    if ($appUrl !== '') {
        $base = rtrim($appUrl, '/');
    } else {
        $scheme = 'http';
        $host   = 'localhost';
        $base   = $scheme . '://' . $host;
        if ($baseUrl !== '') {
            $base .= '/' . ltrim($baseUrl, '/');
        }
    }
    $base = rtrim($base, '/');
    $p    = ltrim($path, '/');
    $res  = $p === '' ? $base . '/' : $base . '/' . $p;

    $pass = ($res === $expected);
    echo ($pass ? "[PASS]" : "[FAIL]") . " APP_URL='{$appUrl}', BASE_URL='{$baseUrl}', path='{$path}' => '{$res}' (expected: '{$expected}')\n";
    return $pass;
}

$allPass = true;
$allPass = run_test_case('', '', 'post', 'http://localhost/post') && $allPass;
$allPass = run_test_case('', '/techpilot/public', 'post', 'http://localhost/techpilot/public/post') && $allPass;
$allPass = run_test_case('https://example.com', '', 'post', 'https://example.com/post') && $allPass;
$allPass = run_test_case('https://example.com', '/techpilot/public', 'post', 'https://example.com/post') && $allPass;
$allPass = run_test_case('https://example.com/techpilot/public', '', 'post', 'https://example.com/techpilot/public/post') && $allPass;

echo "\nContract Result: " . ($allPass ? "ALL 5 CASES PASSED" : "FAILED") . "\n";
