<?php

/**
 * Đơn hàng phải bắt đầu ở not_reserved. InventoryService chỉ chuyển sang
 * reserved sau khi đã trừ stock và ghi audit log trong cùng transaction.
 * Migration này chỉ đổi DEFAULT của schema, không cập nhật dữ liệu đơn cũ.
 */
class Migration_2026_08_01_000004_fix_order_inventory_status_default
{
    public const INVENTORY_STATUSES = [
        'not_reserved',
        'reserved',
        'released',
        'committed',
    ];

    public static function up(PDO $db): bool
    {
        $column = self::inventoryStatusColumn($db);
        self::assertColumnCanTransition($column);

        if (self::columnMatches($column, 'not_reserved')) {
            return true;
        }

        self::alterDefault($db, 'not_reserved');
        if (!self::columnMatches(self::inventoryStatusColumn($db), 'not_reserved')) {
            throw new RuntimeException('Không thể xác minh default orders.inventory_status sau migration up().');
        }

        return true;
    }

    public static function down(PDO $db): bool
    {
        $column = self::inventoryStatusColumn($db);
        self::assertColumnCanTransition($column);

        if (self::columnMatches($column, 'reserved')) {
            return true;
        }

        self::alterDefault($db, 'reserved');
        if (!self::columnMatches(self::inventoryStatusColumn($db), 'reserved')) {
            throw new RuntimeException('Không thể xác minh default orders.inventory_status sau migration down().');
        }

        return true;
    }

    private static function inventoryStatusColumn(PDO $db): array
    {
        $column = $db->query("SHOW COLUMNS FROM `orders` LIKE 'inventory_status'")
            ->fetch(PDO::FETCH_ASSOC);

        if (!is_array($column)) {
            throw new RuntimeException('Không tìm thấy cột orders.inventory_status.');
        }

        return [
            'type' => (string)($column['Type'] ?? ''),
            'null' => strtoupper((string)($column['Null'] ?? '')),
            'default' => $column['Default'] ?? null,
        ];
    }

    private static function assertColumnCanTransition(array $column): void
    {
        $statuses = self::enumValues((string)$column['type']);
        if ($statuses !== self::INVENTORY_STATUSES || $column['null'] !== 'NO') {
            throw new RuntimeException(
                'Schema orders.inventory_status không khớp contract; migration dừng để tránh che giấu schema drift.'
            );
        }

        if (!in_array((string)$column['default'], ['reserved', 'not_reserved'], true)) {
            throw new RuntimeException(
                'Default orders.inventory_status nằm ngoài trạng thái legacy/target được hỗ trợ.'
            );
        }
    }

    private static function columnMatches(array $column, string $default): bool
    {
        return self::enumValues((string)$column['type']) === self::INVENTORY_STATUSES
            && $column['null'] === 'NO'
            && (string)$column['default'] === $default;
    }

    private static function enumValues(string $columnType): array
    {
        if (!preg_match('/^enum\((.*)\)$/i', trim($columnType), $match)) {
            throw new RuntimeException(
                'orders.inventory_status không phải ENUM hợp lệ: ' . $columnType
            );
        }

        preg_match_all("/'((?:''|[^'])*)'/", $match[1], $values);
        return array_map(
            static fn(string $value): string => str_replace("''", "'", $value),
            $values[1] ?? []
        );
    }

    private static function alterDefault(PDO $db, string $default): void
    {
        if (!in_array($default, ['reserved', 'not_reserved'], true)) {
            throw new InvalidArgumentException('Default inventory status không hợp lệ.');
        }

        $enumDefinition = implode(
            ',',
            array_map(
                static fn(string $status): string => "'{$status}'",
                self::INVENTORY_STATUSES
            )
        );

        $db->exec(
            "ALTER TABLE `orders`
             MODIFY COLUMN `inventory_status` ENUM({$enumDefinition}) NOT NULL DEFAULT '{$default}'"
        );
    }
}
