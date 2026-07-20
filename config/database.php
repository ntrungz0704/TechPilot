<?php

/**
 * Cấu hình kết nối cơ sở dữ liệu (PDO - MySQL)
 * Hỗ trợ nạp file config/database.local.php nếu tồn tại.
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

        public static function getConnection(): ?PDO
        {
            if (self::$instance === null) {
                $host = self::HOST;
                $dbname = self::DBNAME;
                $user = self::USER;
                $pass = self::PASS;
                $charset = self::CHARSET;
                $port = null;

                $localConfigFile = __DIR__ . '/database.local.php';
                if (file_exists($localConfigFile)) {
                    $localConfig = require $localConfigFile;
                    if (is_array($localConfig)) {
                        $host = $localConfig['host'] ?? $host;
                        $dbname = $localConfig['database'] ?? $localConfig['dbname'] ?? $dbname;
                        $user = $localConfig['username'] ?? $localConfig['user'] ?? $user;
                        $pass = $localConfig['password'] ?? $localConfig['pass'] ?? $pass;
                        $charset = $localConfig['charset'] ?? $charset;
                        $port = $localConfig['port'] ?? null;
                    }
                }

                $dsn = 'mysql:host=' . $host . ';dbname=' . $dbname . ';charset=' . $charset;
                if (!empty($port)) {
                    $dsn .= ';port=' . $port;
                }

                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];

                try {
                    self::$instance = new PDO($dsn, $user, $pass, $options);
                } catch (PDOException $e) {
                    self::$instance = null;
                }
            }

            return self::$instance;
        }
    }
}
