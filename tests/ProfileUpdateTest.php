<?php

/**
 * End-to-End Test for Profile Suite
 */

$cookieJar = tempnam(sys_get_temp_dir(), 'techpilot_cookie_');

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

echo "=== 1. Login with dev customer ===\n";
$loginPage = httpReq('GET', 'http://127.0.0.1:8000/auth/login', [], $cookieJar);
preg_match('/name="csrf_token" value="([^"]+)"/', $loginPage['body'], $matches);
$csrfToken = $matches[1] ?? '';

$loginRes = httpReq('POST', 'http://127.0.0.1:8000/auth/login', [
    'csrf_token' => $csrfToken,
    'email' => 'dev@techpilot.vn',
    'password' => 'TechPilotDev2026!'
], $cookieJar);

echo "Login status: {$loginRes['code']}\n";

echo "\n=== 2. Get Profile Page ===\n";
$profilePage = httpReq('GET', 'http://127.0.0.1:8000/profile', [], $cookieJar);
echo "Profile status: {$profilePage['code']}\n";

preg_match('/name="csrf_token" value="([^"]+)"/', $profilePage['body'], $matches);
$profileCsrf = $matches[1] ?? '';

echo "\n=== 3. Post Update Profile ===\n";
$newFullName = 'TechPilot Dev User ' . rand(100, 999);
$newPhone = '0987654' . rand(100, 999);

$updateRes = httpReq('POST', 'http://127.0.0.1:8000/profile', [
    'csrf_token' => $profileCsrf,
    '_csrf' => $profileCsrf,
    'full_name' => $newFullName,
    'phone' => $newPhone
], $cookieJar);

echo "Update Profile status: {$updateRes['code']}\n";

$verifyPage = httpReq('GET', 'http://127.0.0.1:8000/profile', [], $cookieJar);
if (strpos($verifyPage['body'], $newFullName) !== false) {
    echo "PASS: Profile info updated successfully!\n";
} else {
    echo "FAIL: Profile info not updated.\n";
}

echo "\n=== 4. Test Change Password with wrong old password ===\n";
preg_match('/name="csrf_token" value="([^"]+)"/', $verifyPage['body'], $matches);
$pwdCsrf = $matches[1] ?? $profileCsrf;

$pwdRes = httpReq('POST', 'http://127.0.0.1:8000/profile/change_password', [
    'csrf_token' => $pwdCsrf,
    '_csrf' => $pwdCsrf,
    'old_password' => 'WrongPassword123',
    'new_password' => 'TechPilotDev2026!',
    'confirm_password' => 'TechPilotDev2026!'
], $cookieJar);
echo "Change password status: {$pwdRes['code']}\n";

$verifyPwd = httpReq('GET', 'http://127.0.0.1:8000/profile', [], $cookieJar);
if (strpos($verifyPwd['body'], 'Mật khẩu cũ không chính xác') !== false) {
    echo "PASS: Correctly rejected wrong old password!\n";
} else {
    echo "FAIL: Error flash not found for wrong old password.\n";
}

// Clean up
@unlink($cookieJar);
