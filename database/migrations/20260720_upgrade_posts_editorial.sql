-- ============================================================================
-- Migration: Upgrade Posts Editorial (2026-07-20)
--
-- Chú ý: Script này được thiết kế để chạy một lần duy nhất.
-- KHÔNG DROP BẢNG, KHÔNG XÓA DỮ LIỆU CŨ, BẢO TOÀN ID.
-- ============================================================================

-- Thêm cột mới
-- MySQL/MariaDB cũ không hỗ trợ "IF NOT EXISTS" trên ADD COLUMN,
-- vì vậy chỉ chạy script này một lần duy nhất. Nếu chạy lại sẽ báo lỗi "Duplicate column name".
ALTER TABLE posts
ADD COLUMN category_slug VARCHAR(60) NOT NULL DEFAULT 'cong-nghe' AFTER image,
ADD COLUMN post_type ENUM('news', 'review', 'guide', 'comparison') NOT NULL DEFAULT 'news' AFTER category_slug,
ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0 AFTER post_type,
ADD COLUMN reading_minutes SMALLINT UNSIGNED DEFAULT NULL AFTER is_featured;

-- Thêm index tổng hợp hỗ trợ cho các truy vấn của Editorial-Commerce
-- Tránh tạo index trùng. Index hiện có là idx_posts_status_time (status, published_at, created_at)
-- Index mới này hỗ trợ lọc category, type, check featured và sắp xếp theo published_at.
ALTER TABLE posts
ADD INDEX idx_posts_editorial (status, category_slug, post_type, is_featured, published_at);

-- ============================================================================
-- End of Migration
-- ============================================================================
