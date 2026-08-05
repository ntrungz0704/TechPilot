<?php

/**
 * Migration: Add FK constraints to return_requests, return_items, inventory_logs
 * 
 * Giải quyết audit issue #9, #10: các bảng này chỉ có index, không có FK cấp DB.
 * 
 * Bước 1: Sửa column type cho khớp với bảng tham chiếu
 *   - orders.id, order_items.id là BIGINT UNSIGNED
 *   - return_requests.order_id, return_items.order_item_id, inventory_logs.order_id
 *     cần đổi từ INT UNSIGNED → BIGINT UNSIGNED
 * 
 * Bước 2: Thêm FK constraints (idempotent qua INFORMATION_SCHEMA check)
 *   return_requests.order_id    → orders.id       (CASCADE on delete)
 *   return_requests.user_id     → users.id        (CASCADE on delete)
 *   return_items.return_request_id → return_requests.id (CASCADE on delete)
 *   return_items.order_item_id  → order_items.id   (CASCADE on delete)
 *   inventory_logs.product_id   → products.id      (CASCADE on delete)
 *   inventory_logs.order_id     → orders.id        (SET NULL on delete, nullable)
 *   inventory_logs.created_by   → users.id         (SET NULL on delete, nullable)
 */
class Migration_2026_08_05_000001_add_fk_constraints_return_inventory
{
    public static function up(PDO $db): bool
    {
        // ── Bước 1: Sửa column types cho compatible ──────────────────────────
        $typeFixSql = [
            "ALTER TABLE `return_requests` MODIFY `order_id` BIGINT UNSIGNED NOT NULL",
            "ALTER TABLE `return_items` MODIFY `order_item_id` BIGINT UNSIGNED NOT NULL",
            "ALTER TABLE `inventory_logs` MODIFY `order_id` BIGINT UNSIGNED NULL",
        ];

        foreach ($typeFixSql as $sql) {
            $db->exec($sql);
        }

        // ── Bước 2: Thêm FK constraints ─────────────────────────────────────
        $constraints = [
            // return_requests
            ['return_requests', 'fk_rr_order',   'order_id',          'orders',          'id', 'CASCADE'],
            ['return_requests', 'fk_rr_user',    'user_id',           'users',           'id', 'CASCADE'],
            // return_items
            ['return_items',    'fk_ri_request',  'return_request_id', 'return_requests', 'id', 'CASCADE'],
            ['return_items',    'fk_ri_order_item','order_item_id',    'order_items',     'id', 'CASCADE'],
            // inventory_logs
            ['inventory_logs',  'fk_il_product',  'product_id',        'products',        'id', 'CASCADE'],
            ['inventory_logs',  'fk_il_order',    'order_id',           'orders',          'id', 'SET NULL'],
            ['inventory_logs',  'fk_il_creator',  'created_by',         'users',           'id', 'SET NULL'],
        ];

        foreach ($constraints as [$table, $fkName, $column, $refTable, $refColumn, $onDelete]) {
            if (self::fkExists($db, $table, $fkName)) {
                continue; // FK đã tồn tại, bỏ qua
            }

            $sql = "ALTER TABLE `{$table}` ADD CONSTRAINT `{$fkName}` "
                 . "FOREIGN KEY (`{$column}`) REFERENCES `{$refTable}`(`{$refColumn}`) "
                 . "ON DELETE {$onDelete} ON UPDATE CASCADE";

            $db->exec($sql);
        }

        return true;
    }

    /**
     * Kiểm tra FK constraint đã tồn tại trong INFORMATION_SCHEMA chưa
     */
    private static function fkExists(PDO $db, string $table, string $constraintName): bool
    {
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :table
               AND CONSTRAINT_NAME = :name
               AND CONSTRAINT_TYPE = 'FOREIGN KEY'"
        );
        $stmt->execute([':table' => $table, ':name' => $constraintName]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
