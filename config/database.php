<?php

if (file_exists(__DIR__ . '/database.local.php')) {
    require_once __DIR__ . '/database.local.php';
    return;
}

/**
 * Cấu hình kết nối cơ sở dữ liệu (PDO - MySQL)
 * Chỉnh sửa 4 thông số bên dưới cho phù hợp với môi trường của bạn.
 * HOẶC tạo file database.local.php (copy từ file này) để không bị commit ghi đè.
 */

if (!class_exists('Database')) {
    class Database
    {
        private static ?PDO $instance = null;

        // ==== THÔNG SỐ KẾT NỐI MẶC ĐỊNH ====
        private const HOST    = '127.0.0.1';
        private const DBNAME  = 'techpilot';
        private const USER    = 'root';
        private const PASS    = '';
        private const CHARSET = 'utf8mb4';
    // ==== THÔNG SỐ KẾT NỐI - chỉnh theo máy của bạn ====
    private const HOST    = '127.0.0.1';
    private const DBNAME  = 'techpilot';
    private const USER    = 'root';
    private const PASS    = '';
    private const CHARSET = 'utf8mb4';

        public static function getConnection(): ?PDO
        {
            if (self::$instance === null) {
                $dsn = 'mysql:host=' . self::HOST . ';dbname=' . self::DBNAME . ';charset=' . self::CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];

                try {
                    self::$instance = new PDO($dsn, self::USER, self::PASS, $options);
                } catch (PDOException $e) {
                    self::$instance = null;
                }
            }

            return self::$instance;
        }
    }
}
