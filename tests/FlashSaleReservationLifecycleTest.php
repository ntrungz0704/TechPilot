<?php

/**
 * Flash Sale quota reservation lifecycle tests.
 *
 * Toàn bộ integration dùng TEMPORARY tables, không sửa dữ liệu runtime.
 */

$rootPath = dirname(__DIR__);
$passed = 0;
$failed = 0;

function assertFlashReservation(bool $condition, string $message): void
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

function flashReservationCount(PDO $db, int $orderId): int
{
    $stmt = $db->prepare('SELECT COUNT(*) FROM flash_sale_reservations WHERE order_id = :order_id');
    $stmt->execute([':order_id' => $orderId]);
    return (int)$stmt->fetchColumn();
}

require_once $rootPath . '/config/app.php';
require_once $rootPath . '/config/database.php';
require_once $rootPath . '/app/core/helpers.php';

echo "========================================================\n";
echo "=== TECHPILOT FLASH SALE RESERVATION LIFECYCLE TEST  ===\n";
echo "========================================================\n\n";

$migrationName = '2026_08_01_000006_create_flash_sale_reservations.php';
$migrationPath = $rootPath . '/database/migrations/' . $migrationName;
$servicePath = $rootPath . '/app/services/FlashSaleService.php';

echo "--- 1. Source contracts ---\n";
assertFlashReservation(file_exists($migrationPath), 'Migration tạo flash_sale_reservations tồn tại');
assertFlashReservation(file_exists($servicePath), 'FlashSaleService tồn tại');

$seedSource = file_get_contents($rootPath . '/database/seed.sql');
$orderSource = file_get_contents($rootPath . '/app/models/Order.php');
$adminOrderSource = file_get_contents($rootPath . '/app/controllers/AdminOrderController.php');
$profileSource = file_get_contents($rootPath . '/app/controllers/ProfileController.php');
$adminFlashSaleSource = file_get_contents($rootPath . '/app/controllers/AdminFlashSaleController.php');
$adminFlashSaleIndexSource = file_get_contents($rootPath . '/app/views/admin/flash_sales/index.php');

assertFlashReservation(
    str_contains($seedSource, 'CREATE TABLE `flash_sale_reservations`')
        && str_contains($seedSource, $migrationName),
    'Fresh-install seed chứa reservation table và migration baseline'
);
assertFlashReservation(
    str_contains($orderSource, 'FlashSaleService::quoteForPurchase')
        && str_contains($orderSource, 'FlashSaleService::reserveOrderItem'),
    'Order::create quote và reserve quota trong transaction'
);
assertFlashReservation(
    str_contains($orderSource, 'FlashSaleService::commitOrderReservations')
        && str_contains($orderSource, 'FlashSaleService::releaseOrderReservations'),
    'VNPay commit/release quota cùng vòng đời thanh toán'
);
assertFlashReservation(
    str_contains($adminOrderSource, 'FlashSaleService::commitOrderReservations')
        && str_contains($adminOrderSource, 'FlashSaleService::releaseOrderReservations'),
    'Admin COD/cancel commit hoặc release quota'
);
assertFlashReservation(
    str_contains($profileSource, 'FlashSaleService::releaseOrderReservations'),
    'Khách hủy đơn release quota'
);
assertFlashReservation(
    !str_contains($adminFlashSaleSource, 'DELETE FROM flash_sale_items WHERE flash_sale_id = :id')
        && str_contains($adminFlashSaleSource, 'FlashSaleService::assertItemRemovable'),
    'Admin không xóa-tạo lại item đã có lịch sử quota'
);
assertFlashReservation(
    str_contains($adminFlashSaleSource, 'FlashSaleService::auditQuotaCounters')
        && str_contains($adminFlashSaleIndexSource, 'lệch sổ quota'),
    'Admin có cảnh báo khi bộ đếm và sổ quota bị lệch'
);

echo "\n--- 2. Isolated migration contract ---\n";
$db = Database::getConnection();
assertFlashReservation($db instanceof PDO, 'Có PDO để chạy migration/lifecycle trên TEMPORARY tables');

