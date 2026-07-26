<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once ROOT_PATH . '/config/database.php';

$pdo = Database::getConnection();
if (!$pdo) die("DB error\n");

echo "=== USERS TABLE COLUMNS ===\n";
$cols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll();
foreach ($cols as $c) {
    echo "  - " . $c['Field'] . " (" . $c['Type'] . ")\n";
}

echo "\n=== ALL USERS IN DB ===\n";
$users = $pdo->query("SELECT * FROM users ORDER BY id ASC")->fetchAll();

echo "Total Users: " . count($users) . "\n";
foreach ($users as $u) {
    $email = $u['email'] ?? $u['username'] ?? 'N/A';
    $role = $u['role'] ?? 'N/A';
    $status = $u['status'] ?? 'N/A';
    $hash = $u['password'] ?? $u['password_hash'] ?? '';
    $fullName = $u['full_name'] ?? $u['name'] ?? $u['username'] ?? 'N/A';

    echo sprintf("ID: %d | Role: %-6s | Status: %-8s | Email: %-25s | Name: %s\n",
        $u['id'], $role, $status, $email, $fullName
    );
    echo "   Hash: " . $hash . "\n";

    // Test common passwords
    $testPwds = ['password', '123456', 'admin123', 'admin', '12345678', 'Techpilot@123', 'admin@123', '123456789'];
    $matchedPwd = null;
    foreach ($testPwds as $tp) {
        if (password_verify($tp, $hash)) {
            $matchedPwd = $tp;
            break;
        }
        if ($hash === md5($tp) || $hash === $tp) {
            $matchedPwd = $tp . " (plain/md5)";
            break;
        }
    }
    if ($matchedPwd) {
        echo "   ---> MATCHED PASSWORD: '$matchedPwd'\n";
    } else {
        echo "   ---> NO COMMON PASSWORD MATCHED!\n";
    }
}
