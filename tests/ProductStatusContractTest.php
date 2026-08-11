<?php

/**
 * Product lifecycle status contract and migration regression tests.
 *
 * Migration integration uses a TEMPORARY table named `products`. MySQL keeps
 * it scoped to this connection, where it safely shadows the real table and is
 * dropped in finally/when the process exits.
 */

$rootPath = dirname(__DIR__);
$passed = 0;
$failed = 0;

$expectedSchemaStatuses = [
    'draft',
    'active',
    'inactive',
    'hidden',
    'out_of_stock',
    'discontinued',
    'archived',
];

function assertContract(bool $condition, string $message): void
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

function enumValues(string $columnType): array
{
    if (!preg_match('/^enum\((.*)\)$/i', trim($columnType), $match)) {
        return [];
    }

    preg_match_all("/'((?:''|[^'])*)'/", $match[1], $values);
    return array_map(
        static fn(string $value): string => str_replace("''", "'", $value),
        $values[1] ?? []
    );
}

function sameStatusSet(array $actual, array $expected): bool
{
    sort($actual, SORT_STRING);
    sort($expected, SORT_STRING);
    return $actual === $expected;
}

require_once $rootPath . '/config/app.php';
require_once $rootPath . '/app/core/Controller.php';
require_once $rootPath . '/app/controllers/AdminProductController.php';

echo "========================================================\n";
echo "=== TECHPILOT PRODUCT STATUS CONTRACT TEST SUITE     ===\n";
echo "========================================================\n\n";

echo "--- 1. Controller contract ---\n";
$controllerReflection = new ReflectionClass(AdminProductController::class);
$statusesConstant = $controllerReflection->getReflectionConstant('PRODUCT_STATUSES');
$defaultConstant = $controllerReflection->getReflectionConstant('DEFAULT_PRODUCT_STATUS');
$statusMetadata = $statusesConstant instanceof ReflectionClassConstant
    ? $statusesConstant->getValue()
    : null;
$defaultStatus = $defaultConstant instanceof ReflectionClassConstant
    ? $defaultConstant->getValue()
    : null;
$controllerStatuses = is_array($statusMetadata) ? array_keys($statusMetadata) : [];

assertContract($statusesConstant !== false, 'AdminProductController khai báo PRODUCT_STATUSES duy nhất');
assertContract($defaultConstant !== false, 'AdminProductController khai báo DEFAULT_PRODUCT_STATUS');
assertContract(sameStatusSet($controllerStatuses, $expectedSchemaStatuses), 'Controller hỗ trợ đúng 7 lifecycle status');
assertContract($defaultStatus === 'active', 'Status mặc định của controller là active');

$metadataValid = is_array($statusMetadata) && $statusMetadata !== [];
foreach ($statusMetadata ?: [] as $value => $metadata) {
    $metadataValid = $metadataValid
        && is_string($value)
        && is_array($metadata)
        && trim((string)($metadata['label'] ?? '')) !== ''
        && trim((string)($metadata['form_label'] ?? '')) !== '';
}
assertContract($metadataValid, 'Mỗi status có label cho filter và form');

$controllerSource = file_get_contents($rootPath . '/app/controllers/AdminProductController.php');
assertContract(!str_contains($controllerSource, '$validStatuses ='), 'Store/update không còn duplicate mảng validStatuses');
assertContract(substr_count($controllerSource, "'productStatuses'") >= 3, 'Controller truyền status contract cho index/create/edit');

echo "\n--- 2. Admin views ---\n";
$viewContracts = [
    'index' => $rootPath . '/app/views/admin/products/index.php',
    'create' => $rootPath . '/app/views/admin/products/create.php',
    'edit' => $rootPath . '/app/views/admin/products/edit.php',
];
foreach ($viewContracts as $name => $path) {
    $source = file_get_contents($path);
    assertContract(
        str_contains($source, 'foreach ($productStatuses as $value => $meta)'),
        "View {$name} render option từ productStatuses"
    );
}

echo "\n--- 3. Fresh-install seed schema ---\n";
$seedSource = file_get_contents($rootPath . '/database/seed_dev.sql');
$productsDefinition = '';
if (preg_match('/CREATE TABLE `products`\s*\((.*?)\) ENGINE=/si', $seedSource, $match)) {
    $productsDefinition = $match[1];
}
$seedStatuses = [];
if ($productsDefinition !== '' && preg_match('/`status`\s+enum\(([^)]*)\)/i', $productsDefinition, $match)) {
    $seedStatuses = enumValues('enum(' . $match[1] . ')');
}
assertContract($seedStatuses === $expectedSchemaStatuses, 'seed_dev.sql giữ nguyên 3 giá trị legacy và append 4 lifecycle status');

