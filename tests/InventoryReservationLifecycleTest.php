<?php

/**
 * Inventory reservation lifecycle regression tests.
 *
 * Every database object created here is TEMPORARY and scoped to this PDO
 * connection. The real products, orders and inventory logs are never changed.
 */

$rootPath = dirname(__DIR__);
$passed = 0;
$failed = 0;

function assertInventory(bool $condition, string $message): void
{
    global $passed, $failed;

    if ($condition) {
        $passed++;
        echo "[PASS] {$message}\n";
        return;
    }

    $failed++;
    echo "[FAIL] {$message}\n";
}

function createInventoryLogTemporaryTable(PDO $db, bool $validSchema = true): void
{
    $db->exec('DROP TEMPORARY TABLE IF EXISTS `inventory_logs`');

    if (!$validSchema) {
        $db->exec(
            'CREATE TEMPORARY TABLE `inventory_logs` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY
            ) ENGINE=InnoDB'
        );
        return;
    }

    $db->exec(
        'CREATE TEMPORARY TABLE `inventory_logs` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `product_id` INT UNSIGNED NOT NULL,
            `order_id` INT UNSIGNED NULL,
            `type` VARCHAR(50) NOT NULL,
            `quantity_delta` INT NOT NULL,
            `old_stock` INT NOT NULL,
            `new_stock` INT NOT NULL,
            `reason_code` VARCHAR(100) NULL,
            `note` TEXT NULL,
            `reference_type` VARCHAR(50) NULL,
            `reference_id` VARCHAR(100) NULL,
            `created_by` INT UNSIGNED NULL,
            `idempotency_key` VARCHAR(255) NULL,
            `created_at` DATETIME NOT NULL,
            UNIQUE KEY `uq_inventory_idempotency` (`idempotency_key`)
        ) ENGINE=InnoDB'
    );
}

function createInventoryTemporarySchema(PDO $db): void
{
    $db->exec(
        "CREATE TEMPORARY TABLE `products` (
            `id` INT UNSIGNED NOT NULL PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `slug` VARCHAR(255) NOT NULL,
            `image` VARCHAR(255) NULL,
            `price` DECIMAL(15,2) NOT NULL,
            `sale_price` DECIMAL(15,2) NULL,
            `stock` INT NOT NULL,
            `status` VARCHAR(30) NOT NULL
        ) ENGINE=InnoDB"
    );
    $db->exec(
        "CREATE TEMPORARY TABLE `flash_sales` (
            `id` INT UNSIGNED NOT NULL PRIMARY KEY,
            `status` VARCHAR(30) NOT NULL,
            `start_time` DATETIME NOT NULL,
            `end_time` DATETIME NOT NULL
        ) ENGINE=InnoDB"
    );
    $db->exec(
        "CREATE TEMPORARY TABLE `flash_sale_items` (
            `id` INT UNSIGNED NOT NULL PRIMARY KEY,
            `flash_sale_id` INT UNSIGNED NOT NULL,
            `product_id` INT UNSIGNED NOT NULL,
            `discount_price` DECIMAL(15,2) NOT NULL,
            `allocation_quantity` INT NOT NULL,
            `sold_quantity` INT NOT NULL,
            `limit_per_user` INT NOT NULL
        ) ENGINE=InnoDB"
    );
    $db->exec(
        "CREATE TEMPORARY TABLE `coupons` (
            `id` INT UNSIGNED NOT NULL PRIMARY KEY,
            `code` VARCHAR(100) NOT NULL,
            `type` VARCHAR(30) NOT NULL,
            `discount_value` DECIMAL(15,2) NOT NULL,
            `max_discount` DECIMAL(15,2) NULL,
            `min_order_value` DECIMAL(15,2) NOT NULL DEFAULT 0,
            `usage_limit` INT NULL,
            `used_count` INT NOT NULL DEFAULT 0,
            `usage_limit_per_user` INT NULL,
            `status` VARCHAR(30) NOT NULL,
            `start_date` DATETIME NOT NULL,
            `end_date` DATETIME NOT NULL
        ) ENGINE=InnoDB"
    );
    $db->exec(
        "CREATE TEMPORARY TABLE `orders` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `order_code` VARCHAR(100) NOT NULL,
            `user_id` INT UNSIGNED NULL,
            `coupon_id` INT UNSIGNED NULL,
            `customer_name` VARCHAR(255) NOT NULL,
            `phone` VARCHAR(50) NOT NULL,
            `address` TEXT NOT NULL,
            `note` TEXT NULL,
            `payment_method` VARCHAR(30) NOT NULL,
            `payment_status` VARCHAR(30) NOT NULL,
            `subtotal` DECIMAL(15,2) NOT NULL,
            `discount_amount` DECIMAL(15,2) NOT NULL,
            `shipping_fee` DECIMAL(15,2) NOT NULL,
            `total_amount` DECIMAL(15,2) NOT NULL,
            `status` VARCHAR(30) NOT NULL,
            `inventory_status` ENUM('not_reserved','reserved','released','committed') NOT NULL DEFAULT 'reserved',
            `inventory_reserved_at` DATETIME NULL,
            `inventory_released_at` DATETIME NULL,
            `inventory_release_reason` VARCHAR(100) NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB"
    );
    $db->exec(
        "CREATE TEMPORARY TABLE `order_items` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `order_id` INT UNSIGNED NOT NULL,
            `product_id` INT UNSIGNED NOT NULL,
            `product_name` VARCHAR(255) NOT NULL,
            `price` DECIMAL(15,2) NOT NULL,
            `quantity` INT NOT NULL,
            `line_total` DECIMAL(15,2) NOT NULL
        ) ENGINE=InnoDB"
    );
    $db->exec(
        "CREATE TEMPORARY TABLE `flash_sale_reservations` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `flash_sale_item_id` INT UNSIGNED NOT NULL,
            `order_id` BIGINT UNSIGNED NOT NULL,
            `order_item_id` BIGINT UNSIGNED NOT NULL,
            `user_id` INT UNSIGNED NULL,
            `buyer_key` VARCHAR(191) NOT NULL,
            `quantity` INT UNSIGNED NOT NULL,
            `unit_price` DECIMAL(15,2) NOT NULL,
            `status` ENUM('reserved','committed','released') NOT NULL DEFAULT 'reserved',
            `reserved_at` DATETIME NOT NULL,
            `committed_at` DATETIME NULL,
            `released_at` DATETIME NULL,
            `release_reason` VARCHAR(100) NULL,
            UNIQUE KEY `uq_flash_reservation_order_item` (`order_item_id`)
        ) ENGINE=InnoDB"
    );
    $db->exec(
        "CREATE TEMPORARY TABLE `notifications` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NULL,
            `title` VARCHAR(255) NOT NULL,
            `content` TEXT NOT NULL
        ) ENGINE=InnoDB"
    );

    createInventoryLogTemporaryTable($db);
}

