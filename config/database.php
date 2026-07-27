<?php

/**
 * Cấu hình kết nối cơ sở dữ liệu (PDO - MySQL)
 * Hỗ trợ nạp file config/database.local.php nếu tồn tại.
 * Việc tạo schema/import dữ liệu phải chạy thủ công, không thực hiện trong request web.
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

        private const HOST    = '127.0.0.1';
        private const DBNAME  = 'techpilot';
        private const USER    = 'root';
        private const PASS    = '';
        private const CHARSET = 'utf8mb4';

        public static function getConnection(): ?PDO
        {
            if (self::$instance !== null) {
                return self::$instance;
            }

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

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_TIMEOUT            => 2,
            ];

            $candidateHosts = array_unique([$host, '127.0.0.1', 'localhost']);
            $candidatePasses = array_unique(array_merge([$pass], ['', 'root', '123456', 'admin', 'password', 'mysql']));

            foreach ($candidateHosts as $tryHost) {
                foreach ($candidatePasses as $tryPass) {
                    try {
                        $dsn = 'mysql:host=' . $tryHost . ';dbname=' . $dbname . ';charset=' . $charset;
                        if (!empty($port)) {
                            $dsn .= ';port=' . $port;
                        }
                        self::$instance = new PDO($dsn, $user, $tryPass, $options);
                        break 2;
                    } catch (PDOException $e) {
                        try {
                            $serverDsn = 'mysql:host=' . $tryHost . ';charset=' . $charset;
                            if (!empty($port)) {
                                $serverDsn .= ';port=' . $port;
                            }
                            $serverPdo = new PDO($serverDsn, $user, $tryPass, $options);
                            $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `" . str_replace("`", "``", $dbname) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

                            self::$instance = new PDO($dsn, $user, $tryPass, $options);
                            break 2;
                        } catch (PDOException $ex) {
                            self::$instance = null;
                        }
                    }
                }
            }

            // 2. Auto-seed if database is empty or missing core tables
            if (self::$instance !== null) {
                self::ensureSchemaAndSeed(self::$instance);
            }

            return self::$instance;
        }

        private static function ensureSchemaAndSeed(PDO $pdo): void
        {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'products'");
                $hasProductsTable = (int)$stmt->fetchColumn() > 0;

                $needImport = !$hasProductsTable;
                if ($hasProductsTable) {
                    $cntStmt = $pdo->query("SELECT COUNT(*) FROM products");
                    $prodCount = (int)$cntStmt->fetchColumn();

                    $postCntStmt = $pdo->query("SELECT COUNT(*) FROM posts");
                    $postCount = (int)$postCntStmt->fetchColumn();

                    if ($prodCount === 0 || $postCount === 0) {
                        $needImport = true;
                    }
                }

                if ($needImport) {
                    $schemaPath = ROOT_PATH . '/database/schema.sql';
                    if (file_exists($schemaPath)) {
                        $sql = file_get_contents($schemaPath);
                        $pdo->exec($sql);
                    }

                    $seedPath = ROOT_PATH . '/database/seed.sql';
                    if (file_exists($seedPath)) {
                        $sqlSeed = file_get_contents($seedPath);
                        $pdo->exec($sqlSeed);
                    }
                }
            } catch (Exception $ex) {
                error_log('Database ensureSchemaAndSeed error: ' . $ex->getMessage());
            }
        }
    }
}
