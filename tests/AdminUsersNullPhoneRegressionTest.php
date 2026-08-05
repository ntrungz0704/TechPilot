<?php

require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../config/database.php';

function runAdminUsersNullPhoneRegressionTest()
{
    echo "Running AdminUsersNullPhoneRegressionTest...\n";

    $db = (new Database())->getConnection();
    $errors = [];
    $passed = 0;

    // Test 1-4: Unit test for formatPhone
    if (formatPhone(null) !== '') {
        $errors[] = "formatPhone(null) did not return empty string.";
    } else {
        $passed++;
    }

    if (formatPhone('') !== '') {
        $errors[] = "formatPhone('') did not return empty string.";
    } else {
        $passed++;
    }

    if (formatPhone('0901234567') !== '+84901234567') {
        $errors[] = "formatPhone('0901234567') did not return '+84901234567'.";
    } else {
        $passed++;
    }

    if (formatPhone('+84901234567') !== '+84901234567') {
        $errors[] = "formatPhone('+84901234567') did not return '+84901234567'.";
    } else {
        $passed++;
    }

    // Prepare HTTP test
    $tempEmail = 'temp_null_phone_user_' . time() . '@example.com';
    $adminEmail = 'admin99@test.com'; // Admin test user

    // Insert temp user
    try {
        $stmt = $db->prepare("INSERT INTO users (email, password, full_name, phone, address, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $tempEmail,
            password_hash('password123', PASSWORD_DEFAULT),
            'Temp User Null Phone',
            null, // phone
            '123 Temp St',
            'customer',
            'active'
        ]);
        $tempUserId = $db->lastInsertId();
    } catch (Exception $e) {
        $errors[] = "Failed to insert temp user: " . $e->getMessage();
        goto finish;
    }

    // Function to simulate GET request as admin
    $simulateGetAsAdmin = function ($url) use ($db, $adminEmail) {
        // Fetch admin user
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$adminEmail]);
        $adminUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$adminUser) {
            return ['code' => 500, 'body' => 'Admin user not found in DB'];
        }

        // Mock session and server variables
        $_SESSION['user'] = $adminUser;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = $url;
        $_GET = [];
        
        // Output buffer to capture response
        ob_start();
        
        // We need to require index.php in a clean way, or just invoke the router/controller.
        // But requiring index.php might exit the script or re-declare functions.
        // It's safer to use file_get_contents to the local dev server.
        return true; 
    };

    // Since we need to test HTTP response, we should use a curl wrapper to the dev server
    $serverUrl = 'http://127.0.0.1:8011';
    
    // First, login to get session cookie
    // Fetch login page to get CSRF token
    $ch = curl_init($serverUrl . '/auth/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/cookie.txt');
    $loginPage = curl_exec($ch);
    curl_close($ch);

    $csrfToken = '';
    if (preg_match('/name="csrf_token" value="([^"]+)"/', $loginPage, $matches)) {
        $csrfToken = $matches[1];
    }

    // Now login
    $ch = curl_init($serverUrl . '/auth/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'email' => 'admin99@test.com', 
        'password' => 'password123', // Admin password might be password123 as seen in AdminRouteAuthorizationTest
        'csrf_token' => $csrfToken
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/cookie.txt');
    curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/cookie.txt');
    $loginResp = curl_exec($ch);
    $loginCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($loginCode >= 400 && $loginCode != 302 && $loginCode != 303) {
         $errors[] = "Failed to login as admin. HTTP Code: $loginCode";
    }

    // Check /admin/users
    $ch = curl_init($serverUrl . '/admin/users');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/cookie.txt');
    $usersResp = curl_exec($ch);
    $usersCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($usersCode !== 200) {
        $errors[] = "/admin/users returned HTTP $usersCode instead of 200.";
    } else {
        $passed++;
        if (strpos($usersResp, $tempEmail) === false) {
            $errors[] = "/admin/users response does not contain the temporary user email.";
        } else {
            $passed++;
        }
        if (strpos($usersResp, 'Chưa cập nhật') === false) {
            $errors[] = "/admin/users response does not contain 'Chưa cập nhật'.";
        } else {
            $passed++;
        }
        if (strpos($usersResp, 'Fatal error') !== false || strpos($usersResp, 'TypeError') !== false) {
            $errors[] = "/admin/users response contains a PHP error.";
        } else {
            $passed++;
        }
    }

    // Check /admin/customers
    $ch = curl_init($serverUrl . '/admin/customers');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/cookie.txt');
    $customersResp = curl_exec($ch);
    $customersCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($customersCode !== 200) {
        $errors[] = "/admin/customers returned HTTP $customersCode instead of 200.";
    } else {
        $passed++;
        if (strpos($customersResp, $tempEmail) === false) {
            $errors[] = "/admin/customers response does not contain the temporary user email.";
        } else {
            $passed++;
        }
        if (strpos($customersResp, 'Chưa cập nhật') === false) {
            $errors[] = "/admin/customers response does not contain 'Chưa cập nhật'.";
        } else {
            $passed++;
        }
        if (strpos($customersResp, 'Fatal error') !== false || strpos($customersResp, 'TypeError') !== false) {
            $errors[] = "/admin/customers response contains a PHP error.";
        } else {
            $passed++;
        }
    }

finish:
    // Cleanup temporary user
    if (isset($tempUserId)) {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$tempUserId]);
    }
    if (file_exists(__DIR__ . '/cookie.txt')) {
        unlink(__DIR__ . '/cookie.txt');
    }

    if (empty($errors)) {
        echo "SUCCESS: All $passed assertions passed.\n";
        exit(0);
    } else {
        echo "FAILED:\n";
        foreach ($errors as $error) {
            echo "- $error\n";
        }
        exit(1);
    }
}

runAdminUsersNullPhoneRegressionTest();
