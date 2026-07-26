<?php
/**
 * Seed Active Flash Sale Campaign — TechPilot
 * Seeds an active Flash Sale campaign starting now and ending in 7 days,
 * associated with 6 authentic verified Windows Laptops.
 */

require_once __DIR__ . '/../config/database.php';

$db = Database::getConnection();
if (!$db) {
    echo "ERROR: Cannot connect to database\n";
    exit(1);
}

echo "=== Seeding Active Flash Sale Campaign ===\n";

// Clear previous flash sale campaigns
$db->exec("DELETE FROM flash_sale_items");
$db->exec("DELETE FROM flash_sales");

// Create active campaign
$startTime = date('Y-m-d H:i:s', strtotime('-1 hour'));
$endTime   = date('Y-m-d H:i:s', strtotime('+7 days'));

$stmtFs = $db->prepare("
    INSERT INTO flash_sales (title, slug, start_time, end_time, status, created_at, updated_at)
    VALUES ('Flash Sale Bùng Nổ TechPilot', 'flash-sale-bung-no-techpilot', :start_time, :end_time, 'active', NOW(), NOW())
");
$stmtFs->execute([':start_time' => $startTime, ':end_time' => $endTime]);
$flashSaleId = (int)$db->lastInsertId();

// Select 6 active verified products (laptops)
$products = $db->query("
    SELECT id, name, price, stock
    FROM products
    WHERE status = 'active' AND verification_status = 'verified' AND category_id = 1
    ORDER BY id ASC
    LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

if (empty($products)) {
    // Fallback to any 6 verified products
    $products = $db->query("
        SELECT id, name, price, stock
        FROM products
        WHERE status = 'active' AND verification_status = 'verified'
        ORDER BY id ASC
        LIMIT 6
    ")->fetchAll(PDO::FETCH_ASSOC);
}

$stmtFsi = $db->prepare("
    INSERT INTO flash_sale_items (flash_sale_id, product_id, discount_price, allocation_quantity)
    VALUES (:flash_sale_id, :product_id, :discount_price, :allocation_quantity)
");

foreach ($products as $p) {
    $discountPrice = round($p['price'] * 0.9); // 10% off
    $allocQty = min(20, (int)$p['stock']);
    $stmtFsi->execute([
        ':flash_sale_id'       => $flashSaleId,
        ':product_id'          => $p['id'],
        ':discount_price'      => $discountPrice,
        ':allocation_quantity' => $allocQty
    ]);
    echo "Added to Flash Sale: [#{$p['id']}] {$p['name']} - Sale: " . number_format($discountPrice) . "đ\n";
}

echo "\nFlash Sale Campaign #$flashSaleId successfully activated! (Ends: $endTime)\n";
