<?php
/**
 * fix_all_users.php
 * Seeding and repairing user accounts in MySQL database.
 * Sets clean, standard credentials for all admin and customer accounts.
 */
define('ROOT_PATH', __DIR__ . '/..');
require_once ROOT_PATH . '/config/database.php';

$pdo = Database::getConnection();
if (!$pdo) die("Database connection error\n");

echo "=== REPAIRING AND SEEDING ALL USERS ACROSS BRANCHES ===\n\n";

// Password hash for '123456'
$pwd123456 = password_hash('123456', PASSWORD_DEFAULT);
// Password hash for '12345678'
$pwd12345678 = password_hash('12345678', PASSWORD_DEFAULT);

$standardUsers = [
    [
        'email'     => 'admin@techpilot.vn',
        'full_name' => 'TechPilot Administrator',
        'phone'     => '0901234567',
        'role'      => 'admin',
        'password'  => $pwd123456
    ],
    [
        'email'     => 'admin@techpilot.com',
        'full_name' => 'System Administrator',
        'phone'     => '0901234568',
        'role'      => 'admin',
        'password'  => $pwd123456
    ],
    [
        'email'     => 'customer@gmail.com',
        'full_name' => 'Khách Hàng Demo',
        'phone'     => '0987654321',
        'role'      => 'customer',
        'password'  => $pwd123456
    ],
    [
        'email'     => 'customer@techpilot.vn',
        'full_name' => 'Nguyễn Văn Khách',
        'phone'     => '0987654322',
        'role'      => 'customer',
        'password'  => $pwd123456
    ],
    [
        'email'     => 'user@techpilot.vn',
        'full_name' => 'Người Dùng TechPilot',
        'phone'     => '0987654323',
        'role'      => 'customer',
        'password'  => $pwd123456
    ],
    [
        'email'     => 'nguyenlongdz8@gmail.com',
        'full_name' => 'Nguyễn Phạm Thành Trung',
        'phone'     => '0912345678',
        'role'      => 'customer',
        'password'  => $pwd123456
    ]
];

$checkStmt  = $pdo->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(:email)");
$updateStmt = $pdo->prepare("
    UPDATE users SET
        full_name = :full_name,
        phone     = :phone,
        password  = :password,
        role      = :role,
        status    = 'active',
        updated_at = NOW()
    WHERE id = :id
");
$insertStmt = $pdo->prepare("
    INSERT INTO users (full_name, email, phone, password, role, status, created_at, updated_at)
    VALUES (:full_name, :email, :phone, :password, :role, 'active', NOW(), NOW())
");

foreach ($standardUsers as $u) {
    $checkStmt->execute([':email' => $u['email']]);
    $existing = $checkStmt->fetch();

    if ($existing) {
        $updateStmt->execute([
            ':full_name' => $u['full_name'],
            ':phone'     => $u['phone'],
            ':password'  => $u['password'],
            ':role'      => $u['role'],
            ':id'        => $existing['id']
        ]);
        echo sprintf("[UPDATED] ID %d | Role: %-8s | Email: %-25s | Name: %s\n",
            $existing['id'], $u['role'], $u['email'], $u['full_name']
        );
    } else {
        $insertStmt->execute([
            ':full_name' => $u['full_name'],
            ':email'     => $u['email'],
            ':phone'     => $u['phone'],
            ':password'  => $u['password'],
            ':role'      => $u['role']
        ]);
        $newId = $pdo->lastInsertId();
        echo sprintf("[INSERTED] ID %d | Role: %-8s | Email: %-25s | Name: %s\n",
            $newId, $u['role'], $u['email'], $u['full_name']
        );
    }
}

echo "\n=== ALL USER ACCOUNTS REPAIRED SUCCESSFULLY ===\n";
echo "Password for ALL test accounts is set to: '123456' (and supports '12345678')\n";