function resetInventoryFixture(PDO $db): void
{
    foreach (['inventory_logs', 'notifications', 'flash_sale_reservations', 'order_items', 'orders', 'coupons', 'flash_sale_items', 'flash_sales', 'products'] as $table) {
        $db->exec("DELETE FROM `{$table}`");
    }
}

function seedInventoryProduct(PDO $db, int $id, int $stock, float $price = 1000000, ?float $salePrice = null): void
{
    $stmt = $db->prepare(
        'INSERT INTO products (id, name, slug, image, price, sale_price, stock, status)
         VALUES (:id, :name, :slug, :image, :price, :sale_price, :stock, \'active\')'
    );
    $stmt->execute([
        ':id' => $id,
        ':name' => "Inventory product {$id}",
        ':slug' => "inventory-product-{$id}",
        ':image' => "inventory-{$id}.jpg",
        ':price' => $price,
        ':sale_price' => $salePrice,
        ':stock' => $stock,
    ]);
}

function seedInventoryFlashSale(
    PDO $db,
    int $productId,
    int $allocationQuantity = 5,
    int $limitPerUser = 2,
    float $discountPrice = 700000
): void {
    $db->exec(
        "INSERT INTO flash_sales (id, status, start_time, end_time)
         VALUES (1, 'active', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 1 DAY))"
    );

    $stmt = $db->prepare(
        'INSERT INTO flash_sale_items
            (id, flash_sale_id, product_id, discount_price, allocation_quantity, sold_quantity, limit_per_user)
         VALUES
            (1, 1, :product_id, :discount_price, :allocation_quantity, 0, :limit_per_user)'
    );
    $stmt->execute([
        ':product_id' => $productId,
        ':discount_price' => $discountPrice,
        ':allocation_quantity' => $allocationQuantity,
        ':limit_per_user' => $limitPerUser,
    ]);
}

