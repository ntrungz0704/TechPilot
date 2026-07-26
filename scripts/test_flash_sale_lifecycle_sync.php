<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Product.php';
require_once __DIR__ . '/../app/models/Order.php';

$db = Database::getConnection();
echo "=== FLASH SALE REAL-TIME LIFECYCLE & SYNC TEST ===\n\n";

$productModel = new Product();
$activeFs = $productModel->getFlashSale();

echo "1. Active Campaign Query Test:\n";
echo "   Active Flash Sale Products Count: " . count($activeFs) . " " . (count($activeFs) > 0 ? "[PASS]" : "[FAIL]") . "\n";
if (!empty($activeFs)) {
    echo "   Sample Active Item: {$activeFs[0]['name']} | Price: " . number_format($activeFs[0]['price']) . "đ | Sale: " . number_format($activeFs[0]['discount_price']) . "đ\n";
}

echo "\n2. Simulation Test - Expired Campaign:\n";
$db->beginTransaction();
try {
    // Force end_time to past
    $db->exec("UPDATE flash_sales SET end_time = '2026-01-01 00:00:00'");
    
    $expiredFs = $productModel->getFlashSale();
    echo "   Expired Query Count: " . count($expiredFs) . " " . (count($expiredFs) === 0 ? "[PASS - Cleanly Returns Empty]" : "[FAIL]") . "\n";

    // Test Admin Status Calculation
    $fsRow = $db->query("SELECT * FROM flash_sales ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $now = date('Y-m-d H:i:s');
    $isAdminEnded = ($fsRow['end_time'] < $now);
    echo "   Admin Badge Status: " . ($isAdminEnded ? "Đã kết thúc [PASS]" : "Đang chạy [FAIL]") . "\n";

} finally {
    $db->rollBack();
}

echo "\n3. Post-Rollback Restored State:\n";
$restoredFs = $productModel->getFlashSale();
echo "   Restored Flash Sale Count: " . count($restoredFs) . " " . (count($restoredFs) > 0 ? "[PASS]" : "[FAIL]") . "\n";
