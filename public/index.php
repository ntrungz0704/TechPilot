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

$router = new Router();
$router->dispatch($url);
