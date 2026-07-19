<?php

/**
 * Cấu hình chung ứng dụng
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Đường dẫn gốc của website, tự động nhận theo thư mục public đang chạy
// Ví dụ với XAMPP: /techpilot/public hoặc /public
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$scriptDir = str_replace('\\', '/', $scriptDir);
$scriptDir = rtrim($scriptDir, '/');

if ($scriptDir === '/' || $scriptDir === '\\') {
    $scriptDir = '';
}

define('BASE_URL', $scriptDir);

define('APP_NAME', 'TechPilot');

// Đường dẫn tuyệt đối tới thư mục gốc dự án
define('ROOT_PATH', dirname(__DIR__));

// Bật hiển thị lỗi khi phát triển (tắt khi lên production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
