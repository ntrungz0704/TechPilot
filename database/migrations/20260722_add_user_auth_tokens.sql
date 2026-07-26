-- Add authentication token columns safely on MySQL 8 and MariaDB.

SET @dbname = DATABASE();
SET @tablename = 'users';

-- remember_token
SET @columnname = 'remember_token';
SET @preparedStatement = (
    SELECT IF(
        (
            SELECT COUNT(*)
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = @dbname
              AND TABLE_NAME = @tablename
              AND COLUMN_NAME = @columnname
        ) > 0,
        'SELECT 1',
        'ALTER TABLE users ADD COLUMN remember_token VARCHAR(255) DEFAULT NULL AFTER status'
    )
);

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- reset_token
SET @columnname = 'reset_token';
SET @preparedStatement = (
    SELECT IF(
        (
            SELECT COUNT(*)
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = @dbname
              AND TABLE_NAME = @tablename
              AND COLUMN_NAME = @columnname
        ) > 0,
        'SELECT 1',
        'ALTER TABLE users ADD COLUMN reset_token VARCHAR(100) DEFAULT NULL AFTER remember_token'
    )
);

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- reset_token_expiry
SET @columnname = 'reset_token_expiry';
SET @preparedStatement = (
    SELECT IF(
        (
            SELECT COUNT(*)
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = @dbname
              AND TABLE_NAME = @tablename
              AND COLUMN_NAME = @columnname
        ) > 0,
        'SELECT 1',
        'ALTER TABLE users ADD COLUMN reset_token_expiry DATETIME DEFAULT NULL AFTER reset_token'
    )
);

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
