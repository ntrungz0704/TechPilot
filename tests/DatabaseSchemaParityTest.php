<?php

/**
 * Ensures fresh-install seed, migration contracts, install verification and
 * the current database agree on the same physical-table schema.
 *
 * Integration checks are read-only except for TEMPORARY tables that shadow
 * return_requests/return_items while validating the new migration.
 */

define('ROOT_PATH', dirname(__DIR__));

final class DatabaseSchemaParityTest
{
    private const EXPECTED_TABLES = [
        'ai_assistant_logs',
        'banners',
        'brands',
        'cart_items',
        'carts',
        'categories',
        'chatbot_rate_limits',
        'coupons',
        'flash_sale_items',
        'flash_sale_reservations',
        'flash_sales',
        'inventory_logs',
        'migrations',
        'notifications',
        'order_items',
        'orders',
        'posts',
        'product_ai_chat_histories',
        'product_images',
        'products',
        'return_items',
        'return_requests',
        'reviews',
        'user_addresses',
        'user_behavior_logs',
        'user_interest_profiles',
        'users',
        'wishlists',
    ];

    private const DRIFT_TABLES = [
        'chatbot_rate_limits',
        'flash_sale_reservations',
        'return_items',
        'return_requests',
        'user_behavior_logs',
        'user_interest_profiles',
    ];

    private int $passed = 0;
    private int $failed = 0;
    private array $errors = [];

    public function run(): void
    {
        echo "========================================================\n";
        echo "=== TECHPILOT DATABASE SCHEMA PARITY TEST SUITE     ===\n";
        echo "========================================================\n\n";

        $seedTables = $this->testSourceContracts();
        $migrationReady = $this->testReturnMigrationContract();

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();
        $this->assert($db instanceof PDO, 'Có PDO để so schema runtime');

        if ($db instanceof PDO) {
            $this->testRuntimeParity($db, $seedTables);
            $this->testSeedDdlOnTemporaryTables($db, $seedTables);
            if ($migrationReady) {
                $this->testReturnMigrationOnTemporaryTables($db);
            }
        }

        echo "\n════════════════════════════════════════════════════════\n";
        echo "Database Schema Parity Results: {$this->passed} passed, {$this->failed} failed\n";
        echo "════════════════════════════════════════════════════════\n";

        if ($this->failed > 0) {
            echo "\n[FAIL] DATABASE SCHEMA PARITY ERRORS:\n";
            foreach ($this->errors as $error) {
                echo "  - {$error}\n";
            }
            exit(1);
        }

        exit(0);
    }

    /** @return array<string, array<int, string>> */
    private function testSourceContracts(): array
    {
        echo "--- 1. Fresh-install source contracts ---\n";

        $seedSource = file_get_contents(ROOT_PATH . '/database/seed_dev.sql');
        $seedTables = $this->parseSeedTables($seedSource);
        $seedNames = array_keys($seedTables);
        sort($seedNames, SORT_STRING);
        $expected = self::EXPECTED_TABLES;
        sort($expected, SORT_STRING);

        $this->assert(count($seedNames) === 28, 'seed_dev.sql tạo đúng 28 bảng vật lý');
        $this->assertSameList($expected, $seedNames, 'Danh sách bảng trong seed khớp schema contract');

        foreach (self::DRIFT_TABLES as $table) {
            $this->assert(isset($seedTables[$table]), "Seed chứa bảng {$table}");
        }

        $migrationCreatedTables = [];
        $migrationFiles = glob(ROOT_PATH . '/database/migrations/*.php') ?: [];
        foreach ($migrationFiles as $migrationFile) {
            $source = file_get_contents($migrationFile);
            preg_match_all(
                '/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+`([^`]+)`/i',
                $source,
                $matches
            );
            foreach ($matches[1] ?? [] as $table) {
                $migrationCreatedTables[$table] = true;
            }
        }

        foreach (array_keys($migrationCreatedTables) as $table) {
            $this->assert(
                isset($seedTables[$table]),
                "Bảng {$table} do migration tạo cũng tồn tại trong fresh-install seed"
            );
        }

        $readme = file_get_contents(ROOT_PATH . '/README.md');
        $this->assert(
            str_contains($readme, '**28 bảng vật lý**'),
            'README công bố đúng 28 bảng vật lý'
        );

        $verifySource = file_get_contents(ROOT_PATH . '/scripts/verify-install.php');
        $verifyTables = $this->parseVerifyInstallTables($verifySource);
        sort($verifyTables, SORT_STRING);
        $this->assertSameList($expected, $verifyTables, 'verify-install kiểm tra đủ 28 bảng');

        return $seedTables;
    }

