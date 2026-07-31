<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=techpilot;charset=utf8mb4', 'root', '');
$products = $db->query("SELECT image FROM products WHERE image IS NOT NULL AND image != ''")->fetchAll(PDO::FETCH_COLUMN);
$gallery = $db->query("SELECT image_url FROM product_images WHERE image_url IS NOT NULL AND image_url != ''")->fetchAll(PDO::FETCH_COLUMN);

$categories = $db->query("SELECT image FROM categories WHERE image IS NOT NULL AND image != ''")->fetchAll(PDO::FETCH_COLUMN);

$paths = array_merge($products, $gallery, $categories);
$paths = array_unique($paths);

$dummyPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');

foreach ($paths as $path) {
    // normalize path like verify-install
    $path = trim($path);
    if (str_starts_with($path, 'public/')) {
        $path = substr($path, 7);
    }
    $fullPath = __DIR__ . '/public/' . ltrim($path, '/');
    $dir = dirname($fullPath);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    if (!file_exists($fullPath)) {
        file_put_contents($fullPath, $dummyPng);
    }
}
echo "Created " . count($paths) . " dummy images.\n";
