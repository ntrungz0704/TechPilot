<?php

/**
 * Tạo ledger giữ hạn mức Flash Sale theo từng order item.
 * Đơn legacy không được backfill vì không thể chứng minh giá đã đến từ campaign nào.
 */
class Migration_2026_08_01_000006_create_flash_sale_reservations
{
    private const TABLE_CONTRACT = [
        'id' => ['bigint unsigned', 'NO', null, 'auto_increment'],
        'flash_sale_item_id' => ['int unsigned', 'NO', null, ''],
        'order_id' => ['bigint unsigned', 'NO', null, ''],
        'order_item_id' => ['bigint unsigned', 'NO', null, ''],
        'user_id' => ['int unsigned', 'YES', null, ''],
        'buyer_key' => ['varchar(191)', 'NO', null, ''],
        'quantity' => ['int unsigned', 'NO', null, ''],
        'unit_price' => ['decimal(15,2)', 'NO', null, ''],
        'status' => ["enum('reserved','committed','released')", 'NO', 'reserved', ''],
        'reserved_at' => ['datetime', 'NO', 'current_timestamp', ''],
        'committed_at' => ['datetime', 'YES', null, ''],
        'released_at' => ['datetime', 'YES', null, ''],
        'release_reason' => ['varchar(100)', 'YES', null, ''],
    ];

    public static function up(PDO $db): bool
    {
        $db->exec(
            "CREATE TABLE IF NOT EXISTS `flash_sale_reservations` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `flash_sale_item_id` INT UNSIGNED NOT NULL,
                `order_id` BIGINT UNSIGNED NOT NULL,
                `order_item_id` BIGINT UNSIGNED NOT NULL,
                `user_id` INT UNSIGNED NULL,
                `buyer_key` VARCHAR(191) NOT NULL,
                `quantity` INT UNSIGNED NOT NULL,
                `unit_price` DECIMAL(15,2) NOT NULL,
                `status` ENUM('reserved','committed','released') NOT NULL DEFAULT 'reserved',
                `reserved_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `committed_at` DATETIME NULL,
                `released_at` DATETIME NULL,
                `release_reason` VARCHAR(100) NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_flash_reservation_order_item` (`order_item_id`),
                KEY `idx_flash_reservation_item_status` (`flash_sale_item_id`, `status`),
                KEY `idx_flash_reservation_buyer` (`flash_sale_item_id`, `buyer_key`, `status`),
                KEY `idx_flash_reservation_order` (`order_id`, `status`),
                KEY `idx_flash_reservation_user` (`user_id`),
                CONSTRAINT `fk_flash_reservations_item`
                    FOREIGN KEY (`flash_sale_item_id`) REFERENCES `flash_sale_items` (`id`) ON DELETE RESTRICT,
                CONSTRAINT `fk_flash_reservations_order`
                    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE RESTRICT,
                CONSTRAINT `fk_flash_reservations_order_item`
                    FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE RESTRICT,
                CONSTRAINT `fk_flash_reservations_user`
                    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        self::assertTableContract($db);
        self::assertIndex($db, 'PRIMARY', ['id'], true);
        self::assertIndex($db, 'uq_flash_reservation_order_item', ['order_item_id'], true);
        self::assertIndex($db, 'idx_flash_reservation_item_status', ['flash_sale_item_id', 'status'], false);
        self::assertIndex($db, 'idx_flash_reservation_buyer', ['flash_sale_item_id', 'buyer_key', 'status'], false);
        self::assertIndex($db, 'idx_flash_reservation_order', ['order_id', 'status'], false);
        self::assertIndex($db, 'idx_flash_reservation_user', ['user_id'], false);

        return true;
    }

    public static function down(PDO $db): bool
    {
        $count = (int)$db->query('SELECT COUNT(*) FROM `flash_sale_reservations`')->fetchColumn();
        if ($count > 0) {
            throw new RuntimeException(
                "Không thể rollback flash_sale_reservations vì đang có {$count} reservation."
            );
        }

        $db->exec('DROP TABLE IF EXISTS `flash_sale_reservations`');
        return true;
    }

    private static function assertTableContract(PDO $db): void
    {
        $columns = $db->query('SHOW COLUMNS FROM `flash_sale_reservations`')
            ->fetchAll(PDO::FETCH_ASSOC);
        $actualNames = array_map(
            static fn(array $column): string => (string)($column['Field'] ?? ''),
            $columns
        );

        if ($actualNames !== array_keys(self::TABLE_CONTRACT)) {
            throw new RuntimeException(
                'Schema flash_sale_reservations không khớp contract; migration dừng để tránh che giấu drift.'
            );
        }

        foreach ($columns as $column) {
            $name = (string)$column['Field'];
            [$type, $nullable, $default, $extra] = self::TABLE_CONTRACT[$name];
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
                    "Schema flash_sale_reservations.{$name} không khớp contract."
                );
            }
        }
    }

    /** @param array<int, string> $columns */
    private static function assertIndex(PDO $db, string $indexName, array $columns, bool $unique): void
    {
        $rows = $db->query('SHOW INDEX FROM `flash_sale_reservations`')->fetchAll(PDO::FETCH_ASSOC);
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
            throw new RuntimeException(
                "Index flash_sale_reservations.{$indexName} không khớp contract."
            );
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
        $normalized = str_replace('default_generated', '', $normalized);
        $normalized = str_replace('current_timestamp()', 'current_timestamp', $normalized);
        return trim((string)preg_replace('/\s+/', ' ', $normalized));
    }
}
