-- remember_token đã được tạo bởi migration 20260722_002_add_remember_token_to_users.sql.
-- Migration này chỉ bổ sung token đặt lại mật khẩu.

ALTER TABLE users
    ADD COLUMN reset_token VARCHAR(100) DEFAULT NULL AFTER remember_token,
    ADD COLUMN reset_token_expiry DATETIME DEFAULT NULL AFTER reset_token;