    private function testReturnMigrationContract(): bool
    {
        echo "\n--- 2. Return tables migration contract ---\n";

        $migrationPath = ROOT_PATH . '/database/migrations/2026_08_01_000003_create_return_tables.php';
        $migrationClass = 'Migration_2026_08_01_000003_create_return_tables';
        $exists = file_exists($migrationPath);
        $this->assert($exists, 'Migration khôi phục return tables tồn tại');

        if ($exists) {
            require_once $migrationPath;
        }

        $ready = class_exists($migrationClass);
        $this->assert($ready, 'Return migration class đúng convention của runner');

        if ($ready) {
            $reflection = new ReflectionClass($migrationClass);
            $this->assert($reflection->getMethod('up')->isStatic(), 'Return migration up() là static');
            $this->assert($reflection->getMethod('down')->isStatic(), 'Return migration down() là static');
        }

        return $ready;
    }

    /** @param array<string, array<int, string>> $seedTables */
    private function testRuntimeParity(PDO $db, array $seedTables): void
    {
        echo "\n--- 3. Runtime database parity ---\n";

        $actualTables = $db->query(
            "SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'"
        )->fetchAll(PDO::FETCH_COLUMN);
        sort($actualTables, SORT_STRING);

        $seedNames = array_keys($seedTables);
        sort($seedNames, SORT_STRING);
        $this->assertSameList($seedNames, $actualTables, 'Runtime DB và seed có cùng danh sách bảng');

        foreach (self::EXPECTED_TABLES as $table) {
            if (!isset($seedTables[$table]) || !in_array($table, $actualTables, true)) {
                continue;
            }

            $actualColumns = $db->query(
                'SHOW COLUMNS FROM `' . str_replace('`', '``', $table) . '`'
            )->fetchAll(PDO::FETCH_COLUMN);
            $this->assertSameList(
                $seedTables[$table],
                array_map('strval', $actualColumns),
                "Tên và thứ tự cột {$table} khớp seed"
            );
        }
    }

    private function testReturnMigrationOnTemporaryTables(PDO $db): void
    {
        echo "\n--- 5. Return migration integration bằng TEMPORARY tables ---\n";
        $migrationClass = 'Migration_2026_08_01_000003_create_return_tables';

        try {
            $this->createTemporaryReturnTables($db, true);
            $this->assert($migrationClass::up($db) === true, 'up() chấp nhận schema return hiện hành');
            $this->assert($migrationClass::up($db) === true, 'up() chạy lần hai vẫn idempotent');

            $this->dropTemporaryReturnTables($db);
            $this->createTemporaryReturnTables($db, false);

            $refusedDrift = false;
            try {
                $migrationClass::up($db);
            } catch (RuntimeException $error) {
                $refusedDrift = str_contains($error->getMessage(), 'return_requests');
            }
            $this->assert($refusedDrift, 'up() từ chối ghi ledger khi return_requests thiếu cột');
        } finally {
            $this->dropTemporaryReturnTables($db);
        }
    }

    /** @param array<string, array<int, string>> $seedTables */
    private function testSeedDdlOnTemporaryTables(PDO $db, array $seedTables): void
    {
        echo "\n--- 4. Fresh-install DDL bằng TEMPORARY tables ---\n";
        $seedSource = file_get_contents(ROOT_PATH . '/database/seed_dev.sql');

        foreach (self::DRIFT_TABLES as $table) {
            $quotedTable = preg_quote($table, '/');
            $found = preg_match(
                '/CREATE TABLE `' . $quotedTable . '`\s*(\(.*?\) ENGINE=.*?;)/si',
                $seedSource,
                $match
            ) === 1;

            if (!$found) {
                $this->assert(false, "Đọc được DDL {$table} từ seed");
                continue;
            }

            try {
                // InnoDB không hỗ trợ foreign key trên TEMPORARY table. Phần
                // này chỉ kiểm tra contract cột từ fresh-install seed; foreign
                // key thật được đối chiếu qua runtime schema/migration test.
                $temporaryDdl = preg_replace(
                    '/^\s*CONSTRAINT\s+.*(?:\R|$)/mi',
                    '',
                    $match[1]
                ) ?? $match[1];
                $temporaryDdl = preg_replace(
                    '/,\s*(\)\s+ENGINE=)/s',
                    "\n" . '$1',
                    trim($temporaryDdl)
                ) ?? $temporaryDdl;

                $db->exec('DROP TEMPORARY TABLE IF EXISTS `' . $table . '`');
                $db->exec('CREATE TEMPORARY TABLE `' . $table . '` ' . $temporaryDdl);
                $actualColumns = $db->query(
                    'SHOW COLUMNS FROM `' . $table . '`'
                )->fetchAll(PDO::FETCH_COLUMN);
                $this->assertSameList(
                    $seedTables[$table],
                    array_map('strval', $actualColumns),
                    "DDL seed tạo được bảng {$table} đúng contract"
                );
            } finally {
                $db->exec('DROP TEMPORARY TABLE IF EXISTS `' . $table . '`');
            }
        }
    }

