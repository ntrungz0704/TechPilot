<?php
/**
 * FRONT CONTROLLER
 * Toàn bộ request đều đi qua file này (nhờ .htaccess rewrite)
 */

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/app/core/helpers.php';
require_once dirname(__DIR__) . '/app/core/Controller.php';
require_once dirname(__DIR__) . '/app/core/Router.php';

// Lấy phần URL sau index.php, ví dụ: product/detail/asus-rog-zephyrus-g16
$url = $_GET['url'] ?? '';

// Kiểm tra bảo mật CSRF cho toàn bộ các POST request (chống giả mạo yêu cầu)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if ($token === '' || $token !== ($_SESSION['csrf_token'] ?? '')) {
        http_response_code(403);
        die('Yêu cầu không hợp lệ (CSRF Token mismatch).');
    }
}

$router = new Router();
$router->dispatch($url);
