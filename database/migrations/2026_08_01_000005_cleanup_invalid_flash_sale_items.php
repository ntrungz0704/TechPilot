<?php

/**
 * Dọn đúng 6 Flash Sale item legacy của campaign #33 không còn đạt contract:
 * - 4 item có discount_price = 0;
 * - 2 item có giá Flash cao hơn sale_price hiện hành.
 *
 * Migration kiểm tra fingerprint từng dòng trước khi xóa để không đụng nhầm
 * dữ liệu đã được admin chỉnh sau thời điểm audit.
 */
class Migration_2026_08_01_000005_cleanup_invalid_flash_sale_items
{
    private const TARGET_ROWS = [
        50 => [
            'flash_sale_id' => 33,
            'product_id' => 637,
            'discount_price' => 49491000.0,
            'allocation_quantity' => 8,
            'sold_quantity' => 0,
            'limit_per_user' => 1,
        ],
        52 => [
            'flash_sale_id' => 33,
            'product_id' => 633,
            'discount_price' => 53991000.0,
            'allocation_quantity' => 10,
            'sold_quantity' => 0,
            'limit_per_user' => 1,
        ],
        53 => [
            'flash_sale_id' => 33,
            'product_id' => 657,
            'discount_price' => 0.0,
            'allocation_quantity' => 10,
            'sold_quantity' => 0,
            'limit_per_user' => 2,
        ],
        55 => [
            'flash_sale_id' => 33,
            'product_id' => 659,
            'discount_price' => 0.0,
            'allocation_quantity' => 10,
            'sold_quantity' => 0,
            'limit_per_user' => 2,
        ],
        57 => [
            'flash_sale_id' => 33,
            'product_id' => 654,
            'discount_price' => 0.0,
            'allocation_quantity' => 10,
            'sold_quantity' => 0,
            'limit_per_user' => 2,
        ],
        58 => [
            'flash_sale_id' => 33,
            'product_id' => 655,
            'discount_price' => 0.0,
            'allocation_quantity' => 10,
            'sold_quantity' => 0,
            'limit_per_user' => 2,
        ],
    ];

    public static function up(PDO $db): bool
    {
        return self::atomically($db, static function () use ($db): void {
            $presentTargets = [];

            // Xác minh toàn bộ target trước, chỉ bắt đầu xóa khi tất cả đều an toàn.
            foreach (self::TARGET_ROWS as $id => $expected) {
                $row = self::fetchTargetWithProduct($db, $id);
                if ($row === null) {
                    continue;
                }

                self::assertMatchesBaseline($id, $row, $expected);
                if (!self::isInvalidAgainstCurrentProductPrice($row)) {
                    throw new RuntimeException(
                        "Flash Sale item #{$id} hiện đã hợp lệ; migration dừng để không xóa dữ liệu mới."
                    );
                }
                $presentTargets[$id] = $expected;
            }

            $delete = $db->prepare(
                'DELETE FROM flash_sale_items
                 WHERE id = :id AND flash_sale_id = :flash_sale_id AND product_id = :product_id'
            );
            foreach ($presentTargets as $id => $expected) {
                $delete->execute([
                    ':id' => $id,
                    ':flash_sale_id' => $expected['flash_sale_id'],
                    ':product_id' => $expected['product_id'],
                ]);
                if ($delete->rowCount() !== 1) {
                    throw new RuntimeException("Không thể xóa chính xác Flash Sale item #{$id}.");
                }
            }

            foreach (array_keys(self::TARGET_ROWS) as $id) {
                if (self::fetchItemById($db, $id) !== null) {
                    throw new RuntimeException("Flash Sale item #{$id} vẫn tồn tại sau cleanup.");
                }
            }
        });
    }

