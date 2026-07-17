<?php
/**
 * PHP Built-in Web Server Router Script
 * This file allows running the application directly from the root directory using:
 * php -S 127.0.0.1:8000 router.php
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Check if request is for a physical file inside the public directory
$publicFile = __DIR__ . '/public' . $uri;

if ($uri !== '/' && file_exists($publicFile) && !is_dir($publicFile)) {
    // Serve static files with correct Content-Type from public directory
    $ext = strtolower(pathinfo($publicFile, PATHINFO_EXTENSION));
    $mimeTypes = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
        'ttf'  => 'font/ttf',
        'pdf'  => 'application/pdf'
    ];
    
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
    }
    readfile($publicFile);
    exit;
}

// Otherwise, rewrite to public/index.php
$_GET['url'] = ltrim($uri, '/');
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/public/index.php';

require_once __DIR__ . '/public/index.php';
