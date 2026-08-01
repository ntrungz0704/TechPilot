<?php

/**
 * Flash Sale legacy-data cleanup tests.
 *
 * Integration chỉ dùng TEMPORARY tables và không sửa dữ liệu runtime.
 */

$rootPath = dirname(__DIR__);
$passed = 0;
$failed = 0;

function assertFlashCleanup(bool $condition, string $message): void
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

echo "========================================================\n";
echo "=== TECHPILOT FLASH SALE DATA CLEANUP TEST SUITE     ===\n";
echo "========================================================\n\n";

$migrationName = '2026_08_01_000005_cleanup_invalid_flash_sale_items.php';
$migrationPath = $rootPath . '/database/migrations/' . $migrationName;
$migrationClass = 'Migration_2026_08_01_000005_cleanup_invalid_flash_sale_items';
$targetIds = [50, 52, 53, 55, 57, 58];

echo "--- 1. Source contracts ---\n";
assertFlashCleanup(file_exists($migrationPath), 'Migration cleanup Flash Sale tồn tại');

$seedSource = file_get_contents($rootPath . '/database/seed.sql');
assertFlashCleanup(
    str_contains($seedSource, $migrationName),
    'Fresh-install seed baseline migration cleanup'
);
foreach ($targetIds as $targetId) {
    assertFlashCleanup(
        !preg_match('/\(' . $targetId . ',33,/', $seedSource),
        "Fresh-install seed không còn Flash Sale item #{$targetId}"
    );
}

echo "\n--- 2. Isolated cleanup integration ---\n";
require_once $rootPath . '/config/database.php';
$db = Database::getConnection();
assertFlashCleanup($db instanceof PDO, 'Có PDO để chạy migration trên TEMPORARY tables');

if ($db instanceof PDO && file_exists($migrationPath)) {
    try {
        foreach (['flash_sale_items', 'flash_sales', 'products'] as $table) {
            $db->exec("DROP TEMPORARY TABLE IF EXISTS `{$table}`");
        }

        $db->exec(
            "CREATE TEMPORARY TABLE `products` (
                `id` INT UNSIGNED NOT NULL PRIMARY KEY,
                `price` DECIMAL(15,2) NOT NULL,
                `sale_price` DECIMAL(15,2) NULL
            ) ENGINE=InnoDB"
        );
        $db->exec(
            "CREATE TEMPORARY TABLE `flash_sales` (
                `id` INT UNSIGNED NOT NULL PRIMARY KEY
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
                `limit_per_user` INT NOT NULL,
                UNIQUE KEY `uq_campaign_product` (`flash_sale_id`, `product_id`)
            ) ENGINE=InnoDB"
        );

        $db->exec(
            "INSERT INTO `products` (`id`, `price`, `sale_price`) VALUES
                (633, 59990000, 52191000),
                (635, 39990000, NULL),
                (637, 54990000, 48391000),
                (654, 25990000, 23651000),
                (655, 24990000, 21991000),
                (657, 23990000, NULL),
                (659, 36990000, NULL)"
        );
        $db->exec('INSERT INTO `flash_sales` (`id`) VALUES (33)');
        $db->exec(
            "INSERT INTO `flash_sale_items`
                (`id`, `flash_sale_id`, `product_id`, `discount_price`, `allocation_quantity`, `sold_quantity`, `limit_per_user`)
             VALUES
                (50, 33, 637, 49491000, 8, 0, 1),
                (51, 33, 635, 35991000, 12, 0, 1),
                (52, 33, 633, 53991000, 10, 0, 1),
                (53, 33, 657, 0, 10, 0, 2),
                (55, 33, 659, 0, 10, 0, 2),
                (57, 33, 654, 0, 10, 0, 2),
                (58, 33, 655, 0, 10, 0, 2)"
        );

        require_once $migrationPath;
        assertFlashCleanup(class_exists($migrationClass), 'Migration class đúng convention của runner');

        $migrationResult = $migrationClass::up($db);
        assertFlashCleanup($migrationResult === true, 'Migration up() chạy thành công');
        $remainingIds = $db->query(
            'SELECT id FROM flash_sale_items ORDER BY id'
        )->fetchAll(PDO::FETCH_COLUMN);
        assertFlashCleanup($remainingIds === [51], 'Migration chỉ giữ lại item hợp lệ trong fixture');
        assertFlashCleanup($migrationClass::up($db) === true, 'Migration up() chạy lần hai vẫn idempotent');

        assertFlashCleanup($migrationClass::down($db) === true, 'Migration down() khôi phục dữ liệu đã dọn');
        $restoredIds = array_map(
            'intval',
            $db->query('SELECT id FROM flash_sale_items ORDER BY id')->fetchAll(PDO::FETCH_COLUMN)
        );
        assertFlashCleanup($restoredIds === [50, 51, 52, 53, 55, 57, 58], 'Rollback khôi phục đúng 6 item legacy');

        // Nếu một target đã được sửa thành dữ liệu kinh doanh mới, migration phải dừng thay vì xóa nhầm.
        $db->exec('UPDATE flash_sale_items SET discount_price = 47000000 WHERE id = 50');
        $driftRejected = false;
        try {
            $migrationClass::up($db);
        } catch (RuntimeException) {
            $driftRejected = true;
        }
        assertFlashCleanup($driftRejected, 'Migration từ chối xóa khi target đã lệch baseline');
        assertFlashCleanup(
            (int)$db->query('SELECT COUNT(*) FROM flash_sale_items')->fetchColumn() === 7,
            'Drift guard không xóa dở dữ liệu'
        );

        $db->exec('UPDATE flash_sale_items SET discount_price = 49491000 WHERE id = 50');
        assertFlashCleanup($migrationClass::up($db) === true, 'Migration chạy lại sau khi dữ liệu khớp baseline');
    } catch (Throwable $e) {
        assertFlashCleanup(false, 'Cleanup integration không phát sinh exception: ' . $e->getMessage());
    } finally {
        foreach (['flash_sale_items', 'flash_sales', 'products'] as $table) {
            try {
                $db->exec("DROP TEMPORARY TABLE IF EXISTS `{$table}`");
            } catch (Throwable) {
            }
        }
    }
}

echo "\n========================================================\n";
echo "Flash Sale Cleanup Results: {$passed} passed, {$failed} failed\n";
echo "========================================================\n";

exit($failed > 0 ? 1 : 0);
