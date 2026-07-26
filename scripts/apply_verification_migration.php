<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getConnection();
if (!$db) {
    echo "ERROR: Cannot connect to database\n";
    exit(1);
}

echo "=== Applying Catalog Verification Migration ===\n";

$sqlFile = __DIR__ . '/../database/migrations/20260727_add_catalog_verification_fields.sql';
$sql = file_get_contents($sqlFile);

try {
    $db->exec($sql);
    echo "Migration applied successfully!\n";
} catch (Exception $e) {
    // MySQL 5.7+ may not support IF NOT EXISTS in ADD COLUMN, run individually
    $queries = [
        "ALTER TABLE products ADD COLUMN model_number VARCHAR(100) NULL AFTER sku",
        "ALTER TABLE products ADD COLUMN canonical_model_key VARCHAR(100) NULL AFTER model_number",
        "ALTER TABLE products ADD COLUMN verification_status ENUM('unverified','needs_review','verified','rejected') NOT NULL DEFAULT 'needs_review'",
        "ALTER TABLE products ADD COLUMN verification_score TINYINT UNSIGNED NOT NULL DEFAULT 0",
        "ALTER TABLE products ADD COLUMN verified_at DATETIME NULL",
        "ALTER TABLE products ADD COLUMN source_checked_at DATETIME NULL"
    ];

    foreach ($queries as $q) {
        try {
            $db->exec($q);
            echo "Executed: $q\n";
        } catch (Exception $ex) {
            echo "Notice: " . $ex->getMessage() . "\n";
        }
    }
}
