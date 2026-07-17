<?php
/**
 * Router dùng riêng cho PHP Built-in Server (khi không có Apache/XAMPP).
 * Cách chạy: php -S localhost:8000 router.php   (thực thi trong thư mục /public)
 * Với Apache/Nginx, không cần file này - .htaccess đã lo việc rewrite URL.
 */

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$filePath = __DIR__ . $requestUri;

// Nếu request trỏ tới 1 file có thật trong /public (css, js, ảnh...) thì trả về trực tiếp
if ($requestUri !== '/' && file_exists($filePath) && !is_dir($filePath)) {
    return false; // để PHP built-in server tự phục vụ file tĩnh
}

// Ngược lại, giả lập rewrite giống .htaccess: gán vào $_GET['url'] rồi include index.php
$_GET['url'] = ltrim($requestUri, '/');
require __DIR__ . '/index.php';
