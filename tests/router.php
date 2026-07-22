<?php
/**
 * CLI Router Script for PHP Built-in Web Server in Integration & CI Testing
 */
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve static assets directly from public/ or root directory
$staticFile = __DIR__ . '/..' . $uri;
if ($uri !== '/' && file_exists($staticFile) && !is_dir($staticFile)) {
    return false;
}

// Strip /public prefix if present in request path
$cleanUri = preg_replace('#^/public/#', '/', $uri);

$_GET['url'] = ltrim($cleanUri, '/');
$_SERVER['SCRIPT_NAME'] = '/public/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../public/index.php';

require_once __DIR__ . '/../public/index.php';
