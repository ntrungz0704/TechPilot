<?php
/**
 * Test cases for absoluteUrl() and resolveAbsoluteUrl() helper.
 * Chạy: php tests/test_absolute_url.php
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/core/helpers.php';

function test_case($name, $appUrl, $baseUrl, $path, $expected, $server = []) {
    $res = resolveAbsoluteUrl($path, $appUrl, $baseUrl, $server);
    $pass = ($res === $expected);
    echo ($pass ? "[PASS]" : "[FAIL]") . " {$name}\n";
    if (!$pass) {
        echo "       Got     : '{$res}'\n";
        echo "       Expected: '{$expected}'\n";
    }
    return $pass;
}

$allPass = true;

// 1. APP_URL="", BASE_URL=""
$allPass = test_case('Case 1: APP_URL="", BASE_URL=""', '', '', 'post', 'http://localhost/post') && $allPass;

// 2. APP_URL="", BASE_URL="/techpilot/public"
$allPass = test_case('Case 2: APP_URL="", BASE_URL="/techpilot/public"', '', '/techpilot/public', 'post', 'http://localhost/techpilot/public/post') && $allPass;

// 3. APP_URL="https://example.com", BASE_URL=""
$allPass = test_case('Case 3: APP_URL="https://example.com", BASE_URL=""', 'https://example.com', '', 'post', 'https://example.com/post') && $allPass;

// 4. APP_URL="https://example.com", BASE_URL="/techpilot/public"
$allPass = test_case('Case 4: APP_URL="https://example.com", BASE_URL="/techpilot/public"', 'https://example.com', '/techpilot/public', 'post', 'https://example.com/post') && $allPass;

// 5. APP_URL="https://example.com/techpilot/public", BASE_URL=""
$allPass = test_case('Case 5: APP_URL="https://example.com/techpilot/public", BASE_URL=""', 'https://example.com/techpilot/public', '', 'post', 'https://example.com/techpilot/public/post') && $allPass;

// 6. Validation Invalid APP_URL: javascript:...
$allPass = test_case('Case 6: APP_URL="javascript:alert(1)" (fallback)', 'javascript:alert(1)', '/techpilot/public', 'post', 'http://localhost/techpilot/public/post') && $allPass;

// 7. Validation Invalid APP_URL: example.com (no scheme)
$allPass = test_case('Case 7: APP_URL="example.com" (fallback)', 'example.com', '', 'post', 'http://localhost/post') && $allPass;

// 8. Validation Invalid APP_URL: /foo (relative path)
$allPass = test_case('Case 8: APP_URL="/foo" (fallback)', '/foo', '', 'post', 'http://localhost/post') && $allPass;

// 9. Directly test absoluteUrl() helper function
$helperResult = absoluteUrl('post');
$helperPass = (str_contains($helperResult, '/post'));
echo ($helperPass ? "[PASS]" : "[FAIL]") . " Direct call to absoluteUrl('post') => '{$helperResult}'\n";
$allPass = $helperPass && $allPass;

echo "\n══════════════════════════════════════════\n";
echo "Results: " . ($allPass ? "ALL 9 TEST CASES PASSED" : "FAILED") . "\n";
echo "══════════════════════════════════════════\n";

exit($allPass ? 0 : 1);
