<?php

/**
 * Cấu hình kết nối cơ sở dữ liệu (PDO - MySQL)
 * Hỗ trợ nạp file config/database.local.php nếu tồn tại.
 * Tự động khởi tạo CSDL và đồng bộ dữ liệu (Auto DB Import & Sync) khi có thay đổi tệp techpilot.sql.
 */

if (!class_exists('Database')) {
    class Database
    {
        private static ?PDO $instance = null;

        // ==== THÔNG SỐ KẾT NỐI MẶC ĐỊNH ====
        private const HOST    = '127.0.0.1';
        private const DBNAME  = 'techpilot';
        private const USER    = 'root';
        private const PASS    = '123456';
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
                    self::ensureAutoSync(self::$instance);
                } catch (PDOException $e) {
                    // Nếu kết nối trực tiếp vào database 'techpilot' thất bại (ví dụ CSDL chưa được tạo)
                    // Tự động kết nối tới MySQL Server để tạo CSDL và nạp dữ liệu tự động
                    try {
                        $serverDsn = 'mysql:host=' . $host . ';charset=' . $charset;
                        if (!empty($port)) {
                            $serverDsn .= ';port=' . $port;
                        }
                        $serverPdo = new PDO($serverDsn, $user, $pass, [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
                        ]);

                        $sqlFile = dirname(__DIR__) . '/database/techpilot.sql';
                        if (file_exists($sqlFile)) {
                            $sql = file_get_contents($sqlFile);
                            $serverPdo->exec($sql);
                        }

                        // Kết nối lại vào database techpilot sau khi tự động nạp
                        self::$instance = new PDO($dsn, $user, $pass, $options);
                        self::saveSyncTimestamp($sqlFile);
                    } catch (PDOException $ex) {
                        self::$instance = null;
                    }
                }
            }

            return self::$instance;
        }

        /** Kiểm tra và tự động đồng bộ khi file techpilot.sql có sự thay đổi mới */
        private static function ensureAutoSync(PDO $pdo): void
        {
            $sqlFile = dirname(__DIR__) . '/database/techpilot.sql';
            if (!file_exists($sqlFile)) return;

            $mtime = filemtime($sqlFile);
            $hash = md5_file($sqlFile);
            $syncFile = __DIR__ . '/.db_sync_state.json';

            $shouldSync = false;
            if (!file_exists($syncFile)) {
                $shouldSync = true;
            } else {
                $state = json_decode(file_get_contents($syncFile), true);
                if (($state['hash'] ?? '') !== $hash || ($state['mtime'] ?? 0) !== $mtime) {
                    $shouldSync = true;
                }
            }

            // Kiểm tra thêm xem bảng products có dữ liệu chưa
            if (!$shouldSync) {
                try {
                    $cnt = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
                    if ($cnt == 0) {
                        $shouldSync = true;
                    }
                } catch (Exception $e) {
                    $shouldSync = true;
                }
            }

            if ($shouldSync) {
                try {
                    $sql = file_get_contents($sqlFile);
                    $pdo->exec($sql);
                    self::saveSyncTimestamp($sqlFile);
                } catch (Exception $e) {
                    // Bỏ qua cảnh báo nếu đã có bảng
                }
            }
        }

        private static function saveSyncTimestamp(string $sqlFile): void
        {
            $syncFile = __DIR__ . '/.db_sync_state.json';
            $state = [
                'mtime' => filemtime($sqlFile),
                'hash' => md5_file($sqlFile),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            file_put_contents($syncFile, json_encode($state));
        }
    }
}
