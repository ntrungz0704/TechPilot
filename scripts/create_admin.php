<?php
/**
 * CLI Script to provision an Admin user for TechPilot.
 * Usage: php scripts/create_admin.php --email=admin@techpilot.local --name="TechPilot Admin"
 */

if (php_sapi_name() !== 'cli') {
    die("Script này chỉ có thể chạy từ CLI.\n");
}

// Định nghĩa ROOT_PATH
define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/config/database.php';

// Parse CLI arguments
$options = getopt('', ['email:', 'name:']);
$email = $options['email'] ?? null;
$name = $options['name'] ?? null;

if (!$email || !$name) {
    echo "Lỗi: Thiếu tham số bắt buộc.\n";
    echo "Cách dùng: php scripts/create_admin.php --email=<email> --name=\"<name>\"\n";
    exit(1);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Lỗi: Email không hợp lệ.\n";
    exit(1);
}

// Yêu cầu nhập mật khẩu
echo "Nhập mật khẩu cho Admin (sẽ ẩn ký tự): ";

$password = '';
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $password = rtrim(shell_exec('powershell -Command "$p = Read-Host -AsSecureString; [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($p))"'));
} else {
    shell_exec('stty -echo');
    $password = rtrim(fgets(STDIN));
    shell_exec('stty echo');
    echo "\n";
}

if (strlen($password) < 6) {
    echo "\nLỗi: Mật khẩu phải có ít nhất 6 ký tự.\n";
    exit(1);
}

$db = Database::getConnection();
if (!$db) {
    echo "Lỗi: Không thể kết nối đến cơ sở dữ liệu.\n";
    exit(1);
}

// Kiểm tra email đã tồn tại chưa
$stmt = $db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
$stmt->execute([':email' => $email]);
if ($stmt->fetch()) {
    echo "Lỗi: Email này đã được sử dụng.\n";
    exit(1);
}

// Lưu admin
$hashed = password_hash($password, PASSWORD_DEFAULT);
$stmt = $db->prepare('INSERT INTO users (role_id, full_name, email, password, status) VALUES (1, :name, :email, :password, \'active\')');
$success = $stmt->execute([
    ':name' => $name,
    ':email' => $email,
    ':password' => $hashed
]);

if ($success) {
    echo "\nThành công: Đã tạo tài khoản Admin cho '{$name}' ({$email})!\n";
} else {
    echo "\nLỗi: Không thể lưu Admin vào database.\n";
}
