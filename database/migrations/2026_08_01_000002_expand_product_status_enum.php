<?php

/**
 * Mở rộng lifecycle status của products mà không thay đổi thứ tự ba giá trị
 * legacy. Migration từ chối mọi thao tác có nguy cơ ép hoặc làm mất dữ liệu.
 */
class Migration_2026_08_01_000002_expand_product_status_enum
{
    public const LEGACY_STATUSES = [
        'draft',
        'active',
        'inactive',
    ];

    public const TARGET_STATUSES = [
        'draft',
        'active',
        'inactive',
        'hidden',
        'out_of_stock',
        'discontinued',
        'archived',
    ];

    private const NEW_STATUSES = [
        'hidden',
        'out_of_stock',
        'discontinued',
        'archived',
    ];

    public static function up(PDO $db): bool
    {
        $column = self::statusColumn($db);
        $currentStatuses = self::enumValues((string)$column['type']);

        self::assertSchemaCanTransition($currentStatuses, self::TARGET_STATUSES);
        self::assertStoredValuesAreSupported($db, self::TARGET_STATUSES);

        if (self::columnMatches($column, self::TARGET_STATUSES)) {
            return true;
        }

        self::alterStatusColumn($db, self::TARGET_STATUSES);
        $updatedColumn = self::statusColumn($db);
        if (!self::columnMatches($updatedColumn, self::TARGET_STATUSES)) {
            throw new RuntimeException('Không thể xác minh ENUM products.status sau migration up().');
        }

        return true;
    }

    public static function down(PDO $db): bool
    {
        $column = self::statusColumn($db);
        $currentStatuses = self::enumValues((string)$column['type']);

        if (self::columnMatches($column, self::LEGACY_STATUSES)) {
            return true;
        }

        self::assertSchemaCanTransition($currentStatuses, self::TARGET_STATUSES);

        $quotedStatuses = self::quotedValues($db, self::NEW_STATUSES);
        $rows = $db->query(
            "SELECT `status`, COUNT(*) AS `total`
             FROM `products`
             WHERE `status` IN ({$quotedStatuses})
             GROUP BY `status`
             ORDER BY `status`"
        )->fetchAll(PDO::FETCH_ASSOC);

        if ($rows !== []) {
            $usage = array_map(
                static fn(array $row): string => (string)$row['status'] . '=' . (int)$row['total'],
                $rows
            );
            throw new RuntimeException(
                'Không thể rollback products.status vì status mới đang được sử dụng: '
                . implode(', ', $usage)
            );
        }

        self::alterStatusColumn($db, self::LEGACY_STATUSES);
        $rolledBackColumn = self::statusColumn($db);
        if (!self::columnMatches($rolledBackColumn, self::LEGACY_STATUSES)) {
            throw new RuntimeException('Không thể xác minh ENUM products.status sau migration down().');
        }

        return true;
    }

    private static function statusColumn(PDO $db): array
    {
        $column = $db->query("SHOW COLUMNS FROM `products` LIKE 'status'")
            ->fetch(PDO::FETCH_ASSOC);

        if (!is_array($column)) {
            throw new RuntimeException('Không tìm thấy cột products.status.');
        }

        return [
            'type' => (string)($column['Type'] ?? $column['type'] ?? ''),
            'null' => strtoupper((string)($column['Null'] ?? $column['null'] ?? '')),
            'default' => $column['Default'] ?? $column['default'] ?? null,
        ];
    }

    private static function enumValues(string $columnType): array
    {
        if (!preg_match('/^enum\((.*)\)$/i', trim($columnType), $match)) {
            throw new RuntimeException('products.status không phải cột ENUM hợp lệ: ' . $columnType);
        }

        preg_match_all("/'((?:''|[^'])*)'/", $match[1], $values);
        return array_map(
            static fn(string $value): string => str_replace("''", "'", $value),
            $values[1] ?? []
        );
    }

    private static function assertSchemaCanTransition(array $current, array $allowed): void
    {
        $unexpected = array_values(array_diff($current, $allowed));
        if ($unexpected !== []) {
            throw new RuntimeException(
                'products.status đang có ENUM ngoài contract; migration dừng để tránh mất dữ liệu: '
                . implode(', ', $unexpected)
            );
        }
    }

    private static function assertStoredValuesAreSupported(PDO $db, array $allowed): void
    {
        $quotedStatuses = self::quotedValues($db, $allowed);
        $rows = $db->query(
            "SELECT DISTINCT `status`
             FROM `products`
             WHERE `status` IS NULL OR `status` NOT IN ({$quotedStatuses})"
        )->fetchAll(PDO::FETCH_COLUMN);

        if ($rows !== []) {
            throw new RuntimeException(
                'products đang chứa status ngoài contract; migration dừng để tránh ép kiểu: '
                . implode(', ', array_map('strval', $rows))
            );
        }
    }

    private static function columnMatches(array $column, array $statuses): bool
    {
        return self::enumValues((string)$column['type']) === $statuses
            && $column['null'] === 'NO'
            && (string)$column['default'] === 'active';
    }

    private static function alterStatusColumn(PDO $db, array $statuses): void
    {
        $enumDefinition = implode(
            ',',
            array_map(
                static fn(string $status): string => "'" . str_replace("'", "''", $status) . "'",
                $statuses
            )
        );

        $db->exec(
            "ALTER TABLE `products`
             MODIFY COLUMN `status` ENUM({$enumDefinition}) NOT NULL DEFAULT 'active'"
        );
    }

    private static function quotedValues(PDO $db, array $values): string
    {
        return implode(
            ',',
            array_map(static fn(string $value): string => $db->quote($value), $values)
        );
    }
}
