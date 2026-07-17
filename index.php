<?php

/**
 * Entry point gốc cho XAMPP/Apache khi truy cập /techpilot/
 * Chuyển hướng thẳng sang thư mục public để không bị 404.
 */

$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
$queryString = $_SERVER['QUERY_STRING'] ?? '';

if (strpos($currentPath, '/public/') === false && $currentPath !== '/public') {
    $redirectTarget = 'public/' . ltrim(str_replace('/Techpilot', '', $currentPath), '/');
    if ($redirectTarget === 'public/') {
        $redirectTarget = 'public/';
    }

    if ($queryString !== '') {
        $redirectTarget .= '?' . $queryString;
    }

    header('Location: /Techpilot/' . ltrim($redirectTarget, '/'));
    exit;
}

require __DIR__ . '/public/index.php';