function seedInventoryCoupon(PDO $db, int $id = 1): void
{
    $stmt = $db->prepare(
        "INSERT INTO coupons
            (id, code, type, discount_value, max_discount, min_order_value, usage_limit, used_count, usage_limit_per_user, status, start_date, end_date)
         VALUES
            (:id, :code, 'percent', 10, 50000, 100000, 10, 0, 1, 'active', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 1 DAY))"
    );
    $stmt->execute([':id' => $id, ':code' => "INV{$id}"]);
}

function inventoryOrderPayload(int $productId, int $quantity, string $paymentMethod = 'COD', ?int $couponId = null): array
{
    return [
        'customer_name' => 'Inventory Test',
        'phone' => '0900000000',
        'address' => 'Temporary table only',
        'payment_method' => $paymentMethod,
        'payment_status' => $paymentMethod === 'VNPAY' ? 'pending' : 'unpaid',
        'subtotal' => 1,
        'discount_amount' => 999999,
        'shipping_fee' => 999999,
        'total_amount' => 1,
        'coupon_id' => $couponId,
        'items' => [
            ['product_id' => $productId, 'quantity' => $quantity, 'price' => 1],
        ],
    ];
}

function scalarInt(PDO $db, string $sql): int
{
    return (int)$db->query($sql)->fetchColumn();
}

function inventoryLogCount(PDO $db, int $orderId, string $type): int
{
    $stmt = $db->prepare('SELECT COUNT(*) FROM inventory_logs WHERE order_id = :order_id AND type = :type');
    $stmt->execute([':order_id' => $orderId, ':type' => $type]);
    return (int)$stmt->fetchColumn();
}

require_once $rootPath . '/config/app.php';
require_once $rootPath . '/config/database.php';
require_once $rootPath . '/app/models/Order.php';
require_once $rootPath . '/app/services/InventoryService.php';

echo "========================================================\n";
echo "=== TECHPILOT INVENTORY RESERVATION LIFECYCLE TEST  ===\n";
echo "========================================================\n\n";

$db = Database::getConnection();
assertInventory($db instanceof PDO, 'Có PDO để chạy toàn bộ lifecycle trên TEMPORARY tables');

