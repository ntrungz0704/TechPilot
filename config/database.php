<?php

/**
 * Cấu hình kết nối cơ sở dữ liệu (PDO - MySQL)
 * Hỗ trợ nạp file config/database.local.php nếu tồn tại.
 * Việc tạo schema/import dữ liệu phải chạy thủ công, không thực hiện trong request web.
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
            $currentAppEnv = strtolower((string)(getenv('APP_ENV') ?: ($_SERVER['APP_ENV'] ?? (defined('APP_ENV') ? APP_ENV : ''))));
            if ((getenv('FORCE_DB_FAILURE') === '1' || ($_SERVER['FORCE_DB_FAILURE'] ?? '') === '1') && in_array($currentAppEnv, ['test', 'testing'], true)) {
                return null;
            }

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

                $sslMode = strtolower((string)(getenv('DB_SSL_MODE') ?: ($localConfig['ssl_mode'] ?? '')));
                $sslCa   = getenv('DB_SSL_CA') ?: ($localConfig['ssl_ca'] ?? '');

                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    (defined('Pdo\Mysql::ATTR_INIT_COMMAND') ? \Pdo\Mysql::ATTR_INIT_COMMAND : @PDO::MYSQL_ATTR_INIT_COMMAND) => 'SET NAMES utf8mb4',
                ];

                if (!empty($sslMode) && $sslMode !== 'disabled') {
                    if (!empty($sslCa) && file_exists($sslCa)) {
                        $options[defined('Pdo\Mysql::ATTR_SSL_CA') ? \Pdo\Mysql::ATTR_SSL_CA : @PDO::MYSQL_ATTR_SSL_CA] = $sslCa;
                    } else {
                        $options[defined('Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT') ? \Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT : @PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
                    }
                }

                try {
                    self::$instance = new PDO($dsn, $user, $pass, $options);
                } catch (PDOException $e) {
                    $appEnv = defined('APP_ENV') ? APP_ENV : 'production';
                    if ($appEnv === 'development') {
                        error_log('Database connection error: ' . $e->getMessage());
                    } else {
                        error_log('Database connection failed (Code: ' . $e->getCode() . ')');
                    }
                    self::$instance = null;
                }
            }

            return self::$instance;
        }
    }
}
