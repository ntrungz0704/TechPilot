<?php

/**
 * Migration: Create chatbot AI support tables
 * - user_behavior_logs: lưu lịch sử hành vi người dùng (xem SP, thêm giỏ, wishlist...)
 * - user_interest_profiles: lưu profile sở thích tổng hợp theo user
 * Idempotent: checks IF NOT EXISTS before creating.
 */
class Migration_2026_07_28_000002_create_chatbot_tables
{
    public static function up(PDO $db): bool
    {
        // Bảng 1: Lưu lịch sử hành vi người dùng
        $sql1 = "
        CREATE TABLE IF NOT EXISTS `user_behavior_logs` (
            `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id`     INT UNSIGNED NOT NULL,
            `action_type` VARCHAR(50)  NOT NULL COMMENT 'product_detail, add_cart, wishlist, search, chatbot_query...',
            `target_type` VARCHAR(50)  NULL,
            `target_id`   INT UNSIGNED NULL,
            `metadata`    JSON         NULL,
            `created_at`  TIMESTAMP    NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_ubl_user` (`user_id`),
            INDEX `idx_ubl_action` (`action_type`),
            INDEX `idx_ubl_target` (`target_type`, `target_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        // Bảng 2: Profile sở thích tổng hợp theo user (1 user = 1 dòng)
        $sql2 = "
        CREATE TABLE IF NOT EXISTS `user_interest_profiles` (
            `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id`         INT UNSIGNED NOT NULL UNIQUE,
            `brand_scores`    JSON         NULL COMMENT 'điểm yêu thích theo hãng',
            `category_scores` JSON         NULL COMMENT 'điểm yêu thích theo danh mục',
            `budget_min`      DECIMAL(12,0) NULL DEFAULT 0,
            `budget_max`      DECIMAL(12,0) NULL DEFAULT 0,
            `last_keywords`   JSON         NULL COMMENT 'từ khóa tìm kiếm gần đây',
            `updated_at`      TIMESTAMP    NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_uip_user` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        // Bảng 3: Giới hạn số lượt hỏi AI theo IP (guest: 5 lượt/ngày) và User (thành viên: 20 lượt/ngày)
        $sql3 = "
        CREATE TABLE IF NOT EXISTS `chatbot_rate_limits` (
            `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `identifier`  VARCHAR(100) NOT NULL COMMENT 'user:{id} hoặc ip:{address}',
            `rate_date`   DATE         NOT NULL,
            `query_count` INT UNSIGNED NOT NULL DEFAULT 0,
            `created_at`  TIMESTAMP    NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`  TIMESTAMP    NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uniq_identifier_date` (`identifier`, `rate_date`),
            INDEX `idx_crl_date` (`rate_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        $db->exec($sql1);
        $db->exec($sql2);
        $db->exec($sql3);
        return true;
    }

    public static function down(PDO $db): bool
    {
        $db->exec("DROP TABLE IF EXISTS `chatbot_rate_limits`;");
        $db->exec("DROP TABLE IF EXISTS `user_interest_profiles`;");
        $db->exec("DROP TABLE IF EXISTS `user_behavior_logs`;");
        return true;
    }
}
