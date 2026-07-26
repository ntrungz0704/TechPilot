<?php

/**
 * Cấu hình kết nối cơ sở dữ liệu (PDO - MySQL)
 * Hỗ trợ nạp file config/database.local.php nếu tồn tại.
 * Tự động khởi tạo CSDL và đồng bộ dữ liệu (Auto DB Import & Sync) khi có thay đổi tệp techpilot.sql.
 */

// Polyfills for missing mbstring extension
if (!function_exists('mb_strtolower')) {
    function mb_strtolower(string $string, ?string $encoding = null): string {
        return strtolower($string);
    }
}
if (!function_exists('mb_strlen')) {
    function mb_strlen(string $string, ?string $encoding = null): int {
        return strlen($string);
    }
}
if (!function_exists('mb_substr')) {
    function mb_substr(string $string, int $start, ?int $length = null, ?string $encoding = null): string {
        return $length !== null ? substr($string, $start, $length) : substr($string, $start);
    }
}

if (!class_exists('Database')) {
    class Database
    {
        private static ?PDO $instance = null;

        // ==== THÔNG SỐ KẾT NỐI MẶC ĐỊNH ====
        private const HOST    = '127.0.0.1';
        private const DBNAME  = 'techpilot';
        private const USER    = 'root';
        private const PASS    = 'a1032004km';
        private const CHARSET = 'utf8mb4';

        public static function getConnection(): ?PDO
        {
            if (self::$instance === null) {
                $host = getenv('DB_HOST') ?: self::HOST;
                $dbname = getenv('DB_NAME') ?: self::DBNAME;
                $user = getenv('DB_USER') !== false ? getenv('DB_USER') : self::USER;
                $pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : self::PASS;
                $charset = self::CHARSET;
                $port = getenv('DB_PORT') ?: null;

                $localConfigFile = __DIR__ . '/database.local.php';
                if (file_exists($localConfigFile)) {
                    $localConfig = require $localConfigFile;
                    if (is_array($localConfig)) {
                        $host = $localConfig['host'] ?? $host;
                        $dbname = $localConfig['database'] ?? $localConfig['dbname'] ?? $dbname;
                        $user = $localConfig['username'] ?? $localConfig['user'] ?? $user;
                        $pass = $localConfig['password'] ?? $localConfig['pass'] ?? $pass;
                        $charset = $localConfig['charset'] ?? $charset;
                        $port = $localConfig['port'] ?? $port;
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
                    // Nếu kết nối tới database 'techpilot' thất bại (chưa tạo CSDL)
                    try {
                        $serverDsn = 'mysql:host=' . $host . ';charset=' . $charset;
                        if (!empty($port)) {
                            $serverDsn .= ';port=' . $port;
                        }
                        $serverPdo = new PDO($serverDsn, $user, $pass, [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        ]);

                        $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `" . str_replace("`", "``", $dbname) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

                        // Kết nối lại vào database techpilot sau khi tạo CSDL
                        self::$instance = new PDO($dsn, $user, $pass, $options);
                    } catch (PDOException $ex) {
                        self::$instance = null;
                    }
                }
            }

            return self::$instance;
        }
    }
}
