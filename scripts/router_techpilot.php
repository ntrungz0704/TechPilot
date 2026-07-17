<?php
/**
 * Router custom dành cho techpilot/public phục vụ PHP Built-in Server
 */

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$webRoot = dirname(__DIR__) . '/techpilot/public';
$filePath = $webRoot . $requestUri;

// Nếu file tĩnh tồn tại, phục vụ trực tiếp
if ($requestUri !== '/' && file_exists($filePath) && !is_dir($filePath)) {
    return false;
}

// Giả lập rewrite URL cho Front Controller
$_GET['url'] = ltrim($requestUri, '/');
require $webRoot . '/index.php';
