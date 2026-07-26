<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once ROOT_PATH . '/config/database.php';

$pdo = Database::getConnection();
if (!$pdo) die("DB error\n");

echo "=== DIAGNOSING PRODUCTS BY CATEGORY AND NAME ===\n\n";

$categories = $pdo->query("SELECT id, name, slug FROM categories WHERE status = 'active' ORDER BY id ASC")->fetchAll();

foreach ($categories as $cat) {
    echo "CATEGORY [ID {$cat['id']}] {$cat['name']} ({$cat['slug']}):\n";
    $prods = $pdo->query("
        SELECT p.id, p.name, p.image, p.category_id, b.name as brand_name
        FROM products p
        LEFT JOIN brands b ON p.brand_id = b.id
        WHERE p.category_id = {$cat['id']} AND p.status = 'active'
        ORDER BY p.id ASC
    ")->fetchAll();

    echo "  Total Active Products: " . count($prods) . "\n";
    echo "  First 5 Product Names & Images:\n";
    foreach (array_slice($prods, 0, 5) as $p) {
        echo sprintf("    - [ID %3d] Name: %-45s | Image: %s\n", $p['id'], $p['name'], $p['image']);
    }
    echo "\n";
}
