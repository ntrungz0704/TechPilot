<?php

/**
 * Test Suite for Two-Way Synchronization between Admin User Management and Customer Profile
 */

$adminCookie = tempnam(sys_get_temp_dir(), 'tp_admin_cookie_');
$userCookie = tempnam(sys_get_temp_dir(), 'tp_user_cookie_');

function httpCall(string $method, string $url, array $data = [], ?string $cookieFile = null): array {
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

echo "=== 1. Login Admin and Customer ===\n";

// Login Admin (id=1, admin@techpilot.vn)
$loginAdminPage = httpCall('GET', 'http://127.0.0.1:8000/auth/login', [], $adminCookie);
preg_match('/name="csrf_token" value="([^"]+)"/', $loginAdminPage['body'], $m);
$adminCsrf = $m[1] ?? '';

$resAdminLogin = httpCall('POST', 'http://127.0.0.1:8000/auth/login', [
    'csrf_token' => $adminCsrf,
    'email' => 'admin@techpilot.vn',
    'password' => 'TechPilotAdmin2026!'
], $adminCookie);
echo "Admin login HTTP: {$resAdminLogin['code']}\n";

// Login Customer (dev@techpilot.vn)
$loginUserPage = httpCall('GET', 'http://127.0.0.1:8000/auth/login', [], $userCookie);
preg_match('/name="csrf_token" value="([^"]+)"/', $loginUserPage['body'], $m);
$userCsrf = $m[1] ?? '';

$resUserLogin = httpCall('POST', 'http://127.0.0.1:8000/auth/login', [
    'csrf_token' => $userCsrf,
    'email' => 'dev@techpilot.vn',
    'password' => 'TechPilotDev2026!'
], $userCookie);
echo "Customer login HTTP: {$resUserLogin['code']}\n";

echo "\n=== 2. Customer updates Profile -> Admin sees updated info ===\n";

$profilePage = httpCall('GET', 'http://127.0.0.1:8000/profile', [], $userCookie);
preg_match('/name="csrf_token" value="([^"]+)"/', $profilePage['body'], $m);
$pCsrf = $m[1] ?? '';

$newTestName = 'Kỹ sư TechPilot ' . rand(100, 999);
$newTestPhone = '+849' . rand(10000000, 99999999);

$upProfileRes = httpCall('POST', 'http://127.0.0.1:8000/profile', [
    'csrf_token' => $pCsrf,
    '_csrf' => $pCsrf,
    'full_name' => $newTestName,
    'phone' => $newTestPhone
], $userCookie);

echo "Profile update HTTP: {$upProfileRes['code']}\n";

// Admin visits /admin/users and searches for the customer email
$adminUsersPage = httpCall('GET', 'http://127.0.0.1:8000/admin/users?search=dev@techpilot.vn', [], $adminCookie);
if (strpos($adminUsersPage['body'], $newTestName) !== false && strpos($adminUsersPage['body'], $newTestPhone) !== false) {
    echo "PASS: Admin view successfully reflects profile updates made by User!\n";
} else {
    echo "FAIL: Admin view did not find updated user information.\n";
    echo "Admin page body:\n" . $adminUsersPage['body'] . "\n";
    exit(1);
}

echo "\n=== 3. Admin locks Customer -> Customer Session is immediately revoked ===\n";

// Find Customer ID from admin users page
preg_match('/action="[^"]*\/admin\/users\/toggle_status\/(\d+)"/', $adminUsersPage['body'], $mId);
$targetUserId = $mId[1] ?? null;

if (!$targetUserId) {
    echo "FAIL: Could not find toggle_status form for user in admin page.\n";
    exit(1);
}

preg_match('/name="csrf_token" value="([^"]+)"/', $adminUsersPage['body'], $mCsrf);
$admToken = $mCsrf[1] ?? '';

// Lock the user
$lockRes = httpCall('POST', "http://127.0.0.1:8000/admin/users/toggle_status/{$targetUserId}", [
    'csrf_token' => $admToken,
    '_csrf' => $admToken
], $adminCookie);

echo "Admin toggle status HTTP: {$lockRes['code']}\n";

// Customer attempts to access /profile
$userBlockedAccess = httpCall('GET', 'http://127.0.0.1:8000/profile', [], $userCookie);
echo "Blocked user /profile HTTP: {$userBlockedAccess['code']}\n";

if ($userBlockedAccess['code'] === 302 && strpos($userBlockedAccess['headers'], 'auth/login') !== false) {
    echo "PASS: Locked customer was immediately redirected to login and kicked out!\n";
} else {
    echo "FAIL: Locked customer was not kicked out.\n";
    exit(1);
}

echo "\n=== 4. Admin unlocks Customer -> Customer can login again ===\n";

// Re-read admin users page for fresh CSRF
$adminUsersPage2 = httpCall('GET', 'http://127.0.0.1:8000/admin/users?search=' . urlencode($newTestName), [], $adminCookie);
preg_match('/name="csrf_token" value="([^"]+)"/', $adminUsersPage2['body'], $mCsrf2);
$admToken2 = $mCsrf2[1] ?? '';

// Unlock the user
$unlockRes = httpCall('POST', "http://127.0.0.1:8000/admin/users/toggle_status/{$targetUserId}", [
    'csrf_token' => $admToken2,
    '_csrf' => $admToken2
], $adminCookie);
echo "Admin unlock status HTTP: {$unlockRes['code']}\n";

// Customer logs back in
$loginUserPage2 = httpCall('GET', 'http://127.0.0.1:8000/auth/login', [], $userCookie);
preg_match('/name="csrf_token" value="([^"]+)"/', $loginUserPage2['body'], $m);
$userCsrf2 = $m[1] ?? '';

$resUserRelogin = httpCall('POST', 'http://127.0.0.1:8000/auth/login', [
    'csrf_token' => $userCsrf2,
    'email' => 'dev@techpilot.vn',
    'password' => 'TechPilotDev2026!'
], $userCookie);

if ($resUserRelogin['code'] === 302 && (strpos($resUserRelogin['headers'], 'Location: /') !== false || strpos($resUserRelogin['headers'], 'Location: http') !== false)) {
    echo "PASS: Unlocked customer logged back in successfully!\n";
} else {
    echo "FAIL: Unlocked customer failed to login.\n";
    exit(1);
}

@unlink($adminCookie);
@unlink($userCookie);

echo "\n=== TWO-WAY ADMIN <-> USER SYNCHRONIZATION TEST: 100% SUCCESS ===\n";
