<?php
/**
 * CLI Router Script for PHP Built-in Web Server in Integration & CI Testing
 */
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve static assets from public/ (BASE_URL is empty, assets are at /assets/...)
$staticFile = __DIR__ . '/../public' . $uri;
if ($uri !== '/' && file_exists($staticFile) && !is_dir($staticFile)) {
    $mimeTypes = [
        'css' => 'text/css', 'js' => 'application/javascript',
        'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
        'gif' => 'image/gif', 'svg' => 'image/svg+xml', 'ico' => 'image/x-icon',
        'woff' => 'font/woff', 'woff2' => 'font/woff2', 'ttf' => 'font/ttf',
        'webp' => 'image/webp', 'json' => 'application/json',
    ];
    $ext = strtolower(pathinfo($staticFile, PATHINFO_EXTENSION));
    header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
    readfile($staticFile);
    exit;
}

// Strip /public prefix if present in request path
$cleanUri = preg_replace('#^/public/#', '/', $uri);

$_GET['url'] = ltrim($cleanUri, '/');
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../public/index.php';

require_once __DIR__ . '/../public/index.php';
