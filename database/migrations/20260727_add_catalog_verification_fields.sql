-- Migration:
-- database/migrations/20260727_add_catalog_verification_fields.sql

SET @database_name = DATABASE();

-- =========================================================
-- model_number
-- =========================================================

SET @column_exists = (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = @database_name
      AND table_name = 'products'
      AND column_name = 'model_number'
);

SET @sql = IF(
    @column_exists = 0,
    'ALTER TABLE products ADD COLUMN model_number VARCHAR(100) NULL',
    'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =========================================================
-- canonical_model_key
-- =========================================================

SET @column_exists = (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = @database_name
      AND table_name = 'products'
      AND column_name = 'canonical_model_key'
);

SET @sql = IF(
    @column_exists = 0,
    'ALTER TABLE products ADD COLUMN canonical_model_key VARCHAR(100) NULL',
    'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =========================================================
-- verification_status
-- =========================================================

SET @column_exists = (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = @database_name
      AND table_name = 'products'
      AND column_name = 'verification_status'
);

SET @sql = IF(
    @column_exists = 0,
    'ALTER TABLE products
     ADD COLUMN verification_status
     ENUM(''unverified'', ''needs_review'', ''verified'', ''rejected'')
     NOT NULL DEFAULT ''unverified''',
    'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =========================================================
-- verification_score
-- =========================================================

SET @column_exists = (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = @database_name
      AND table_name = 'products'
      AND column_name = 'verification_score'
);

SET @sql = IF(
    @column_exists = 0,
    'ALTER TABLE products
     ADD COLUMN verification_score TINYINT UNSIGNED NOT NULL DEFAULT 0',
    'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =========================================================
-- verified_at
-- =========================================================

SET @column_exists = (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = @database_name
      AND table_name = 'products'
      AND column_name = 'verified_at'
);

SET @sql = IF(
    @column_exists = 0,
    'ALTER TABLE products ADD COLUMN verified_at DATETIME NULL',
    'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =========================================================
-- source_checked_at
-- =========================================================

SET @column_exists = (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = @database_name
      AND table_name = 'products'
      AND column_name = 'source_checked_at'
);

SET @sql = IF(
    @column_exists = 0,
    'ALTER TABLE products ADD COLUMN source_checked_at DATETIME NULL',
    'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =========================================================
-- unique index
-- =========================================================

SET @index_exists = (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = @database_name
      AND table_name = 'products'
      AND index_name = 'idx_products_canonical_model_key'
);

SET @sql = IF(
    @index_exists = 0,
    'CREATE UNIQUE INDEX idx_products_canonical_model_key
     ON products (canonical_model_key)',
    'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
