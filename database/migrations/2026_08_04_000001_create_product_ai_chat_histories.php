<?php

/**
 * Migration: Create product_ai_chat_histories table for ChatPersist feature
 * Converted from raw SQL to PHP migration format for MigrationRunner consistency.
 * Idempotent: checks IF NOT EXISTS before creating.
 */
class Migration_2026_08_04_000001_create_product_ai_chat_histories
{
    public static function up(PDO $db): bool
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS `product_ai_chat_histories` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NOT NULL,
            `product_id` INT UNSIGNED NOT NULL,
            `role` ENUM('user', 'assistant') NOT NULL,
            `message` TEXT NOT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_user_product` (`user_id`, `product_id`),
            INDEX `idx_created_at` (`created_at`),
            CONSTRAINT `fk_pach_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_pach_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        $db->exec($sql);
        return true;
    }
}
