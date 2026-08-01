<?php

/**
 * TechPilot — Ledger-aware Migration Runner
 *
 * Cách dùng:
 *   php scripts/database/migrate.php
 *   php scripts/database/migrate.php --status
 *   php scripts/database/migrate.php --baseline-existing
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    die('Migration runner chỉ chạy từ CLI.');
}

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/MigrationRunner.php';

$printUsage = static function (): void {
    echo "Cách dùng:\n";
    echo "  php scripts/database/migrate.php                     Chạy migration pending\n";
    echo "  php scripts/database/migrate.php --status            Chỉ xem trạng thái\n";
    echo "  php scripts/database/migrate.php --baseline-existing Chỉ ghi ledger cho DB legacy đã đồng bộ\n";
};

$arguments = array_slice($argv, 1);
if (count($arguments) > 1) {
    fwrite(STDERR, "[FAIL] Chỉ chấp nhận một tùy chọn.\n");
    $printUsage();
    exit(2);
}

$command = $arguments[0] ?? '--run';
$allowedCommands = ['--run', '--status', '--baseline-existing', '--help', '-h'];
if (!in_array($command, $allowedCommands, true)) {
    fwrite(STDERR, "[FAIL] Tùy chọn không hợp lệ: {$command}\n");
    $printUsage();
    exit(2);
}

if ($command === '--help' || $command === '-h') {
    $printUsage();
    exit(0);
}

$db = Database::getConnection();
if (!$db instanceof PDO) {
    fwrite(STDERR, "[FAIL] Không thể kết nối database. Kiểm tra config/database.local.php hoặc .env\n");
    exit(1);
}

$runner = new MigrationRunner($db, __DIR__ . '/../../database/migrations');

try {
    if ($command === '--status') {
        $status = $runner->status();
        echo "=== TechPilot Migration Status ===\n";

        foreach ($status['entries'] as $entry) {
            $label = $entry['state'] === 'applied' ? 'APPLIED' : 'PENDING';
            echo "[{$label}] {$entry['migration']}\n";
        }

        echo "\nTổng: {$status['total']} | Applied: {$status['applied']} | Pending: {$status['pending']}\n";
        exit(0);
    }

    if ($command === '--baseline-existing') {
        echo "=== TechPilot Existing Database Baseline ===\n";
        echo "[WARN] Chế độ này chỉ ghi ledger, không gọi bất kỳ up() nào.\n";
        $result = $runner->baseline();

        foreach ($result['entries'] as $entry) {
            $labels = [
                'baselined' => 'BASELINE',
                'skipped' => 'SKIP',
                'failed' => 'FAIL',
            ];
            $label = $labels[$entry['state']] ?? strtoupper($entry['state']);
            echo "[{$label}] {$entry['migration']} — {$entry['message']}\n";
        }

        echo "\nTổng: {$result['total']} | Baselined: {$result['baselined']} | Skip: {$result['skipped']} | Fail: {$result['failed']}\n";
        exit($result['failed'] > 0 ? 1 : 0);
    }

    echo "=== TechPilot Migration Runner ===\n";
    $result = $runner->run();

    foreach ($result['entries'] as $entry) {
        $labels = [
            'applied' => 'PASS',
            'skipped' => 'SKIP',
            'failed' => 'FAIL',
        ];
        $label = $labels[$entry['state']] ?? strtoupper($entry['state']);
        echo "[{$label}] {$entry['migration']} — {$entry['message']}\n";
    }

    echo "\nTổng: {$result['total']} | Applied: {$result['applied']} | Skip: {$result['skipped']} | Fail: {$result['failed']}\n";

    if ($result['failed'] > 0) {
        echo "[FAIL] Runner đã dừng; các migration phía sau chưa được chạy.\n";
        exit(1);
    }

    echo "[PASS] Không còn migration pending.\n";
    exit(0);
} catch (Throwable $error) {
    fwrite(STDERR, "[FAIL] " . $error->getMessage() . "\n");
    exit(1);
}