if ($db instanceof PDO && file_exists($migrationPath)) {
    try {
        $db->exec('DROP TEMPORARY TABLE IF EXISTS `flash_sale_reservations`');
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
                `reserved_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `committed_at` DATETIME NULL,
                `released_at` DATETIME NULL,
                `release_reason` VARCHAR(100) NULL,
                UNIQUE KEY `uq_flash_reservation_order_item` (`order_item_id`),
                KEY `idx_flash_reservation_item_status` (`flash_sale_item_id`, `status`),
                KEY `idx_flash_reservation_buyer` (`flash_sale_item_id`, `buyer_key`, `status`),
                KEY `idx_flash_reservation_order` (`order_id`, `status`),
                KEY `idx_flash_reservation_user` (`user_id`)
            ) ENGINE=InnoDB"
        );

        require_once $migrationPath;
        $migrationClass = 'Migration_2026_08_01_000006_create_flash_sale_reservations';
        assertFlashReservation(class_exists($migrationClass), 'Migration class đúng convention của runner');
        assertFlashReservation($migrationClass::up($db) === true, 'Migration chấp nhận reservation schema đúng contract');
        assertFlashReservation($migrationClass::up($db) === true, 'Migration up() chạy lần hai vẫn idempotent');

        $db->exec(
            "INSERT INTO flash_sale_reservations
                (flash_sale_item_id, order_id, order_item_id, user_id, buyer_key, quantity, unit_price, reserved_at)
             VALUES (1, 1, 1, NULL, 'guest:test', 1, 700000, NOW())"
        );
        $rollbackRefused = false;
        try {
            $migrationClass::down($db);
        } catch (RuntimeException) {
            $rollbackRefused = true;
        }
        assertFlashReservation($rollbackRefused, 'Migration từ chối rollback khi đang có reservation');
        $db->exec('DELETE FROM flash_sale_reservations');
        assertFlashReservation($migrationClass::down($db) === true, 'Migration rollback được khi reservation table rỗng');
    } catch (Throwable $e) {
        assertFlashReservation(false, 'Migration contract không phát sinh exception: ' . $e->getMessage());
    } finally {
        $db->exec('DROP TEMPORARY TABLE IF EXISTS `flash_sale_reservations`');
    }
}

echo "\n--- 3. Isolated service lifecycle ---\n";

if ($db instanceof PDO && file_exists($servicePath)) {
    try {
        foreach (['flash_sale_reservations', 'order_items', 'orders', 'flash_sale_items', 'flash_sales', 'products'] as $table) {
            $db->exec("DROP TEMPORARY TABLE IF EXISTS `{$table}`");
        }

        $db->exec(
            "CREATE TEMPORARY TABLE `products` (
                `id` INT UNSIGNED NOT NULL PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `price` DECIMAL(15,2) NOT NULL,
                `sale_price` DECIMAL(15,2) NULL,
                `status` VARCHAR(30) NOT NULL
            ) ENGINE=InnoDB"
        );
        $db->exec(
            "CREATE TEMPORARY TABLE `flash_sales` (
                `id` INT UNSIGNED NOT NULL PRIMARY KEY,
                `title` VARCHAR(255) NOT NULL,
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
            "CREATE TEMPORARY TABLE `orders` (
                `id` BIGINT UNSIGNED NOT NULL PRIMARY KEY,
                `user_id` INT UNSIGNED NULL,
                `phone` VARCHAR(50) NOT NULL
            ) ENGINE=InnoDB"
        );
        $db->exec(
            "CREATE TEMPORARY TABLE `order_items` (
                `id` BIGINT UNSIGNED NOT NULL PRIMARY KEY,
                `order_id` BIGINT UNSIGNED NOT NULL,
                `product_id` INT UNSIGNED NULL,
                `quantity` INT NOT NULL,
                `price` DECIMAL(15,2) NOT NULL
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
            "INSERT INTO products VALUES
                (1, 'Product 1', 1000000, 900000, 'active'),
                (2, 'Product 2', 900000, NULL, 'active')"
        );
        $db->exec(
            "INSERT INTO flash_sales VALUES
                (1, 'Campaign 1', 'active', DATE_SUB(NOW(), INTERVAL 1 HOUR), DATE_ADD(NOW(), INTERVAL 1 HOUR))"
        );
        $db->exec("INSERT INTO flash_sale_items VALUES (1, 1, 1, 700000, 3, 0, 2)");
        $db->exec(
            "INSERT INTO orders (id, user_id, phone) VALUES
                (100, 10, '0900 111 222'),
                (101, 11, '0900 333 444'),
                (102, 12, '0900 555 666'),
                (103, NULL, '0900 777 888')"
        );
        $db->exec(
            "INSERT INTO order_items (id, order_id, product_id, quantity, price) VALUES
                (1000, 100, 1, 2, 700000),
                (1001, 101, 1, 1, 700000),
                (1002, 102, 1, 2, 700000),
                (1003, 103, 1, 1, 700000)"
        );

        require_once $servicePath;

        $transactionGuarded = false;
        try {
            FlashSaleService::quoteForPurchase($db, 1, 1, 'user:10');
        } catch (LogicException) {
            $transactionGuarded = true;
        }
        assertFlashReservation($transactionGuarded, 'Service từ chối xử lý quota ngoài transaction');

        $buyer10 = FlashSaleService::buyerKey(10, '0900 111 222');
        $guestA = FlashSaleService::buyerKey(null, '0900 777 888');
        $guestB = FlashSaleService::buyerKey(null, '0900777888');
        assertFlashReservation($buyer10 === 'user:10', 'User đăng nhập dùng buyer_key ổn định theo ID');
        assertFlashReservation($guestA === $guestB && str_starts_with($guestA, 'guest:'), 'Guest phone được chuẩn hóa và hash ổn định');

        $db->beginTransaction();
        $quote = FlashSaleService::quoteForPurchase($db, 1, 2, $buyer10);
        assertFlashReservation(($quote['status'] ?? '') === 'eligible' && (int)($quote['item']['id'] ?? 0) === 1, 'Buyer đầu tiên quote được 2 suất');
        $reserved = FlashSaleService::reserveOrderItem(
            $db,
            1,
            100,
            1000,
            10,
            $buyer10,
            2,
            700000.0
        );
        $db->commit();
        assertFlashReservation($reserved, 'Reserve quota thành công');
        assertFlashReservation((int)$db->query('SELECT sold_quantity FROM flash_sale_items WHERE id = 1')->fetchColumn() === 2, 'Reserve tăng sold_quantity 0 → 2');
        assertFlashReservation(flashReservationCount($db, 100) === 1, 'Reserve tạo đúng một reservation');

        $db->beginTransaction();
        $reservedAgain = FlashSaleService::reserveOrderItem($db, 1, 100, 1000, 10, $buyer10, 2, 700000.0);
        $db->commit();
        assertFlashReservation($reservedAgain, 'Reserve lặp trả về idempotent success');
        assertFlashReservation((int)$db->query('SELECT sold_quantity FROM flash_sale_items WHERE id = 1')->fetchColumn() === 2, 'Reserve lặp không tăng quota lần hai');

        $db->beginTransaction();
        $sameBuyerQuote = FlashSaleService::quoteForPurchase($db, 1, 1, $buyer10);
        $db->rollBack();
        assertFlashReservation(($sameBuyerQuote['status'] ?? '') === 'limit_reached', 'Buyer không vượt limit_per_user');

        $db->beginTransaction();
        $tooLargeQuote = FlashSaleService::quoteForPurchase($db, 1, 2, FlashSaleService::buyerKey(12, '0900 555 666'));
        $db->rollBack();
        assertFlashReservation(($tooLargeQuote['status'] ?? '') === 'sold_out', 'Không quote vượt allocation còn lại');

        $buyer11 = FlashSaleService::buyerKey(11, '0900 333 444');
        $db->beginTransaction();
        $lastQuote = FlashSaleService::quoteForPurchase($db, 1, 1, $buyer11);
        $lastReserved = FlashSaleService::reserveOrderItem($db, 1, 101, 1001, 11, $buyer11, 1, 700000.0);
        $db->commit();
        assertFlashReservation(($lastQuote['status'] ?? '') === 'eligible' && $lastReserved, 'Buyer khác lấy được suất cuối');
        assertFlashReservation((int)$db->query('SELECT sold_quantity FROM flash_sale_items WHERE id = 1')->fetchColumn() === 3, 'Quota đạt đúng allocation, không vượt');

        $db->beginTransaction();
        $soldOutQuote = FlashSaleService::quoteForPurchase($db, 1, 1, FlashSaleService::buyerKey(12, '0900 555 666'));
        $db->rollBack();
        assertFlashReservation(($soldOutQuote['status'] ?? '') === 'sold_out', 'Hết quota thì từ chối quote mới');

        $db->beginTransaction();
        $released = FlashSaleService::releaseOrderReservations($db, 100, 'payment_failed');
        $db->commit();
        assertFlashReservation($released, 'Release reservation thành công');
        assertFlashReservation((int)$db->query('SELECT sold_quantity FROM flash_sale_items WHERE id = 1')->fetchColumn() === 1, 'Release hoàn quota 3 → 1');

        $db->beginTransaction();
        $releasedAgain = FlashSaleService::releaseOrderReservations($db, 100, 'payment_failed_repeat');
        $db->commit();
        assertFlashReservation($releasedAgain, 'Release lặp trả về idempotent success');
        assertFlashReservation((int)$db->query('SELECT sold_quantity FROM flash_sale_items WHERE id = 1')->fetchColumn() === 1, 'Release lặp không hoàn quota lần hai');

        $db->beginTransaction();
        $committed = FlashSaleService::commitOrderReservations($db, 101);
        $db->commit();
        assertFlashReservation($committed, 'Commit reservation thành công');

        $db->beginTransaction();
        $releaseCommitted = FlashSaleService::releaseOrderReservations($db, 101, 'cancel_after_commit');
        $db->commit();
        assertFlashReservation($releaseCommitted, 'Release reservation đã committed thành công');
        assertFlashReservation((int)$db->query('SELECT sold_quantity FROM flash_sale_items WHERE id = 1')->fetchColumn() === 0, 'Release sau commit hoàn lại quota đúng một lần');
        $checkCommittedReservation = $db->query('SELECT status, release_reason, committed_at, released_at FROM flash_sale_reservations WHERE order_id = 101')->fetch(PDO::FETCH_ASSOC);
        assertFlashReservation($checkCommittedReservation['status'] === 'released', 'Status cuối của committed là released');
        assertFlashReservation($checkCommittedReservation['release_reason'] === 'cancel_after_commit', 'Lưu release_reason đúng cho committed');
        assertFlashReservation($checkCommittedReservation['committed_at'] !== null, 'Vẫn giữ lại committed_at để audit');
        assertFlashReservation($checkCommittedReservation['released_at'] !== null, 'Cập nhật released_at');

        $db->exec("INSERT INTO products VALUES (3, 'Product 3', 900000, NULL, 'active')");
        $db->exec("INSERT INTO flash_sale_items VALUES (3, 1, 3, 650000, 5, 0, 2)");
        $db->exec("INSERT INTO orders (id, user_id, phone) VALUES (104, 14, '0900 888 777')");
        $db->exec("INSERT INTO order_items (id, order_id, product_id, quantity, price) VALUES (1004, 104, 1, 1, 700000), (1005, 104, 3, 1, 650000)");

        $buyer14 = FlashSaleService::buyerKey(14, '0900 888 777');
        $db->beginTransaction();
        FlashSaleService::reserveOrderItem($db, 1, 104, 1004, 14, $buyer14, 1, 700000.0);
        FlashSaleService::reserveOrderItem($db, 3, 104, 1005, 14, $buyer14, 1, 650000.0);
        $db->commit();

        $db->beginTransaction();
        FlashSaleService::releaseOrderReservations($db, 104, 'cancel_multiple');
        $db->commit();

        assertFlashReservation((int)$db->query('SELECT sold_quantity FROM flash_sale_items WHERE id = 1')->fetchColumn() === 0, 'Release hoàn quota item 1');
        assertFlashReservation((int)$db->query('SELECT sold_quantity FROM flash_sale_items WHERE id = 3')->fetchColumn() === 0, 'Release hoàn quota item 3');
        assertFlashReservation((int)$db->query("SELECT COUNT(*) FROM flash_sale_reservations WHERE order_id = 104 AND status = 'released'")->fetchColumn() === 2, 'Tất cả reservation trong đơn đều được release');

        $db->exec("INSERT INTO orders (id, user_id, phone) VALUES (105, 15, '0900 999 999')");
        $db->exec("INSERT INTO order_items (id, order_id, product_id, quantity, price) VALUES (1006, 105, 1, 1, 700000)");
        $buyer15 = FlashSaleService::buyerKey(15, '0900 999 999');
        $db->beginTransaction();
        FlashSaleService::reserveOrderItem($db, 1, 105, 1006, 15, $buyer15, 1, 700000.0);
        $db->commit();

        $db->exec("UPDATE flash_sale_items SET sold_quantity = 0 WHERE id = 1");

        $driftRollback = false;
        try {
            $db->beginTransaction();
            FlashSaleService::releaseOrderReservations($db, 105, 'cancel_drift');
            $db->commit();
        } catch (RuntimeException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $driftRollback = true;
        }
        assertFlashReservation($driftRollback, 'Release từ chối khi counter drift (sold_quantity < quantity)');
        assertFlashReservation((int)$db->query('SELECT sold_quantity FROM flash_sale_items WHERE id = 1')->fetchColumn() === 0, 'Counter không bị âm');
        $res105 = $db->query("SELECT status FROM flash_sale_reservations WHERE order_id = 105")->fetchColumn();
        assertFlashReservation($res105 === 'reserved', 'Reservation giữ nguyên trạng thái cũ khi rollback');

        $db->exec("UPDATE flash_sale_items SET sold_quantity = 1 WHERE id = 1");
        $db->beginTransaction();
        FlashSaleService::releaseOrderReservations($db, 105, 'cancel_fixed');
        $db->commit();

        // Restore state for subsequent tests (requires 1 active reservation)
        $db->exec("INSERT INTO orders (id, user_id, phone) VALUES (106, 16, '0900 123 123')");
        $db->exec("INSERT INTO order_items (id, order_id, product_id, quantity, price) VALUES (1007, 106, 1, 1, 700000)");
        $buyer16 = FlashSaleService::buyerKey(16, '0900 123 123');
        $db->beginTransaction();
        FlashSaleService::reserveOrderItem($db, 1, 106, 1007, 16, $buyer16, 1, 700000.0);
        $db->commit();

        $beforeRollbackCount = flashReservationCount($db, 103);
        $db->beginTransaction();
        $guestQuote = FlashSaleService::quoteForPurchase($db, 1, 1, $guestA);
        FlashSaleService::reserveOrderItem($db, 1, 103, 1003, null, $guestA, 1, 700000.0);
        $db->rollBack();
        assertFlashReservation(($guestQuote['status'] ?? '') === 'eligible', 'Guest quote được quota còn lại trong transaction');
        assertFlashReservation(flashReservationCount($db, 103) === $beforeRollbackCount, 'Rollback không để lại reservation');
        assertFlashReservation((int)$db->query('SELECT sold_quantity FROM flash_sale_items WHERE id = 1')->fetchColumn() === 1, 'Rollback không làm đổi sold_quantity');

        $audit = FlashSaleService::auditQuotaCounters($db, 1);
        $item1Audit = false;
        foreach ($audit as $a) {
            if ($a['flash_sale_item_id'] === 1) {
                $item1Audit = $a;
                break;
            }
        }
        assertFlashReservation(
            $item1Audit !== false
                && ($item1Audit['is_consistent'] ?? false) === true
                && (int)($item1Audit['ledger_quantity'] ?? -1) === 1,
            'Audit xác nhận bộ đếm khớp tổng reservation đang hiệu lực'
        );

        $db->exec('UPDATE flash_sale_items SET sold_quantity = 2 WHERE id = 1');
        $driftAudit = FlashSaleService::auditQuotaCounters($db, 1);
        $item1Drift = false;
        foreach ($driftAudit as $a) {
            if ($a['flash_sale_item_id'] === 1) {
                $item1Drift = $a;
                break;
            }
        }
        assertFlashReservation(
            $item1Drift !== false
                && ($item1Drift['is_consistent'] ?? true) === false
                && (int)($item1Drift['difference'] ?? 0) === 1,
            'Audit phát hiện drift nhưng không tự ý sửa dữ liệu'
        );
        $db->exec('UPDATE flash_sale_items SET sold_quantity = 1 WHERE id = 1');

        $soldItemRemovalRejected = false;
        try {
            $db->beginTransaction();
            FlashSaleService::assertItemRemovable($db, 1);
            $db->rollBack();
        } catch (RuntimeException) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $soldItemRemovalRejected = true;
        }
        assertFlashReservation($soldItemRemovalRejected, 'Không cho xóa item đang có quota đã giữ/bán');

        $db->exec('UPDATE flash_sale_items SET sold_quantity = 0 WHERE id = 1');
        $historyRemovalRejected = false;
        try {
            $db->beginTransaction();
            FlashSaleService::assertItemRemovable($db, 1);
            $db->rollBack();
        } catch (RuntimeException) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $historyRemovalRejected = true;
        }
        assertFlashReservation($historyRemovalRejected, 'Sold về 0 vẫn không xóa item đã có lịch sử reservation');
        $db->exec('UPDATE flash_sale_items SET sold_quantity = 1 WHERE id = 1');

        $db->exec('INSERT INTO flash_sale_items VALUES (2, 1, 2, 650000, 5, 0, 2)');
        $db->beginTransaction();
        FlashSaleService::assertItemRemovable($db, 2);
        $db->rollBack();
        assertFlashReservation(true, 'Cho phép bỏ item chưa từng giữ/bán quota');

        require_once $rootPath . '/app/controllers/AdminFlashSaleController.php';
        if (!class_exists('TestableAdminFlashSaleController', false)) {
            class TestableAdminFlashSaleController extends AdminFlashSaleController
            {
                public array $redirects = [];

                public function __construct()
                {
                }

                protected function redirect(string $path): void
                {
                    $this->redirects[] = $path;
                }
            }
        }

        $originalPost = $_POST;
        $originalRequestMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'title' => 'Campaign updated safely',
            'start_time' => '2026-08-01T10:00',
            'end_time' => '2026-08-01T12:00',
            'status' => 'active',
            'items' => [
                1 => [
                    'active' => '1',
                    'discount_price' => '680000',
                    'allocation_quantity' => '4',
                    'limit_per_user' => '2',
                ],
                3 => [
                    'active' => '1',
                    'discount_price' => '650000',
                    'allocation_quantity' => '5',
                    'limit_per_user' => '2',
                ],
            ],
        ];

        $adminController = new TestableAdminFlashSaleController();
        $adminController->update('1');

        $updatedItem = $db->query(
            'SELECT id, discount_price, allocation_quantity, sold_quantity, limit_per_user
             FROM flash_sale_items WHERE product_id = 1'
        )->fetch(PDO::FETCH_ASSOC);
        assertFlashReservation(
            (int)($updatedItem['id'] ?? 0) === 1
                && (float)($updatedItem['discount_price'] ?? 0) === 680000.0
                && (int)($updatedItem['allocation_quantity'] ?? 0) === 4
                && (int)($updatedItem['sold_quantity'] ?? 0) === 1,
            'Admin update giữ nguyên item ID/sold_quantity và chỉ đổi cấu hình cho phép'
        );
        assertFlashReservation(
            (int)$db->query('SELECT COUNT(*) FROM flash_sale_items WHERE id = 2')->fetchColumn() === 0,
            'Admin update xóa được item bỏ chọn khi chưa có lịch sử quota'
        );
        assertFlashReservation(
            (int)$db->query('SELECT COUNT(*) FROM flash_sale_reservations WHERE flash_sale_item_id = 1')->fetchColumn() === 5,
            'Admin update không làm mất lịch sử reservation'
        );
        assertFlashReservation(
            end($adminController->redirects) === 'admin/flash-sales',
            'Admin update hoàn tất theo success flow'
        );

        $_POST = $originalPost;
        if ($originalRequestMethod === null) {
            unset($_SERVER['REQUEST_METHOD']);
        } else {
            $_SERVER['REQUEST_METHOD'] = $originalRequestMethod;
        }
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        assertFlashReservation(false, 'Reservation lifecycle không phát sinh exception: ' . $e->getMessage());
    } finally {
        foreach (['flash_sale_reservations', 'order_items', 'orders', 'flash_sale_items', 'flash_sales', 'products'] as $table) {
            try {
                $db->exec("DROP TEMPORARY TABLE IF EXISTS `{$table}`");
            } catch (Throwable) {
            }
        }
    }
}

echo "\n========================================================\n";
echo "Flash Sale Reservation Results: {$passed} passed, {$failed} failed\n";
echo "========================================================\n";

exit($failed > 0 ? 1 : 0);
