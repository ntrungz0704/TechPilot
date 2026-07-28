<?php
/**
 * TechPilot — Migration Runner
 *
 * Quét database/migrations/ và chạy method up(PDO $db) của từng migration
 * theo thứ tự tên file tăng dần. Mỗi migration phải idempotent.
 *
 * Cách dùng:
 *   php scripts/database/migrate.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    die('Migration runner chỉ chạy từ CLI.');
}

// Nạp config
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';

$db = Database::getConnection();
if ($db === null) {
    fwrite(STDERR, "[FAIL] Không thể kết nối database. Kiểm tra config/database.local.php hoặc .env\n");
    exit(1);
}

// Thư mục migrations
$migrationsDir = __DIR__ . '/../../database/migrations';
if (!is_dir($migrationsDir)) {
    echo "[WARN] Thư mục database/migrations/ không tồn tại. Không có migration nào cần chạy.\n";
    echo "[PASS] Migration runner hoàn tất.\n";
    exit(0);
}

// Quét file PHP migration, sắp xếp tên tăng dần
$files = glob($migrationsDir . '/*.php');
if (empty($files)) {
    echo "[INFO] Không có file migration nào trong database/migrations/\n";
    echo "[PASS] Migration runner hoàn tất.\n";
    exit(0);
}

sort($files, SORT_STRING);

$totalFiles = count($files);
$passed = 0;
$failed = 0;

echo "=== TechPilot Migration Runner ===\n";
echo "Tìm thấy {$totalFiles} migration(s)\n\n";

foreach ($files as $file) {
    $basename = basename($file);
    echo "[RUN ] {$basename} ... ";

    // Nạp file
    require_once $file;

    // Tìm class migration trong file
    // Convention: Migration_<tên_file_không_có_.php>
    $nameWithoutExt = pathinfo($basename, PATHINFO_FILENAME);
    $className = 'Migration_' . $nameWithoutExt;

    if (!class_exists($className)) {
        echo "SKIP (class {$className} không tồn tại)\n";
        continue;
    }

    try {
        $result = $className::up($db);

        if ($result) {
            echo "PASS\n";
            $passed++;
        } else {
            echo "FAIL (up() trả về false)\n";
            $failed++;
        }
    } catch (\Throwable $e) {
        echo "FAIL\n";
        fwrite(STDERR, "  Lỗi: " . $e->getMessage() . "\n");
        $failed++;
    }
}

echo "\n=== Kết quả ===\n";
echo "Tổng: {$totalFiles} | PASS: {$passed} | FAIL: {$failed}\n";

if ($failed > 0) {
    echo "\n[FAIL] Có {$failed} migration bị lỗi.\n";
    exit(1);
}

echo "\n[PASS] Tất cả migration đã chạy thành công.\n";
exit(0);
