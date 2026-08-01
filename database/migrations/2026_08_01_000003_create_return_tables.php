<?php

/**
 * Khôi phục hai bảng đổi trả đã được runtime sử dụng nhưng bị thiếu khỏi
 * fresh-install seed/migration chain. Migration không tự ý ALTER schema lạ.
 */
class Migration_2026_08_01_000003_create_return_tables
{
    private const TABLE_CONTRACTS = [
        'return_requests' => [
            'id' => ['int unsigned', 'NO', null, 'auto_increment'],
            'return_code' => ['varchar(50)', 'NO', null, ''],
            'order_id' => ['int unsigned', 'NO', null, ''],
            'user_id' => ['int unsigned', 'NO', null, ''],
            'reason' => ['varchar(255)', 'NO', null, ''],
            'description' => ['text', 'YES', null, ''],
            'status' => ["enum('requested','approved','rejected','completed')", 'YES', 'requested', ''],
            'created_at' => ['timestamp', 'YES', 'current_timestamp', ''],
            'updated_at' => ['timestamp', 'YES', 'current_timestamp', 'on update current_timestamp'],
        ],
        'return_items' => [
            'id' => ['int unsigned', 'NO', null, 'auto_increment'],
            'return_request_id' => ['int unsigned', 'NO', null, ''],
            'order_item_id' => ['int unsigned', 'NO', null, ''],
            'quantity' => ['int unsigned', 'NO', null, ''],
            'resolution' => ["enum('refund','replace','repair')", 'YES', 'refund', ''],
            'created_at' => ['timestamp', 'YES', 'current_timestamp', ''],
        ],
    ];

    public static function up(PDO $db): bool
    {
        $db->exec(
            "CREATE TABLE IF NOT EXISTS `return_requests` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `return_code` VARCHAR(50) NOT NULL,
                `order_id` INT UNSIGNED NOT NULL,
                `user_id` INT UNSIGNED NOT NULL,
                `reason` VARCHAR(255) NOT NULL,
                `description` TEXT NULL,
                `status` ENUM('requested','approved','rejected','completed') NULL DEFAULT 'requested',
                `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `return_code` (`return_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $db->exec(
            "CREATE TABLE IF NOT EXISTS `return_items` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `return_request_id` INT UNSIGNED NOT NULL,
                `order_item_id` INT UNSIGNED NOT NULL,
                `quantity` INT UNSIGNED NOT NULL,
                `resolution` ENUM('refund','replace','repair') NULL DEFAULT 'refund',
                `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        foreach (self::TABLE_CONTRACTS as $table => $contract) {
            self::assertTableContract($db, $table, $contract);
        }
        self::assertIndex($db, 'return_requests', 'PRIMARY', ['id'], true);
        self::assertIndex($db, 'return_requests', 'return_code', ['return_code'], true);
        self::assertIndex($db, 'return_items', 'PRIMARY', ['id'], true);

        return true;
    }

    public static function down(PDO $db): bool
    {
        $usedTables = [];
        foreach (array_keys(self::TABLE_CONTRACTS) as $table) {
            $count = self::tableRowCount($db, $table);
            if ($count > 0) {
                $usedTables[] = $table . '=' . $count;
            }
        }

        if ($usedTables !== []) {
            throw new RuntimeException(
                'Không thể rollback return tables vì đang có dữ liệu: '
                . implode(', ', $usedTables)
            );
        }

        $db->exec('DROP TABLE IF EXISTS `return_items`');
        $db->exec('DROP TABLE IF EXISTS `return_requests`');
        return true;
    }

    /** @param array<string, array{0:string,1:string,2:?string,3:string}> $contract */
    private static function assertTableContract(PDO $db, string $table, array $contract): void
    {
        $columns = $db->query(
            'SHOW COLUMNS FROM `' . str_replace('`', '``', $table) . '`'
        )->fetchAll(PDO::FETCH_ASSOC);
        $actualNames = array_map(
            static fn(array $column): string => (string)($column['Field'] ?? ''),
            $columns
        );

        if ($actualNames !== array_keys($contract)) {
            throw new RuntimeException(
                "Schema {$table} không khớp contract; migration dừng để tránh che giấu schema drift."
            );
        }

        foreach ($columns as $column) {
            $name = (string)$column['Field'];
            [$type, $nullable, $default, $extra] = $contract[$name];
            $actual = [
                self::normalizeType((string)$column['Type']),
                strtoupper((string)$column['Null']),
                self::normalizeDefault($column['Default'] ?? null),
                self::normalizeExtra((string)($column['Extra'] ?? '')),
            ];
            $expected = [
                self::normalizeType($type),
                $nullable,
                self::normalizeDefault($default),
                self::normalizeExtra($extra),
            ];

            if ($actual !== $expected) {
                throw new RuntimeException(
                    "Schema {$table}.{$name} không khớp contract; migration không tự ALTER dữ liệu hiện có."
                );
            }
        }
    }

    /** @param array<int, string> $columns */
    private static function assertIndex(
        PDO $db,
        string $table,
        string $indexName,
        array $columns,
        bool $unique
    ): void {
        $rows = $db->query(
            'SHOW INDEX FROM `' . str_replace('`', '``', $table) . '`'
        )->fetchAll(PDO::FETCH_ASSOC);
        $matched = array_values(array_filter(
            $rows,
            static fn(array $row): bool => (string)($row['Key_name'] ?? '') === $indexName
        ));
        usort(
            $matched,
            static fn(array $left, array $right): int =>
                (int)($left['Seq_in_index'] ?? 0) <=> (int)($right['Seq_in_index'] ?? 0)
        );
        $actualColumns = array_map(
            static fn(array $row): string => (string)($row['Column_name'] ?? ''),
            $matched
        );
        $actualUnique = $matched !== [] && (int)($matched[0]['Non_unique'] ?? 1) === 0;

        if ($actualColumns !== $columns || $actualUnique !== $unique) {
            throw new RuntimeException("Index {$table}.{$indexName} không khớp contract.");
        }
    }

    private static function tableRowCount(PDO $db, string $table): int
    {
        try {
            return (int)$db->query(
                'SELECT COUNT(*) FROM `' . str_replace('`', '``', $table) . '`'
            )->fetchColumn();
        } catch (PDOException $error) {
            $driverCode = (int)($error->errorInfo[1] ?? 0);
            if ((string)$error->getCode() === '42S02' || $driverCode === 1146) {
                return 0;
            }
            throw $error;
        }
    }

    private static function normalizeType(string $type): string
    {
        $normalized = strtolower(trim($type));
        return (string)preg_replace(
            '/\b(tinyint|smallint|mediumint|int|bigint)\(\d+\)/',
            '$1',
            $normalized
        );
    }

    private static function normalizeDefault(mixed $default): ?string
    {
        if ($default === null) {
            return null;
        }

        return str_replace('current_timestamp()', 'current_timestamp', strtolower((string)$default));
    }

    private static function normalizeExtra(string $extra): string
    {
        $normalized = strtolower(trim($extra));
        if ($normalized === 'null') {
            return '';
        }

        // MySQL 8 thêm DEFAULT_GENERATED vào metadata, MariaDB/temporary table
        // thì không; đây là khác biệt biểu diễn, không phải khác biệt schema.
        $normalized = str_replace('default_generated', '', $normalized);
        $normalized = str_replace('current_timestamp()', 'current_timestamp', $normalized);
        return trim((string)preg_replace('/\s+/', ' ', $normalized));
    }
}