echo "\n--- 4. Migration contract ---\n";
$migrationPath = $rootPath . '/database/migrations/2026_08_01_000002_expand_product_status_enum.php';
$migrationClass = 'Migration_2026_08_01_000002_expand_product_status_enum';
$migrationExists = file_exists($migrationPath);
assertContract($migrationExists, 'Migration mở rộng product status tồn tại');

if ($migrationExists) {
    require_once $migrationPath;
}

$migrationReady = class_exists($migrationClass);
assertContract($migrationReady, 'Migration class đúng convention của runner');

if ($migrationReady) {
    $migrationReflection = new ReflectionClass($migrationClass);
    $targetStatuses = $migrationReflection->getConstant('TARGET_STATUSES');
    $legacyStatuses = $migrationReflection->getConstant('LEGACY_STATUSES');
    assertContract($targetStatuses === $expectedSchemaStatuses, 'Migration target khớp seed và giữ thứ tự ENUM legacy');
    assertContract($legacyStatuses === ['draft', 'active', 'inactive'], 'Migration khai báo đúng legacy ENUM');
    assertContract($migrationReflection->getMethod('up')->isStatic(), 'Migration up() là static');
    assertContract($migrationReflection->getMethod('down')->isStatic(), 'Migration down() là static');
}

echo "\n--- 5. Isolated MySQL migration integration ---\n";
if ($migrationReady) {
    require_once $rootPath . '/config/database.php';
    $db = Database::getConnection();
    assertContract($db instanceof PDO, 'Có PDO để chạy migration trên TEMPORARY table');

    if ($db instanceof PDO) {
        try {
            $db->exec('DROP TEMPORARY TABLE IF EXISTS `products`');
            $db->exec(
                "CREATE TEMPORARY TABLE `products` (
                    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `status` ENUM('draft','active','inactive') NOT NULL DEFAULT 'active'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
            $db->exec("INSERT INTO `products` (`status`) VALUES ('draft'), ('active'), ('inactive')");

            assertContract($migrationClass::up($db) === true, 'Migration up() chạy thành công trên legacy ENUM');
            $column = $db->query("SHOW COLUMNS FROM `products` LIKE 'status'")->fetch(PDO::FETCH_ASSOC);
            assertContract(
                enumValues((string)($column['Type'] ?? '')) === $expectedSchemaStatuses,
                'Migration up() tạo đúng target ENUM'
            );

            assertContract($migrationClass::up($db) === true, 'Migration up() chạy lần hai vẫn idempotent');

            foreach (array_slice($expectedSchemaStatuses, 3) as $status) {
                $stmt = $db->prepare('INSERT INTO `products` (`status`) VALUES (:status)');
                $stmt->execute([':status' => $status]);
            }
            $storedStatuses = $db->query('SELECT DISTINCT `status` FROM `products`')->fetchAll(PDO::FETCH_COLUMN);
            assertContract(
                sameStatusSet(array_map('strval', $storedStatuses), $expectedSchemaStatuses),
                'Database chấp nhận và lưu đủ 7 lifecycle status'
            );

            $rollbackBlocked = false;
            try {
                $migrationClass::down($db);
            } catch (RuntimeException) {
                $rollbackBlocked = true;
            }
            assertContract($rollbackBlocked, 'down() từ chối rollback khi status mới đang được sử dụng');

            $db->exec("DELETE FROM `products` WHERE `status` IN ('hidden','out_of_stock','discontinued','archived')");
            assertContract($migrationClass::down($db) === true, 'down() chỉ rollback khi không làm mất lifecycle status');
            $legacyColumn = $db->query("SHOW COLUMNS FROM `products` LIKE 'status'")->fetch(PDO::FETCH_ASSOC);
            assertContract(
                enumValues((string)($legacyColumn['Type'] ?? '')) === ['draft', 'active', 'inactive'],
                'Rollback an toàn khôi phục đúng legacy ENUM'
            );
        } catch (Throwable $e) {
            assertContract(false, 'Migration integration không phát sinh exception: ' . $e->getMessage());
        } finally {
            $db->exec('DROP TEMPORARY TABLE IF EXISTS `products`');
        }
    }
} else {
    assertContract(false, 'Không thể chạy integration vì migration chưa sẵn sàng');
}

echo "\n========================================================\n";
echo "Product Status Results: {$passed} passed, {$failed} failed\n";
echo "========================================================\n";

exit($failed === 0 ? 0 : 1);