    public static function down(PDO $db): bool
    {
        return self::atomically($db, static function () use ($db): void {
            $campaignStmt = $db->prepare('SELECT id FROM flash_sales WHERE id = :id FOR UPDATE');
            $campaignStmt->execute([':id' => 33]);
            if (!$campaignStmt->fetchColumn()) {
                throw new RuntimeException('Không thể rollback cleanup: campaign #33 không còn tồn tại.');
            }

            $productStmt = $db->prepare('SELECT id FROM products WHERE id = :id FOR UPDATE');
            $uniqueStmt = $db->prepare(
                'SELECT id FROM flash_sale_items
                 WHERE flash_sale_id = :flash_sale_id AND product_id = :product_id
                 LIMIT 1 FOR UPDATE'
            );
            $missingTargets = [];

            // Kiểm tra toàn bộ xung đột trước khi khôi phục bất kỳ dòng nào.
            foreach (self::TARGET_ROWS as $id => $expected) {
                $productStmt->execute([':id' => $expected['product_id']]);
                if (!$productStmt->fetchColumn()) {
                    throw new RuntimeException(
                        "Không thể rollback cleanup: product #{$expected['product_id']} không còn tồn tại."
                    );
                }

                $existing = self::fetchItemById($db, $id);
                if ($existing !== null) {
                    self::assertMatchesBaseline($id, $existing, $expected);
                    continue;
                }

                $uniqueStmt->execute([
                    ':flash_sale_id' => $expected['flash_sale_id'],
                    ':product_id' => $expected['product_id'],
                ]);
                if ($uniqueStmt->fetchColumn()) {
                    throw new RuntimeException(
                        "Không thể rollback cleanup: campaign/product của item #{$id} đã được dùng bởi dòng khác."
                    );
                }
                $missingTargets[$id] = $expected;
            }

            $insert = $db->prepare(
                'INSERT INTO flash_sale_items
                    (id, flash_sale_id, product_id, discount_price, allocation_quantity, sold_quantity, limit_per_user)
                 VALUES
                    (:id, :flash_sale_id, :product_id, :discount_price, :allocation_quantity, :sold_quantity, :limit_per_user)'
            );
            foreach ($missingTargets as $id => $expected) {
                $insert->execute([
                    ':id' => $id,
                    ':flash_sale_id' => $expected['flash_sale_id'],
                    ':product_id' => $expected['product_id'],
                    ':discount_price' => $expected['discount_price'],
                    ':allocation_quantity' => $expected['allocation_quantity'],
                    ':sold_quantity' => $expected['sold_quantity'],
                    ':limit_per_user' => $expected['limit_per_user'],
                ]);
            }
        });
    }

    private static function atomically(PDO $db, callable $operation): bool
    {
        $ownsTransaction = !$db->inTransaction();
        if ($ownsTransaction) {
            $db->beginTransaction();
        }

        try {
            $operation();
            if ($ownsTransaction) {
                $db->commit();
            }
            return true;
        } catch (Throwable $error) {
            if ($ownsTransaction && $db->inTransaction()) {
                $db->rollBack();
            }
            throw $error;
        }
    }

    private static function fetchTargetWithProduct(PDO $db, int $id): ?array
    {
        $stmt = $db->prepare(
            'SELECT fsi.*, p.price, p.sale_price
             FROM flash_sale_items fsi
             INNER JOIN products p ON p.id = fsi.product_id
             WHERE fsi.id = :id
             LIMIT 1 FOR UPDATE'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    private static function fetchItemById(PDO $db, int $id): ?array
    {
        $stmt = $db->prepare(
            'SELECT id, flash_sale_id, product_id, discount_price,
                    allocation_quantity, sold_quantity, limit_per_user
             FROM flash_sale_items
             WHERE id = :id
             LIMIT 1 FOR UPDATE'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    private static function assertMatchesBaseline(int $id, array $row, array $expected): void
    {
        foreach (['flash_sale_id', 'product_id', 'allocation_quantity', 'sold_quantity', 'limit_per_user'] as $field) {
            if ((int)($row[$field] ?? -1) !== (int)$expected[$field]) {
                throw new RuntimeException(
                    "Flash Sale item #{$id} đã lệch baseline tại {$field}; migration dừng."
                );
            }
        }

        if (abs((float)($row['discount_price'] ?? -1) - (float)$expected['discount_price']) > 0.001) {
            throw new RuntimeException(
                "Flash Sale item #{$id} đã lệch baseline tại discount_price; migration dừng."
            );
        }
    }

    private static function isInvalidAgainstCurrentProductPrice(array $row): bool
    {
        $basePrice = (float)($row['price'] ?? 0);
        $salePrice = (float)($row['sale_price'] ?? 0);
        $regularPrice = ($salePrice > 0 && $salePrice < $basePrice) ? $salePrice : $basePrice;
        $discountPrice = (float)($row['discount_price'] ?? 0);

        return $discountPrice <= 0 || $discountPrice >= $regularPrice;
    }
}
