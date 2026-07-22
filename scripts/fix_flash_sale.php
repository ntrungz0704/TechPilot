<?php
/**
 * Script tạo bảng flash_sale_items và insert dữ liệu mẫu
 * Chạy: php scripts/fix_flash_sale.php
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

$db = Database::getConnection();
if (!$db) {
    echo "FAILED: Khong ket noi duoc database!\n";
    exit(1);
}

echo "=== TAO BANG flash_sale_items ===\n";

// Tạo bảng (bỏ FK tới product_variants vì bảng đó chưa có)
$sql = "CREATE TABLE IF NOT EXISTS flash_sale_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    flash_sale_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    variant_id INT UNSIGNED DEFAULT NULL,
    discount_price DECIMAL(12, 0) NOT NULL,
    allocation_quantity INT NOT NULL DEFAULT 10,
    sold_quantity INT NOT NULL DEFAULT 0,
    limit_per_user INT NOT NULL DEFAULT 2,
    CONSTRAINT fk_flash_items_sale FOREIGN KEY (flash_sale_id) REFERENCES flash_sales (id) ON DELETE CASCADE,
    CONSTRAINT fk_flash_items_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
    UNIQUE KEY uq_flash_sale_product (flash_sale_id, product_id),
    INDEX idx_flash_items_product (product_id, flash_sale_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $db->exec($sql);
    echo "OK  => Bang flash_sale_items da duoc tao!\n";
} catch (PDOException $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// Kiểm tra đã có flash_sales chưa
$fsCount = $db->query("SELECT COUNT(*) FROM flash_sales")->fetchColumn();
echo "INFO: flash_sales co $fsCount ban ghi\n";

if ($fsCount == 0) {
    // Tạo flash sale mẫu nếu chưa có
    $db->exec("INSERT INTO flash_sales (title, start_time, end_time, status) VALUES 
        ('Flash Sale Hom Nay', NOW() - INTERVAL 1 HOUR, NOW() + INTERVAL 23 HOUR, 'active')");
    echo "OK  => Da tao 1 flash_sale mau\n";
}

$fsId = $db->query("SELECT id FROM flash_sales WHERE status='active' LIMIT 1")->fetchColumn();
if (!$fsId) {
    $fsId = $db->query("SELECT id FROM flash_sales LIMIT 1")->fetchColumn();
}
echo "INFO: Dung flash_sale id = $fsId\n";

// Lấy 6 sản phẩm bất kỳ để thêm vào flash sale
$products = $db->query("SELECT id, price FROM products ORDER BY id ASC LIMIT 6")->fetchAll();
echo "INFO: Tim thay " . count($products) . " san pham\n";

$inserted = 0;
foreach ($products as $p) {
    $discountPrice = (int)($p['price'] * 0.8); // giảm 20%
    try {
        $stmt = $db->prepare("INSERT IGNORE INTO flash_sale_items 
            (flash_sale_id, product_id, variant_id, discount_price, allocation_quantity, sold_quantity, limit_per_user)
            VALUES (:fs_id, :p_id, NULL, :disc_price, 10, 0, 2)");
        $stmt->execute([
            ':fs_id'      => $fsId,
            ':p_id'       => $p['id'],
            ':disc_price' => $discountPrice,
        ]);
        $inserted++;
    } catch (PDOException $e) {
        echo "WARN: San pham id={$p['id']} bi loi: " . $e->getMessage() . "\n";
    }
}

echo "OK  => Da them $inserted san pham vao flash_sale_items\n";

// Kiểm tra lại
$count = $db->query("SELECT COUNT(*) FROM flash_sale_items")->fetchColumn();
echo "\n=== KET QUA CUOI ===\n";
echo "flash_sale_items: $count ban ghi\n";
echo "DONE! Reload trang web de kiem tra.\n";
