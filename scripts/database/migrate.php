<?php
/**
 * scripts/database/migrate.php
 * Non-destructive CLI migration runner for TechPilot.
 */

if (PHP_SAPI !== 'cli' && empty($_SERVER['ARTISAN_CLI'])) {
    fwrite(STDERR, "Error: Migration runner can only be executed via CLI.\n");
    exit(1);
}

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/database.php';

$db = Database::getConnection();
if (!$db) {
    fwrite(STDERR, "Error: Database connection failed.\n");
    exit(1);
}

// 1. Ensure migrations tracking table exists
$db->exec("CREATE TABLE IF NOT EXISTS migrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL UNIQUE,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 2. Scan database/migrations/*.sql
$migrationsDir = ROOT_PATH . '/database/migrations';
if (!is_dir($migrationsDir)) {
    mkdir($migrationsDir, 0755, true);
}

$files = glob($migrationsDir . '/*.sql');
sort($files);

$stmt = $db->query("SELECT migration FROM migrations");
$executed = $stmt->fetchAll(PDO::FETCH_COLUMN);

$ran = 0;
foreach ($files as $file) {
    $filename = basename($file);
    if (in_array($filename, $executed, true)) {
        continue;
    }

    echo "Running migration: {$filename}... ";
    $sql = file_get_contents($file);
    if (empty(trim($sql))) {
        echo "SKIPPED (empty)\n";
        continue;
    }

    try {
        $db->exec($sql);
        $ins = $db->prepare("INSERT INTO migrations (migration) VALUES (:migration)");
        $ins->execute([':migration' => $filename]);
        echo "DONE\n";
        $ran++;
    } catch (Throwable $e) {
        // If column or index already exists, log warning and record migration
        if (str_contains($e->getMessage(), 'Duplicate column name') || str_contains($e->getMessage(), 'Duplicate key name') || str_contains($e->getMessage(), '1060')) {
            $ins = $db->prepare("INSERT INTO migrations (migration) VALUES (:migration)");
            $ins->execute([':migration' => $filename]);
            echo "ALREADY APPLIED (recorded)\n";
            $ran++;
        } else {
            echo "FAILED\n";
            fwrite(STDERR, "Migration failed [{$filename}]: " . $e->getMessage() . "\n");
            exit(1);
        }
    }
}

echo "Migration finished cleanly. Executed {$ran} new migration(s).\n";
exit(0);
