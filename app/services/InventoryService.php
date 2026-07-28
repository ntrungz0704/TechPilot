<?php

/**
 * Service quản lý Tồn kho và Số lượng Sản phẩm cho TechPilot.
 * Single Source of Truth cho toàn bộ chỉ số tồn kho, đếm mẫu, reservation và idempotent release.
 */
class InventoryService
{
    private static function getDb(): ?PDO
    {
        require_once ROOT_PATH . '/config/database.php';
        return Database::getConnection();
    }

    /**
     * 1. Thống kê toàn bộ chỉ số tồn kho hệ thống (Single Source of Truth)
     */
    public static function getGlobalSummary(?PDO $db = null): array
    {
        $db = $db ?? self::getDb();
        if (!$db) {
            return [
                'total_product_models'  => 0,
                'active_product_models' => 0,
                'total_inventory_units' => 0,
                'low_stock_models'      => 0,
                'out_of_stock_models'   => 0,
                'total_sold_units'      => 0,
            ];
        }

        try {
            $stmt = $db->query("
                SELECT
                    (SELECT COUNT(DISTINCT id) FROM products) AS total_product_models,
                    (SELECT COUNT(DISTINCT id) FROM products WHERE status = 'active') AS active_product_models,
                    (SELECT COALESCE(SUM(stock), 0) FROM products WHERE status = 'active') AS total_inventory_units,
                    (SELECT COUNT(*) FROM products WHERE status = 'active' AND stock BETWEEN 1 AND 9) AS low_stock_models,
                    (SELECT COUNT(*) FROM products WHERE status = 'active' AND stock = 0) AS out_of_stock_models,
                    (SELECT COALESCE(SUM(oi.quantity), 0) 
                     FROM order_items oi 
                     JOIN orders o ON o.id = oi.order_id 
                     WHERE o.status IN ('confirmed', 'processing', 'shipping', 'completed')) AS total_sold_units
            ");
            $res = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'total_product_models'  => (int)($res['total_product_models'] ?? 0),
                'active_product_models' => (int)($res['active_product_models'] ?? 0),
                'total_inventory_units' => (int)($res['total_inventory_units'] ?? 0),
                'low_stock_models'      => (int)($res['low_stock_models'] ?? 0),
                'out_of_stock_models'   => (int)($res['out_of_stock_models'] ?? 0),
                'total_sold_units'      => (int)($res['total_sold_units'] ?? 0),
            ];
        } catch (Exception $e) {
            error_log('InventoryService::getGlobalSummary error: ' . $e->getMessage());
            return [
                'total_product_models'  => 0,
                'active_product_models' => 0,
                'total_inventory_units' => 0,
                'low_stock_models'      => 0,
                'out_of_stock_models'   => 0,
                'total_sold_units'      => 0,
            ];
        }
    }