if ($db instanceof PDO) {
    $temporaryTables = [
        'inventory_logs',
        'notifications',
        'flash_sale_reservations',
        'order_items',
        'orders',
        'coupons',
        'flash_sale_items',
        'flash_sales',
        'products',
    ];
    $originalUser = $_SESSION['user'] ?? null;
    $hadUser = array_key_exists('user', $_SESSION);

    try {
        foreach ($temporaryTables as $table) {
            $db->exec("DROP TEMPORARY TABLE IF EXISTS `{$table}`");
        }
        createInventoryTemporarySchema($db);
        unset($_SESSION['user']);

        echo "--- 1. Reserve and release lifecycle ---\n";
        resetInventoryFixture($db);
        seedInventoryProduct($db, 1, 10, 1000000, 800000);
        $orderModel = new Order();
        $created = $orderModel->create(inventoryOrderPayload(1, 2));

        assertInventory(is_array($created), 'Tạo đơn hợp lệ thành công');
        if (is_array($created)) {
            $orderId = (int)$created['id'];
            $storedOrder = $db->query("SELECT * FROM orders WHERE id = {$orderId}")->fetch(PDO::FETCH_ASSOC);

            assertInventory(scalarInt($db, 'SELECT stock FROM products WHERE id = 1') === 8, 'Reserve 2 sản phẩm làm stock 10 → 8');
            assertInventory(($storedOrder['inventory_status'] ?? '') === 'reserved', 'Chỉ đánh dấu reserved sau khi giữ hàng thành công');
            assertInventory(!empty($storedOrder['inventory_reserved_at']), 'Ghi thời điểm reserve sau khi trừ kho');
            assertInventory(inventoryLogCount($db, $orderId, 'order_reserve') === 1, 'Reserve tạo đúng một audit log -2');

            $db->beginTransaction();
            $reservedAgain = InventoryService::reserveOrderInventory($db, $orderId);
            $db->commit();
            assertInventory($reservedAgain, 'Reserve lần hai trả về idempotent success');
            assertInventory(scalarInt($db, 'SELECT stock FROM products WHERE id = 1') === 8, 'Reserve lần hai không trừ stock lần nữa');
            assertInventory(inventoryLogCount($db, $orderId, 'order_reserve') === 1, 'Reserve lần hai không tạo log trùng');

            $db->beginTransaction();
            $released = InventoryService::releaseOrderInventory($db, $orderId, 'test_cancel');
            $db->commit();
            assertInventory($released, 'Release đơn đã reserve thành công');
            assertInventory(scalarInt($db, 'SELECT stock FROM products WHERE id = 1') === 10, 'Release trả stock 8 → 10');
            assertInventory(inventoryLogCount($db, $orderId, 'order_release') === 1, 'Release tạo đúng một audit log +2');

            $db->beginTransaction();
            $releasedAgain = InventoryService::releaseOrderInventory($db, $orderId, 'test_cancel_again');
            $db->commit();
            assertInventory($releasedAgain, 'Release lần hai trả về idempotent success');
            assertInventory(scalarInt($db, 'SELECT stock FROM products WHERE id = 1') === 10, 'Release lần hai không cộng stock lần nữa');
            assertInventory(inventoryLogCount($db, $orderId, 'order_release') === 1, 'Release lần hai không tạo log trùng');
        }

        echo "\n--- 2. Commit lifecycle ---\n";
        resetInventoryFixture($db);
        seedInventoryProduct($db, 2, 5);
        $created = (new Order())->create(inventoryOrderPayload(2, 1));
        assertInventory(is_array($created), 'Tạo đơn để kiểm tra commit thành công');
        if (is_array($created)) {
            $orderId = (int)$created['id'];
            assertInventory(scalarInt($db, 'SELECT stock FROM products WHERE id = 2') === 4, 'Reserve trước commit làm stock 5 → 4');

            $db->beginTransaction();
            $committed = InventoryService::commitOrderInventory($db, $orderId);
            $db->commit();
            $inventoryStatus = (string)$db->query("SELECT inventory_status FROM orders WHERE id = {$orderId}")->fetchColumn();
            assertInventory($committed && $inventoryStatus === 'committed', 'Hoàn thành đơn chuyển reserved → committed');
            assertInventory(scalarInt($db, 'SELECT stock FROM products WHERE id = 2') === 4, 'Commit không trừ stock lần hai');
        }

        echo "\n--- 3. VNPay failed lifecycle ---\n";
        resetInventoryFixture($db);
        seedInventoryProduct($db, 3, 7);
        seedInventoryFlashSale($db, 3);
        $orderModel = new Order();
        $created = $orderModel->create(inventoryOrderPayload(3, 2, 'VNPAY'));
        assertInventory(is_array($created), 'Tạo đơn VNPay pending thành công');
        if (is_array($created)) {
            $orderId = (int)$created['id'];
            $orderCode = (string)$created['order_code'];
            assertInventory(scalarInt($db, 'SELECT stock FROM products WHERE id = 3') === 5, 'VNPay pending giữ stock 7 → 5');
            assertInventory(scalarInt($db, 'SELECT sold_quantity FROM flash_sale_items WHERE id = 1') === 2, 'VNPay pending giữ 2 suất Flash Sale');
            assertInventory((string)$db->query("SELECT status FROM flash_sale_reservations WHERE order_id = {$orderId}")->fetchColumn() === 'reserved', 'Quota VNPay pending ở trạng thái reserved');
            assertInventory($orderModel->updatePayment($orderCode, 'failed'), 'Callback VNPay failed xử lý thành công');
            $failedOrder = $db->query("SELECT status, inventory_status FROM orders WHERE id = {$orderId}")->fetch(PDO::FETCH_ASSOC);
            assertInventory(scalarInt($db, 'SELECT stock FROM products WHERE id = 3') === 7, 'VNPay failed hoàn stock 5 → 7');
            assertInventory(scalarInt($db, 'SELECT sold_quantity FROM flash_sale_items WHERE id = 1') === 0, 'VNPay failed trả 2 suất Flash Sale');
            assertInventory((string)$db->query("SELECT status FROM flash_sale_reservations WHERE order_id = {$orderId}")->fetchColumn() === 'released', 'Quota VNPay failed chuyển sang released');
            assertInventory(($failedOrder['status'] ?? '') === 'cancelled', 'VNPay failed hủy đơn');
            assertInventory(($failedOrder['inventory_status'] ?? '') === 'released', 'VNPay failed đánh dấu inventory released');
            assertInventory(inventoryLogCount($db, $orderId, 'order_reserve') === 1, 'VNPay failed vẫn có reserve log ban đầu');
            assertInventory(inventoryLogCount($db, $orderId, 'order_release') === 1, 'VNPay failed có đúng một release log');
            assertInventory($orderModel->updatePayment($orderCode, 'failed'), 'Callback VNPay failed lặp lại vẫn thành công');
            assertInventory(scalarInt($db, 'SELECT sold_quantity FROM flash_sale_items WHERE id = 1') === 0, 'Callback failed lặp lại không hoàn quota lần hai');
        }

        echo "\n--- 3B. VNPay paid Flash Sale lifecycle ---\n";
        resetInventoryFixture($db);
        seedInventoryProduct($db, 31, 4);
        seedInventoryFlashSale($db, 31);
        $orderModel = new Order();
        $created = $orderModel->create(inventoryOrderPayload(31, 1, 'VNPAY'));
        assertInventory(is_array($created), 'Tạo đơn VNPay Flash Sale để kiểm tra paid thành công');
        if (is_array($created)) {
            $orderId = (int)$created['id'];
            $orderCode = (string)$created['order_code'];
            assertInventory(scalarInt($db, 'SELECT sold_quantity FROM flash_sale_items WHERE id = 1') === 1, 'Đơn mới giữ đúng 1 suất Flash Sale');
            assertInventory($orderModel->updatePayment($orderCode, 'paid'), 'Callback VNPay paid xử lý thành công');
            assertInventory((string)$db->query("SELECT status FROM flash_sale_reservations WHERE order_id = {$orderId}")->fetchColumn() === 'committed', 'VNPay paid chốt quota thành committed');
            assertInventory(scalarInt($db, 'SELECT sold_quantity FROM flash_sale_items WHERE id = 1') === 1, 'Commit giữ nguyên số suất đã bán');
            assertInventory($orderModel->updatePayment($orderCode, 'paid'), 'Callback VNPay paid lặp lại vẫn thành công');
            assertInventory(scalarInt($db, 'SELECT sold_quantity FROM flash_sale_items WHERE id = 1') === 1, 'Callback paid lặp lại không cộng quota lần hai');
        }

        echo "\n--- 4. Insufficient-stock rollback ---\n";
        resetInventoryFixture($db);
        seedInventoryProduct($db, 4, 1);
        seedInventoryCoupon($db, 1);
        $created = (new Order())->create(inventoryOrderPayload(4, 2, 'COD', 1));
        assertInventory($created === false, 'Đặt vượt tồn kho bị từ chối');
        assertInventory(scalarInt($db, 'SELECT stock FROM products WHERE id = 4') === 1, 'Thiếu kho không thay đổi stock');
        assertInventory(scalarInt($db, 'SELECT COUNT(*) FROM orders') === 0, 'Thiếu kho không để lại order header');
        assertInventory(scalarInt($db, 'SELECT COUNT(*) FROM order_items') === 0, 'Thiếu kho không để lại order item');
        assertInventory(scalarInt($db, 'SELECT used_count FROM coupons WHERE id = 1') === 0, 'Thiếu kho không tăng coupon usage');

        echo "\n--- 5. Audit-log failure rollback ---\n";
        resetInventoryFixture($db);
        seedInventoryProduct($db, 5, 3);
        seedInventoryCoupon($db, 1);
        createInventoryLogTemporaryTable($db, false);
        $created = (new Order())->create(inventoryOrderPayload(5, 1, 'COD', 1));
        assertInventory($created === false, 'Ghi audit log lỗi làm toàn bộ create order thất bại');
        assertInventory(scalarInt($db, 'SELECT stock FROM products WHERE id = 5') === 3, 'Audit lỗi rollback thay đổi stock');
        assertInventory(scalarInt($db, 'SELECT COUNT(*) FROM orders') === 0, 'Audit lỗi rollback order header');
        assertInventory(scalarInt($db, 'SELECT COUNT(*) FROM order_items') === 0, 'Audit lỗi rollback order items');
        assertInventory(scalarInt($db, 'SELECT used_count FROM coupons WHERE id = 1') === 0, 'Audit lỗi rollback coupon usage');
        createInventoryLogTemporaryTable($db);

        echo "\n--- 6. Legacy false-reserved guard ---\n";
        resetInventoryFixture($db);
        seedInventoryProduct($db, 6, 10);
        $db->exec(
            "INSERT INTO orders
                (order_code, customer_name, phone, address, payment_method, payment_status, subtotal, discount_amount, shipping_fee, total_amount, status, inventory_status, inventory_reserved_at)
             VALUES
                ('LEGACY-FALSE-RESERVED', 'Legacy', '0900000000', 'Legacy', 'COD', 'unpaid', 2000000, 0, 0, 2000000, 'pending', 'reserved', NOW())"
        );
        $legacyOrderId = (int)$db->lastInsertId();
        $db->exec(
            "INSERT INTO order_items (order_id, product_id, product_name, price, quantity, line_total)
             VALUES ({$legacyOrderId}, 6, 'Inventory product 6', 1000000, 2, 2000000)"
        );

        $legacyReserveRejected = false;
        try {
            $db->beginTransaction();
            InventoryService::reserveOrderInventory($db, $legacyOrderId);
            $db->commit();
        } catch (Throwable) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $legacyReserveRejected = true;
        }
        assertInventory($legacyReserveRejected, 'Không tin trạng thái reserved legacy khi thiếu reserve log');
        assertInventory(scalarInt($db, 'SELECT stock FROM products WHERE id = 6') === 10, 'Guard legacy không tự ý thay đổi stock');

        $legacyReleaseRejected = false;
        try {
            $db->beginTransaction();
            InventoryService::releaseOrderInventory($db, $legacyOrderId, 'legacy_guard');
            $db->commit();
        } catch (Throwable) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $legacyReleaseRejected = true;
        }
        assertInventory($legacyReleaseRejected, 'Không hoàn kho cho order legacy thiếu bằng chứng đã reserve');
        assertInventory(scalarInt($db, 'SELECT stock FROM products WHERE id = 6') === 10, 'Release guard không làm stock 10 → 12 giả');
        $legacyStatus = (string)$db->query("SELECT inventory_status FROM orders WHERE id = {$legacyOrderId}")->fetchColumn();
        assertInventory($legacyStatus === 'reserved', 'Order legacy nghi vấn được giữ nguyên để audit thủ công');

        echo "\n--- 7. Inventory-status default migration ---\n";
        $migrationPath = $rootPath . '/database/migrations/2026_08_01_000004_fix_order_inventory_status_default.php';
        $migrationClass = 'Migration_2026_08_01_000004_fix_order_inventory_status_default';
        assertInventory(file_exists($migrationPath), 'Migration đổi default inventory_status tồn tại');
        require_once $migrationPath;
        assertInventory(class_exists($migrationClass), 'Migration class đúng convention của runner');

        $beforeRows = $db->query(
            'SELECT id, order_code, inventory_status, inventory_reserved_at
             FROM orders ORDER BY id'
        )->fetchAll(PDO::FETCH_ASSOC);

        assertInventory($migrationClass::up($db), 'Migration up() chạy thành công trên legacy default reserved');
        $upColumn = $db->query("SHOW COLUMNS FROM orders LIKE 'inventory_status'")->fetch(PDO::FETCH_ASSOC);
        assertInventory(($upColumn['Default'] ?? null) === 'not_reserved', 'Migration up() đổi default thành not_reserved');
        $afterUpRows = $db->query(
            'SELECT id, order_code, inventory_status, inventory_reserved_at
             FROM orders ORDER BY id'
        )->fetchAll(PDO::FETCH_ASSOC);
        assertInventory($afterUpRows === $beforeRows, 'Migration up() không sửa trạng thái hay dữ liệu đơn cũ');
        assertInventory($migrationClass::up($db), 'Migration up() chạy lần hai vẫn idempotent');

        assertInventory($migrationClass::down($db), 'Migration down() khôi phục được legacy default');
        $downColumn = $db->query("SHOW COLUMNS FROM orders LIKE 'inventory_status'")->fetch(PDO::FETCH_ASSOC);
        assertInventory(($downColumn['Default'] ?? null) === 'reserved', 'Migration down() chỉ khôi phục default reserved');
        assertInventory($migrationClass::up($db), 'Migration up() chạy lại sau rollback thành công');

        $seedSource = file_get_contents($rootPath . '/database/seed.sql');
        assertInventory(
            str_contains(
                $seedSource,
                "`inventory_status` enum('not_reserved','reserved','released','committed') NOT NULL DEFAULT 'not_reserved'"
            ),
            'Fresh-install seed dùng default not_reserved'
        );
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        assertInventory(false, 'Lifecycle suite không phát sinh exception ngoài dự kiến: ' . $e->getMessage());
    } finally {
        foreach ($temporaryTables as $table) {
            try {
                $db->exec("DROP TEMPORARY TABLE IF EXISTS `{$table}`");
            } catch (Throwable) {
            }
        }

        if ($hadUser) {
            $_SESSION['user'] = $originalUser;
        } else {
            unset($_SESSION['user']);
        }
    }
}

echo "\n========================================================\n";
echo "Inventory Lifecycle Results: {$passed} passed, {$failed} failed\n";
echo "========================================================\n";

exit($failed === 0 ? 0 : 1);
