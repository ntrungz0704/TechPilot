-- Migration: 20260722_001_harden_posts_content.sql
-- Add reading_minutes column to posts table if missing

SET @dbname = DATABASE();
SET @tablename = "posts";
SET @columnname = "reading_minutes";
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @dbname
        AND TABLE_NAME = @tablename
        AND COLUMN_NAME = @columnname
    ) > 0,
    "SELECT 1",
    "ALTER TABLE posts ADD COLUMN reading_minutes SMALLINT UNSIGNED DEFAULT NULL AFTER is_featured"
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
