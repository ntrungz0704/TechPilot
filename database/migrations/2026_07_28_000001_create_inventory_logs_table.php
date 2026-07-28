<?php

/**
 * Migration: Create inventory_logs table for TechPilot Inventory Audit V2
 * Idempotent: checks IF NOT EXISTS before creating.
 */
class Migration_2026_07_28_000001_create_inventory_logs_table
{
    public static function up(PDO $db): bool
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS `inventory_logs` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `product_id` INT UNSIGNED NOT NULL,
            `order_id` INT UNSIGNED NULL,
            `type` VARCHAR(50) NOT NULL COMMENT 'manual_import, manual_export, stock_correction_increase, stock_correction_decrease, order_reserve, order_release, return_restock, supplier_return, initial_stock',
            `quantity_delta` INT NOT NULL,
            `old_stock` INT NOT NULL,
            `new_stock` INT NOT NULL,
            `reason_code` VARCHAR(50) NULL,
            `note` TEXT NULL,
            `reference_type` VARCHAR(50) NULL,
            `reference_id` VARCHAR(100) NULL,
            `created_by` INT UNSIGNED NULL,
            `idempotency_key` VARCHAR(191) NULL UNIQUE,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_inventory_logs_product` (`product_id`),
            INDEX `idx_inventory_logs_order` (`order_id`),
            INDEX `idx_inventory_logs_type` (`type`),
            INDEX `idx_inventory_logs_creator` (`created_by`),
            INDEX `idx_inventory_logs_created_at` (`created_at`),
            INDEX `idx_inventory_logs_ref` (`reference_type`, `reference_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        $db->exec($sql);
        return true;
    }

    public static function down(PDO $db): bool
    {
        $db->exec("DROP TABLE IF EXISTS `inventory_logs`;");
        return true;
    }
}