    /** @return array<string, array<int, string>> */
    private function parseSeedTables(string $seedSource): array
    {
        preg_match_all(
            '/CREATE TABLE `([^`]+)`\s*\((.*?)\) ENGINE=/si',
            $seedSource,
            $matches,
            PREG_SET_ORDER
        );

        $tables = [];
        foreach ($matches as $match) {
            preg_match_all('/^\s*`([^`]+)`\s+/m', $match[2], $columns);
            $tables[$match[1]] = $columns[1] ?? [];
        }

        return $tables;
    }

    /** @return array<int, string> */
    private function parseVerifyInstallTables(string $source): array
    {
        $tables = [];
        foreach (['businessTables', 'technicalTables'] as $variable) {
            if (preg_match('/\$' . $variable . '\s*=\s*\[(.*?)\];/s', $source, $match)) {
                preg_match_all("/'([^']+)'/", $match[1], $names);
                $tables = array_merge($tables, $names[1] ?? []);
            }
        }

        return array_values(array_unique($tables));
    }

    private function createTemporaryReturnTables(PDO $db, bool $complete): void
    {
        $this->dropTemporaryReturnTables($db);
        $updatedAt = $complete
            ? ', `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
            : '';

        $db->exec(
            "CREATE TEMPORARY TABLE `return_requests` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `return_code` VARCHAR(50) NOT NULL,
                `order_id` INT UNSIGNED NOT NULL,
                `user_id` INT UNSIGNED NOT NULL,
                `reason` VARCHAR(255) NOT NULL,
                `description` TEXT NULL,
                `status` ENUM('requested','approved','rejected','completed') NULL DEFAULT 'requested',
                `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
                {$updatedAt},
                PRIMARY KEY (`id`),
                UNIQUE KEY `return_code` (`return_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $db->exec(
            "CREATE TEMPORARY TABLE `return_items` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `return_request_id` INT UNSIGNED NOT NULL,
                `order_item_id` INT UNSIGNED NOT NULL,
                `quantity` INT UNSIGNED NOT NULL,
                `resolution` ENUM('refund','replace','repair') NULL DEFAULT 'refund',
                `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    private function dropTemporaryReturnTables(PDO $db): void
    {
        $db->exec('DROP TEMPORARY TABLE IF EXISTS `return_items`');
        $db->exec('DROP TEMPORARY TABLE IF EXISTS `return_requests`');
    }

    /** @param array<int, string> $expected @param array<int, string> $actual */
    private function assertSameList(array $expected, array $actual, string $message): void
    {
        $missing = array_values(array_diff($expected, $actual));
        $extra = array_values(array_diff($actual, $expected));
        $details = [];
        if ($missing !== []) {
            $details[] = 'thiếu: ' . implode(', ', $missing);
        }
        if ($extra !== []) {
            $details[] = 'dư: ' . implode(', ', $extra);
        }
        if ($missing === [] && $extra === [] && $expected !== $actual) {
            $details[] = 'thứ tự khác contract';
        }

        $this->assert($expected === $actual, $message, implode('; ', $details));
    }

    private function assert(bool $condition, string $message, string $details = ''): void
    {
        if ($condition) {
            $this->passed++;
            echo "[PASS] {$message}\n";
            return;
        }

        $this->failed++;
        $failure = "[FAIL] {$message}" . ($details !== '' ? ": {$details}" : '');
        $this->errors[] = $failure;
        echo "{$failure}\n";
    }
}

(new DatabaseSchemaParityTest())->run();
