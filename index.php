<?php

/**
 * Entry point gốc cho XAMPP/Apache khi truy cập /techpilot/
 * Chuyển hướng thẳng sang thư mục public để không bị 404.
 */

$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
$queryString = $_SERVER['QUERY_STRING'] ?? '';

// Lấy phần đường dẫn thư mục gốc một cách động (ví dụ: /TechPilot hoặc /techpilot)
$baseDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');

// Kiểm tra xem URL đã chứa /public/ hoặc /public chưa
if (stripos($currentPath, '/public/') === false && rtrim(strtolower($currentPath), '/') !== rtrim(strtolower($baseDir . '/public'), '/')) {
    // Lấy phần đường dẫn tương đối phía sau baseDir
    $relativeClass = '';
    if ($baseDir !== '' && stripos($currentPath, $baseDir) === 0) {
        $relativeClass = substr($currentPath, strlen($baseDir));
    } else {
        $relativeClass = $currentPath;
    }

    $redirectTarget = $baseDir . '/public/' . ltrim($relativeClass, '/');

    if ($queryString !== '') {
        $redirectTarget .= '?' . $queryString;
    }

    header('Location: ' . $redirectTarget);
    exit;
}

require __DIR__ . '/public/index.php';
