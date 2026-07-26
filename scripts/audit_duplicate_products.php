<?php
/**
 * audit_duplicate_products.php - Audit duplicate products across TechPilot database
 */
define('ROOT_PATH', __DIR__ . '/..');
require_once ROOT_PATH . '/config/database.php';

$pdo = Database::getConnection();
if (!$pdo) die("DB Connection Error\n");

echo "=== TECHPILOT DUPLICATE PRODUCT AUDIT ===\n\n";

// 1. Category Count Audit
$catStats = $pdo->query("
    SELECT c.id, c.name, c.slug, COUNT(p.id) as active_count
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id AND p.status = 'active'
    WHERE c.status = 'active'
    GROUP BY c.id, c.name, c.slug
    ORDER BY c.id
")->fetchAll();

echo "1. CATEGORY DISTRIBUTION (" . count($catStats) . " categories):\n";
$totalProds = 0;
foreach ($catStats as $c) {
    echo sprintf("  - [ID %2d] %-25s (%-18s): %2d active products\n", $c['id'], $c['name'], $c['slug'], $c['active_count']);
    $totalProds += $c['active_count'];
}
echo "Total Active Products: $totalProds\n\n";

// 2. Duplicate Names Audit
$dupNames = $pdo->query("
    SELECT name, COUNT(*) as cnt, GROUP_CONCAT(id ORDER BY id) as ids, GROUP_CONCAT(price ORDER BY id) as prices
    FROM products
    WHERE status = 'active'
    GROUP BY name
    HAVING cnt > 1
    ORDER BY cnt DESC
")->fetchAll();

echo "2. DUPLICATE PRODUCT NAMES (" . count($dupNames) . " duplicates found):\n";
foreach ($dupNames as $d) {
    echo "  - Tên: \"{$d['name']}\" (Xuất hiện {$d['cnt']} lần | IDs: {$d['ids']} | Giá: {$d['prices']})\n";
}
echo "\n";

// 3. Duplicate SKUs Audit
$dupSkus = $pdo->query("
    SELECT sku, COUNT(*) as cnt, GROUP_CONCAT(id) as ids
    FROM products
    WHERE status = 'active' AND sku != '' AND sku IS NOT NULL
    GROUP BY sku
    HAVING cnt > 1
")->fetchAll();

echo "3. DUPLICATE SKUs (" . count($dupSkus) . " duplicates found):\n";
foreach ($dupSkus as $s) {
    echo "  - SKU: {$s['sku']} (IDs: {$s['ids']})\n";
}

// 4. Shared Images Audit
$dupImages = $pdo->query("
    SELECT image, COUNT(*) as cnt, GROUP_CONCAT(DISTINCT category_id) as cats
    FROM products
    WHERE status = 'active'
    GROUP BY image
    HAVING cnt > 1
")->fetchAll();

echo "\n4. SHARED IMAGE PATHS (" . count($dupImages) . " shared image groups):\n";
foreach ($dupImages as $img) {
    echo "  - Path: {$img['image']} (Dùng chung cho {$img['cnt']} sản phẩm | Categories: {$img['cats']})\n";
}

