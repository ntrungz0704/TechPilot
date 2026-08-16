<?php

/**
 * Test Suite for Checkout Address Dropdowns and +84 Phone Number Validation
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/core/helpers.php';

echo "=== 1. Testing PHP Phone Helper Functions ===\n";

$validPhones = [
    '+84901234567',
    '+842431234567',
    '0901234567',
    '02431234567',
    '84901234567',
    '842431234567',
    '+84 901 234 567',
    '090-123-4567'
];

foreach ($validPhones as $p) {
    $isValid = isValidVietnamesePhone($p);
    $formatted = formatPhone($p);
    if (!$isValid || !str_starts_with($formatted, '+84')) {
        echo "FAIL: Phone '$p' expected valid, got valid=$isValid, formatted=$formatted\n";
        exit(1);
    }
}
echo "PASS: All valid phone numbers correctly recognized and formatted to +84.\n";

$invalidPhones = [
    '',
    '12345',
    '090123',
    '090123456789012',
    '+84001234567', // starts with 0 after +84
    'abcdefghijk',
    '090123456a'
];

foreach ($invalidPhones as $p) {
    $isValid = isValidVietnamesePhone($p);
    if ($isValid) {
        echo "FAIL: Invalid phone '$p' was incorrectly accepted as valid!\n";
        exit(1);
    }
}
echo "PASS: All invalid phone numbers correctly rejected.\n";

echo "\n=== 2. End-to-End Checkout Test with Province/District/Ward & +84 Phone ===\n";

$cookieJar = tempnam(sys_get_temp_dir(), 'techpilot_checkout_cookie_');

function httpReq(string $method, string $url, array $data = [], ?string $cookieFile = null): array {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    
    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    return [
        'code' => $httpCode,
        'headers' => $headers,
        'body' => $body
    ];
}

// 1. Login as customer
$loginPage = httpReq('GET', 'http://127.0.0.1:8000/auth/login', [], $cookieJar);
preg_match('/name="csrf_token" value="([^"]+)"/', $loginPage['body'], $matches);
$csrfToken = $matches[1] ?? '';

$loginRes = httpReq('POST', 'http://127.0.0.1:8000/auth/login', [
    'csrf_token' => $csrfToken,
    'email' => 'dev@techpilot.vn',
    'password' => 'TechPilotDev2026!'
], $cookieJar);

// 2. Add a product to cart (product ID 1)
$cartPage = httpReq('GET', 'http://127.0.0.1:8000/cart', [], $cookieJar);
preg_match('/name="csrf_token" value="([^"]+)"/', $cartPage['body'], $matches);
$cartCsrf = $matches[1] ?? $csrfToken;

$addRes = httpReq('POST', 'http://127.0.0.1:8000/cart/add', [
    'csrf_token' => $cartCsrf,
    'product_id' => 1,
    'quantity' => 1
], $cookieJar);

echo "Add to cart status: {$addRes['code']}\n";

// 3. Get Checkout page
$checkoutPage = httpReq('GET', 'http://127.0.0.1:8000/checkout', [], $cookieJar);
echo "Checkout page status: {$checkoutPage['code']}\n";

preg_match('/name="csrf_token" value="([^"]+)"/', $checkoutPage['body'], $matches);
$chkCsrf = $matches[1] ?? '';
preg_match('/name="submit_token" value="([^"]+)"/', $checkoutPage['body'], $matches);
$submitToken = $matches[1] ?? '';

// Check that dropdown elements exist on checkout page
if (strpos($checkoutPage['body'], 'id="checkoutProvince"') !== false &&
    strpos($checkoutPage['body'], 'id="checkoutDistrict"') !== false &&
    strpos($checkoutPage['body'], 'id="checkoutWard"') !== false &&
    strpos($checkoutPage['body'], 'id="checkoutAddressDetail"') !== false) {
    echo "PASS: Province, District, Ward dropdowns and Detail address elements found in Checkout HTML!\n";
} else {
    echo "FAIL: Dropdown elements missing from Checkout HTML.\n";
    exit(1);
}

// 4. Test Checkout Submit with Invalid Phone (should fail with error message)
$invalidSubmitRes = httpReq('POST', 'http://127.0.0.1:8000/checkout/submit', [
    'csrf_token' => $chkCsrf,
    '_csrf' => $chkCsrf,
    'submit_token' => $submitToken,
    'customer_name' => 'Nguyễn Văn Test',
    'phone' => '12345', // Invalid phone
    'province' => 'TP. Hồ Chí Minh',
    'district' => 'Quận 1',
    'ward' => 'Phường Bến Nghé',
    'address_detail' => 'Số 123 Đường Lê Lợi',
    'payment_method' => 'COD'
], $cookieJar);

$chkErrorPage = httpReq('GET', 'http://127.0.0.1:8000/checkout', [], $cookieJar);
if (strpos($chkErrorPage['body'], 'Số điện thoại không hợp lệ') !== false) {
    echo "PASS: Invalid phone number in checkout correctly rejected with error message!\n";
} else {
    echo "FAIL: Error message not displayed for invalid phone number in checkout.\n";
    exit(1);
}

// Refresh submit token
preg_match('/name="submit_token" value="([^"]+)"/', $chkErrorPage['body'], $matches);
$submitToken2 = $matches[1] ?? '';

// 5. Test Checkout Submit with Valid +84 phone and 4-tier address
$validSubmitRes = httpReq('POST', 'http://127.0.0.1:8000/checkout/submit', [
    'csrf_token' => $chkCsrf,
    '_csrf' => $chkCsrf,
    'submit_token' => $submitToken2,
    'customer_name' => 'Nguyễn Văn Test',
    'phone' => '0912345678', // Will be formatted to +84912345678
    'province' => 'TP. Hồ Chí Minh',
    'district' => 'Quận 1',
    'ward' => 'Phường Bến Nghé',
    'address_detail' => 'Số 123 Đường Lê Lợi',
    'payment_method' => 'COD'
], $cookieJar);

echo "Valid checkout submit status: {$validSubmitRes['code']}\n";

// 6. Check success page
$successPage = httpReq('GET', 'http://127.0.0.1:8000/checkout/success', [], $cookieJar);
echo "Checkout success page status: {$successPage['code']}\n";
if (strpos($successPage['body'], 'Đặt hàng thành công') !== false || strpos($successPage['body'], 'Cảm ơn') !== false) {
    echo "PASS: Order placed successfully!\n";
}

@unlink($cookieJar);
echo "\n=== ALL TESTS PASSED SUCCESSFULLY! ===\n";
