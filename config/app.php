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

// Nếu truy cập từ root (không qua thư mục public) trong môi trường Apache/XAMPP/Laragon,
// ta cần bổ sung /public vào BASE_URL để các assets và link chạy đúng.
if ($scriptDir !== '' && substr($scriptDir, -7) !== '/public' && $scriptDir !== '/public') {
    if (is_dir(dirname(__DIR__) . '/public')) {
        $scriptDir .= '/public';
    }
}

define('BASE_URL', $scriptDir);

define('APP_NAME', 'TechPilot');

// Đường dẫn tuyệt đối tới thư mục gốc dự án
define('ROOT_PATH', dirname(__DIR__));

// Bật hiển thị lỗi khi phát triển (tắt khi lên production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
