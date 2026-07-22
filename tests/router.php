<?php
/**
 * CLI Router Script for PHP Built-in Web Server in Integration & CI Testing
 */
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$filePath = __DIR__ . '/../public' . $uri;

if ($uri !== '/' && file_exists($filePath) && !is_dir($filePath)) {
    return false; // Serve static file directly
}

$_GET['url'] = ltrim($uri, '/');
require_once __DIR__ . '/../public/index.php';