    /**
     * 2. Thống kê theo danh mục (Tách biệt Số mẫu và Tổng tồn kho)
     */
    public static function getCategorySummary(?int $categoryId = null, ?PDO $db = null): array
    {
        $db = $db ?? self::getDb();
        if (!$db) return [];

        try {
            if ($categoryId !== null && $categoryId > 0) {
                $stmt = $db->prepare("
                    SELECT
                        c.id AS category_id,
                        c.name AS category_name,
                        COUNT(DISTINCT p.id) AS product_models,
                        COALESCE(SUM(CASE WHEN p.status = 'active' THEN p.stock ELSE 0 END), 0) AS inventory_units
                    FROM categories c
                    LEFT JOIN categories child ON child.parent_id = c.id
                    LEFT JOIN products p ON (p.category_id = c.id OR p.category_id = child.id)
                    WHERE c.id = :cat_id
                    GROUP BY c.id, c.name
                ");
                $stmt->execute([':cat_id' => $categoryId]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return [
                    'category_id'     => (int)($row['category_id'] ?? $categoryId),
                    'category_name'   => $row['category_name'] ?? '',
                    'product_models'  => (int)($row['product_models'] ?? 0),
                    'inventory_units' => (int)($row['inventory_units'] ?? 0),
                ];
            }

            $stmt = $db->query("
                SELECT
                    c.id AS category_id,
                    c.name AS category_name,
                    c.slug AS category_slug,
                    COUNT(DISTINCT p.id) AS product_models,
                    COALESCE(SUM(CASE WHEN p.status = 'active' THEN p.stock ELSE 0 END), 0) AS inventory_units
                FROM categories c
                LEFT JOIN categories child ON child.parent_id = c.id
                LEFT JOIN products p ON (p.category_id = c.id OR p.category_id = child.id)
                GROUP BY c.id, c.name, c.slug
                ORDER BY c.sort_order ASC, c.id DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('InventoryService::getCategorySummary error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * 3. Lấy tồn kho và trạng thái mua hàng của một sản phẩm
     */
    public static function getProductStock(int $productId, ?PDO $db = null): array
    {
        $db = $db ?? self::getDb();
        if (!$db || $productId <= 0) {
            return ['product_id' => $productId, 'stock' => 0, 'purchasable' => false];
        }

        try {
            $stmt = $db->prepare("SELECT id, name, stock, status FROM products WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $productId]);
            $p = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$p) {
                return ['product_id' => $productId, 'stock' => 0, 'purchasable' => false];
            }

            $stock = max(0, (int)($p['stock'] ?? 0));
            $status = $p['status'] ?? 'inactive';
            $purchasable = ($status === 'active' && $stock > 0);

            return [
                'product_id'   => (int)$p['id'],
                'name'         => $p['name'],
                'stock'        => $stock,
                'status'       => $status,
                'purchasable'  => $purchasable,
            ];
        } catch (Exception $e) {
            return ['product_id' => $productId, 'stock' => 0, 'purchasable' => false];
        }
    }

    /**
     * 4. Trừ/Reserve kho an toàn trong PDO Transaction (Atomic + Anti-negative stock)
     */
    public static function reserveOrderInventory(PDO $db, int $orderId): bool
    {
        if ($orderId <= 0) return false;

        $stmt = $db->prepare("SELECT id, status, inventory_status FROM orders WHERE id = :id FOR UPDATE");
        $stmt->execute([':id' => $orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) return false;
        if (($order['inventory_status'] ?? '') === 'reserved') {
            return true; // Đã reserve kho trước đó
        }

        $itemsStmt = $db->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = :order_id");
        $itemsStmt->execute([':order_id' => $orderId]);
        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($items)) return false;

        foreach ($items as $item) {
            $pid = (int)$item['product_id'];
            $qty = max(1, (int)$item['quantity']);

            $pStmt = $db->prepare("SELECT id, stock, status FROM products WHERE id = :id FOR UPDATE");
            $pStmt->execute([':id' => $pid]);
            $product = $pStmt->fetch(PDO::FETCH_ASSOC);

            if (!$product || ($product['status'] ?? '') !== 'active' || (int)$product['stock'] < $qty) {
                throw new RuntimeException("Sản phẩm ID {$pid} không đủ tồn kho hoặc dừng kinh doanh.");
            }

            $updateStmt = $db->prepare("UPDATE products SET stock = stock - :qty WHERE id = :id AND stock >= :qty");
            $updateStmt->execute([':qty' => $qty, ':id' => $pid]);

            if ($updateStmt->rowCount() !== 1) {
                throw new RuntimeException("Lỗi cập nhật kho cho sản phẩm ID {$pid}.");
            }
        }

        $upOrder = $db->prepare("UPDATE orders SET inventory_status = 'reserved', inventory_reserved_at = NOW() WHERE id = :id");
        $upOrder->execute([':id' => $orderId]);

        return true;
    }

    /**
     * 5. Hoàn kho Idempotent (Chỉ hoàn kho 1 lần duy nhất)
     */
    public static function releaseOrderInventory(PDO $db, int $orderId, string $reason = 'cancelled'): bool
    {
        if ($orderId <= 0) return false;

        $stmt = $db->prepare("SELECT id, inventory_status FROM orders WHERE id = :id FOR UPDATE");
        $stmt->execute([':id' => $orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) return false;

        // Nếu trạng thái kho KHÔNG PHẢI là 'reserved' (ví dụ: đã released hoặc chưa reserve), không hoàn kho lại nữa
        if (($order['inventory_status'] ?? '') !== 'reserved') {
            return true;
        }

        $itemsStmt = $db->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = :order_id");
        $itemsStmt->execute([':order_id' => $orderId]);
        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as $item) {
            $pid = (int)$item['product_id'];
            $qty = max(1, (int)$item['quantity']);

            $pStmt = $db->prepare("SELECT id FROM products WHERE id = :id FOR UPDATE");
            $pStmt->execute([':id' => $pid]);

            $upStmt = $db->prepare("UPDATE products SET stock = stock + :qty WHERE id = :id");
            $upStmt->execute([':qty' => $qty, ':id' => $pid]);
        }

        $upOrder = $db->prepare("
            UPDATE orders 
            SET inventory_status = 'released', 
                inventory_released_at = NOW(), 
                inventory_release_reason = :reason 
            WHERE id = :id
        ");
        $upOrder->execute([':reason' => substr($reason, 0, 100), ':id' => $orderId]);

        return true;
    }

    /**
     * 6. Xác nhận chuyển giao kho sang 'committed' khi hoàn thành đơn
     */
    public static function commitOrderInventory(PDO $db, int $orderId): bool
    {
        $stmt = $db->prepare("UPDATE orders SET inventory_status = 'committed' WHERE id = :id AND inventory_status = 'reserved'");
        return $stmt->execute([':id' => $orderId]);
    }

    /**
     * 7. Lấy tổng số lượng đã bán của 1 sản phẩm từ các đơn vị hợp lệ
     */
    public static function getSoldUnits(int $productId, ?PDO $db = null): int
    {
        $db = $db ?? self::getDb();
        if (!$db || $productId <= 0) return 0;

        try {
            $stmt = $db->prepare("
                SELECT COALESCE(SUM(oi.quantity), 0)
                FROM order_items oi
                JOIN orders o ON o.id = oi.order_id
                WHERE oi.product_id = :product_id
                  AND o.status IN ('confirmed', 'processing', 'shipping', 'completed')
            ");
            $stmt->execute([':product_id' => $productId]);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * 8. Thực hiện Nhập kho (+) hoặc Xuất kho (-) với Ghi vết Audit Log 100%
     */
    public static function adjustStock(
        PDO $db,
        int $productId,
        int $quantityChange,
        string $type = 'import',
        ?string $note = null,
        ?int $userId = null
    ): array {
        if ($productId <= 0) {
            throw new InvalidArgumentException("ID sản phẩm không hợp lệ.");
        }
        if ($quantityChange === 0) {
            throw new InvalidArgumentException("Số lượng thay đổi phải khác 0.");
        }

        // Khóa record sản phẩm FOR UPDATE
        $stmt = $db->prepare("SELECT id, name, stock, status FROM products WHERE id = :id FOR UPDATE");
        $stmt->execute([':id' => $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new RuntimeException("Sản phẩm không tồn tại.");
        }

        $oldStock = (int)($product['stock'] ?? 0);
        $newStock = $oldStock + $quantityChange;

        if ($newStock < 0) {
            throw new RuntimeException("Số lượng xuất kho ({$quantityChange}) lớn hơn số tồn kho hiện tại ({$oldStock}).");
        }

        // Cập nhật stock
        $up = $db->prepare("UPDATE products SET stock = :stock WHERE id = :id");
        $up->execute([':stock' => $newStock, ':id' => $productId]);

        // Ghi nhận Audit Log
        self::logInventoryChange($db, $productId, $type, $quantityChange, $oldStock, $newStock, $note, $userId);

        return [
            'success'     => true,
            'product_id'  => $productId,
            'name'        => $product['name'],
            'old_stock'   => $oldStock,
            'new_stock'   => $newStock,
            'change'      => $quantityChange,
            'type'        => $type,
            'note'        => $note
        ];
    }

    /**
     * 9. Ghi vết giao dịch kho vào bảng inventory_logs
     */
    public static function logInventoryChange(
        PDO $db,
        int $productId,
        string $type,
        int $quantity,
        int $oldStock,
        int $newStock,
        ?string $note = null,
        ?int $userId = null
    ): void {
        try {
            $stmt = $db->prepare("
                INSERT INTO inventory_logs (product_id, type, quantity, old_stock, new_stock, note, created_by, created_at)
                VALUES (:product_id, :type, :quantity, :old_stock, :new_stock, :note, :created_by, NOW())
            ");
            $stmt->execute([
                ':product_id' => $productId,
                ':type'       => $type,
                ':quantity'   => $quantity,
                ':old_stock'  => $oldStock,
                ':new_stock'  => $newStock,
                ':note'       => $note,
                ':created_by' => $userId
            ]);
        } catch (Exception $e) {
            error_log("InventoryService::logInventoryChange error: " . $e->getMessage());
        }
    }

    /**
     * 10. Truy vấn Lịch sử Nhập/Xuất kho (Audit Trail)
     */
    public static function getInventoryLogs(?int $productId = null, int $limit = 50, int $offset = 0, ?PDO $db = null): array
    {
        $db = $db ?? self::getDb();
        if (!$db) return [];

        try {
            $sql = "
                SELECT 
                    l.*,
                    p.name AS product_name,
                    p.image AS product_image,
                    u.full_name AS user_name
                FROM inventory_logs l
                LEFT JOIN products p ON p.id = l.product_id
                LEFT JOIN users u ON u.id = l.created_by
            ";

            $params = [];
            if ($productId !== null && $productId > 0) {
                $sql .= " WHERE l.product_id = :pid";
                $params[':pid'] = $productId;
            }

            $sql .= " ORDER BY l.id DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("InventoryService::getInventoryLogs error: " . $e->getMessage());
            return [];
        }
    }
}
