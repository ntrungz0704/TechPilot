<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/User.php';

$userModel = new User();

$testAccounts = [
    ['email' => 'admin@techpilot.vn', 'password' => '123456'],
    ['email' => 'admin@techpilot.com', 'password' => '123456'],
    ['email' => 'customer@gmail.com', 'password' => '123456'],
    ['email' => 'customer@techpilot.vn', 'password' => '123456'],
    ['email' => 'user@techpilot.vn', 'password' => '123456'],
    ['email' => 'nguyenlongdz8@gmail.com', 'password' => '123456'],
];

echo "=== TESTING LOGIN VERIFICATION FOR ALL ACCOUNTS ===\n\n";

foreach ($testAccounts as $acc) {
    $res = $userModel->verify($acc['email'], $acc['password']);
    if ($res) {
        echo sprintf("[PASS] Email: %-25s | Password: %-8s | Role: %-8s | Name: %s\n",
            $acc['email'], $acc['password'], $res['role'], $res['full_name']
        );
    } else {
        echo sprintf("[FAIL] Email: %-25s | Password: %-8s\n", $acc['email'], $acc['password']);
    }
}
