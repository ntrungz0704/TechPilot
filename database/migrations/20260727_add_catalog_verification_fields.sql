-- Migration: Add Catalog Verification Fields to Products Table
-- File: database/migrations/20260727_add_catalog_verification_fields.sql

ALTER TABLE products
ADD COLUMN IF NOT EXISTS model_number VARCHAR(100) NULL AFTER sku,
ADD COLUMN IF NOT EXISTS canonical_model_key VARCHAR(100) NULL AFTER model_number,
ADD COLUMN IF NOT EXISTS verification_status ENUM('unverified','needs_review','verified','rejected') NOT NULL DEFAULT 'needs_review' AFTER status,
ADD COLUMN IF NOT EXISTS verification_score TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER verification_status,
ADD COLUMN IF NOT EXISTS verified_at DATETIME NULL AFTER verification_score,
ADD COLUMN IF NOT EXISTS source_checked_at DATETIME NULL AFTER source_updated_at;

-- Create unique index on canonical_model_key if not exists
SET @exist := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'products' AND index_name = 'idx_canonical_model_key');
SET @sqlstmt := IF(@exist = 0, 'CREATE UNIQUE INDEX idx_canonical_model_key ON products(canonical_model_key)', 'SELECT 1');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
