<?php

/**
 * Quản lý hạn mức Flash Sale theo reservation lifecycle.
 * Mọi thao tác thay đổi trạng thái phải nằm trong transaction của caller.
 */
class FlashSaleService
{
    private const ACTIVE_RESERVATION_STATUSES = ['reserved', 'committed'];

    private static function requireTransaction(PDO $db, string $operation): void
    {
        if (!$db->inTransaction()) {
            throw new LogicException("{$operation} phải chạy bên trong database transaction.");
        }
    }

    public static function buyerKey(?int $userId, string $phone): string
    {
        if ($userId !== null && $userId > 0) {
            return 'user:' . $userId;
        }

        $normalizedPhone = preg_replace('/\D+/', '', $phone) ?? '';
        if ($normalizedPhone === '') {
            throw new InvalidArgumentException('Không thể xác định khách vãng lai khi thiếu số điện thoại.');
        }

        return 'guest:' . hash('sha256', $normalizedPhone);
    }

    /**
     * @return array{status:string,item:?array}
     */
    public static function quoteForPurchase(
        PDO $db,
        int $productId,
        int $quantity,
        string $buyerKey
    ): array {
        self::requireTransaction($db, 'Quote hạn mức Flash Sale');
        if ($productId <= 0 || $quantity <= 0 || trim($buyerKey) === '') {
            throw new InvalidArgumentException('Thông tin quote Flash Sale không hợp lệ.');
        }

        $stmt = $db->prepare(
            "SELECT fsi.id, fsi.flash_sale_id, fsi.product_id, fsi.discount_price,
                    fsi.allocation_quantity, fsi.sold_quantity, fsi.limit_per_user,
                    p.price, p.sale_price
             FROM flash_sale_items fsi
             INNER JOIN flash_sales fs ON fs.id = fsi.flash_sale_id
             INNER JOIN products p ON p.id = fsi.product_id
             WHERE fsi.product_id = :product_id
               AND p.status = 'active'
               AND fs.status = 'active'
               AND fs.start_time <= NOW()
               AND fs.end_time > NOW()
               AND fsi.discount_price > 0
               AND fsi.discount_price < CASE
                   WHEN p.sale_price > 0 AND p.sale_price < p.price THEN p.sale_price
                   ELSE p.price
               END
             ORDER BY fsi.discount_price ASC, fsi.id ASC
             FOR UPDATE"
        );
        $stmt->execute([':product_id' => $productId]);
        $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($candidates === []) {
            return ['status' => 'none', 'item' => null];
        }

        $sawCapacity = false;
        $sawLimit = false;
        foreach ($candidates as $candidate) {
            $allocation = (int)$candidate['allocation_quantity'];
            $sold = (int)$candidate['sold_quantity'];
            $limit = (int)$candidate['limit_per_user'];

            if ($allocation <= 0 || $sold < 0 || $sold + $quantity > $allocation) {
                continue;
            }
            $sawCapacity = true;

            $usedByBuyer = self::buyerUsage($db, (int)$candidate['id'], $buyerKey);
            if ($limit <= 0 || $usedByBuyer + $quantity > $limit) {
                $sawLimit = true;
                continue;
            }

            return ['status' => 'eligible', 'item' => $candidate];
        }

        if ($sawLimit) {
            return ['status' => 'limit_reached', 'item' => null];
        }

        return [
            'status' => $sawCapacity ? 'limit_reached' : 'sold_out',
            'item' => null,
        ];
    }

    public static function reserveOrderItem(
        PDO $db,
        int $flashSaleItemId,
        int $orderId,
        int $orderItemId,
        ?int $userId,
        string $buyerKey,
        int $quantity,
        float $unitPrice
    ): bool {
        self::requireTransaction($db, 'Reserve hạn mức Flash Sale');
        if ($flashSaleItemId <= 0 || $orderId <= 0 || $orderItemId <= 0
            || $quantity <= 0 || $unitPrice <= 0 || trim($buyerKey) === '') {
            throw new InvalidArgumentException('Thông tin reserve Flash Sale không hợp lệ.');
        }

        $lineStmt = $db->prepare(
            'SELECT oi.id, oi.order_id, oi.product_id, oi.quantity, oi.price,
                    o.user_id, o.phone
             FROM order_items oi
             INNER JOIN orders o ON o.id = oi.order_id
             WHERE oi.id = :order_item_id AND oi.order_id = :order_id
             LIMIT 1 FOR UPDATE'
        );
        $lineStmt->execute([':order_item_id' => $orderItemId, ':order_id' => $orderId]);
        $line = $lineStmt->fetch(PDO::FETCH_ASSOC);
        if (!$line) {
            throw new RuntimeException('Không tìm thấy order item để reserve Flash Sale.');
        }

        $orderUserId = $line['user_id'] !== null ? (int)$line['user_id'] : null;
        $expectedBuyerKey = self::buyerKey($orderUserId, (string)$line['phone']);
        if ($orderUserId !== $userId || !hash_equals($expectedBuyerKey, $buyerKey)) {
            throw new RuntimeException('Buyer identity không khớp đơn hàng.');
        }
        if ((int)$line['quantity'] !== $quantity || abs((float)$line['price'] - $unitPrice) > 0.001) {
            throw new RuntimeException('Giá hoặc số lượng order item không khớp reservation.');
        }

        $existingStmt = $db->prepare(
            'SELECT * FROM flash_sale_reservations WHERE order_item_id = :order_item_id LIMIT 1 FOR UPDATE'
        );
        $existingStmt->execute([':order_item_id' => $orderItemId]);
        $existing = $existingStmt->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            self::assertSameReservation(
                $existing,
                $flashSaleItemId,
                $orderId,
                $orderItemId,
                $userId,
                $buyerKey,
                $quantity,
                $unitPrice
            );
            if (in_array((string)$existing['status'], self::ACTIVE_RESERVATION_STATUSES, true)) {
                return true;
            }
            throw new RuntimeException('Không thể reserve lại order item đã release quota.');
        }

        $itemStmt = $db->prepare(
            "SELECT fsi.id, fsi.product_id, fsi.discount_price, fsi.allocation_quantity,
                    fsi.sold_quantity, fsi.limit_per_user, p.price, p.sale_price
             FROM flash_sale_items fsi
             INNER JOIN flash_sales fs ON fs.id = fsi.flash_sale_id
             INNER JOIN products p ON p.id = fsi.product_id
             WHERE fsi.id = :id
               AND p.status = 'active'
               AND fs.status = 'active'
               AND fs.start_time <= NOW()
               AND fs.end_time > NOW()
             LIMIT 1 FOR UPDATE"
        );
        $itemStmt->execute([':id' => $flashSaleItemId]);
        $item = $itemStmt->fetch(PDO::FETCH_ASSOC);
        if (!$item || (int)$item['product_id'] !== (int)$line['product_id']) {
            throw new RuntimeException('Flash Sale item không còn hợp lệ cho sản phẩm trong đơn.');
        }

        $basePrice = (float)$item['price'];
        $salePrice = (float)($item['sale_price'] ?? 0);
        $regularPrice = ($salePrice > 0 && $salePrice < $basePrice) ? $salePrice : $basePrice;
        $discountPrice = (float)$item['discount_price'];
        if ($discountPrice <= 0 || $discountPrice >= $regularPrice
            || abs($discountPrice - $unitPrice) > 0.001) {
            throw new RuntimeException('Giá Flash Sale không còn khớp giá đã dùng cho đơn hàng.');
        }

        $allocation = (int)$item['allocation_quantity'];
        $sold = (int)$item['sold_quantity'];
        $limit = (int)$item['limit_per_user'];
        if ($allocation <= 0 || $sold < 0 || $sold + $quantity > $allocation) {
            throw new RuntimeException('Flash Sale vừa hết hạn mức.');
        }
        if ($limit <= 0 || self::buyerUsage($db, $flashSaleItemId, $buyerKey) + $quantity > $limit) {
            throw new RuntimeException('Bạn đã đạt giới hạn mua của Flash Sale này.');
        }

        $consumeStmt = $db->prepare(
            'UPDATE flash_sale_items
             SET sold_quantity = sold_quantity + :quantity
             WHERE id = :id
               AND sold_quantity + :quantity_guard <= allocation_quantity'
        );
        $consumeStmt->execute([
            ':quantity' => $quantity,
            ':id' => $flashSaleItemId,
            ':quantity_guard' => $quantity,
        ]);
        if ($consumeStmt->rowCount() !== 1) {
            throw new RuntimeException('Không thể giữ hạn mức Flash Sale; vui lòng tải lại giỏ hàng.');
        }

        $insertStmt = $db->prepare(
            "INSERT INTO flash_sale_reservations
                (flash_sale_item_id, order_id, order_item_id, user_id, buyer_key,
                 quantity, unit_price, status, reserved_at)
             VALUES
                (:flash_sale_item_id, :order_id, :order_item_id, :user_id, :buyer_key,
                 :quantity, :unit_price, 'reserved', NOW())"
        );
        $insertStmt->execute([
            ':flash_sale_item_id' => $flashSaleItemId,
            ':order_id' => $orderId,
            ':order_item_id' => $orderItemId,
            ':user_id' => $userId,
            ':buyer_key' => $buyerKey,
            ':quantity' => $quantity,
            ':unit_price' => $unitPrice,
        ]);

        return true;
    }

    public static function commitOrderReservations(PDO $db, int $orderId): bool
    {
        self::requireTransaction($db, 'Commit hạn mức Flash Sale');
        self::lockOrder($db, $orderId);

        $stmt = $db->prepare(
            'SELECT id, status FROM flash_sale_reservations
             WHERE order_id = :order_id
             ORDER BY id ASC FOR UPDATE'
        );
        $stmt->execute([':order_id' => $orderId]);
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($reservations as $reservation) {
            $status = (string)$reservation['status'];
            if ($status === 'committed') {
                continue;
            }
            if ($status === 'released') {
                throw new RuntimeException('Không thể commit reservation đã release.');
            }

            $update = $db->prepare(
                "UPDATE flash_sale_reservations
                 SET status = 'committed', committed_at = NOW()
                 WHERE id = :id AND status = 'reserved'"
            );
            $update->execute([':id' => $reservation['id']]);
            if ($update->rowCount() !== 1) {
                throw new RuntimeException('Không thể commit Flash Sale reservation.');
            }
        }

        return true;
    }

    public static function releaseOrderReservations(PDO $db, int $orderId, string $reason): bool
    {
        self::requireTransaction($db, 'Release hạn mức Flash Sale');
        self::lockOrder($db, $orderId);

        $stmt = $db->prepare(
            'SELECT id, flash_sale_item_id, quantity, status
             FROM flash_sale_reservations
             WHERE order_id = :order_id
             ORDER BY flash_sale_item_id ASC, id ASC
             FOR UPDATE'
        );
        $stmt->execute([':order_id' => $orderId]);
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($reservations as $reservation) {
            $status = (string)$reservation['status'];
            if ($status === 'released' || $status === 'committed') {
                continue;
            }

            $itemStmt = $db->prepare(
                'SELECT id, sold_quantity FROM flash_sale_items WHERE id = :id FOR UPDATE'
            );
            $itemStmt->execute([':id' => $reservation['flash_sale_item_id']]);
            $item = $itemStmt->fetch(PDO::FETCH_ASSOC);
            $quantity = (int)$reservation['quantity'];
            if (!$item || (int)$item['sold_quantity'] < $quantity) {
                throw new RuntimeException('Không thể hoàn hạn mức vì bộ đếm Flash Sale bị lệch.');
            }

            $restore = $db->prepare(
                'UPDATE flash_sale_items
                 SET sold_quantity = sold_quantity - :quantity
                 WHERE id = :id AND sold_quantity >= :quantity_guard'
            );
            $restore->execute([
                ':quantity' => $quantity,
                ':id' => $reservation['flash_sale_item_id'],
                ':quantity_guard' => $quantity,
            ]);
            if ($restore->rowCount() !== 1) {
                throw new RuntimeException('Không thể hoàn hạn mức Flash Sale.');
            }

            $update = $db->prepare(
                "UPDATE flash_sale_reservations
                 SET status = 'released', released_at = NOW(), release_reason = :reason
                 WHERE id = :id AND status = 'reserved'"
            );
            $update->execute([
                ':reason' => substr(trim($reason), 0, 100),
                ':id' => $reservation['id'],
            ]);
            if ($update->rowCount() !== 1) {
                throw new RuntimeException('Không thể đánh dấu Flash Sale reservation đã release.');
            }
        }

        return true;
    }

    /**
     * Đối chiếu bộ đếm nhanh trên flash_sale_items với sổ reservation.
     * Hàm này chỉ phát hiện drift để cảnh báo; không tự sửa dữ liệu.
     *
     * @return array<int, array<string, int|bool|string>>
     */
    public static function auditQuotaCounters(PDO $db, ?int $flashSaleId = null): array
    {
        if ($flashSaleId !== null && $flashSaleId <= 0) {
            throw new InvalidArgumentException('Flash Sale ID không hợp lệ.');
        }

        $where = '';
        $params = [];
        if ($flashSaleId !== null) {
            $where = 'WHERE fsi.flash_sale_id = :flash_sale_id';
            $params[':flash_sale_id'] = $flashSaleId;
        }

        $stmt = $db->prepare(
            "SELECT fsi.id AS flash_sale_item_id,
                    fsi.flash_sale_id,
                    fsi.product_id,
                    fsi.allocation_quantity,
                    fsi.sold_quantity,
                    COALESCE(SUM(CASE
                        WHEN fsr.status IN ('reserved', 'committed') THEN fsr.quantity
                        ELSE 0
                    END), 0) AS ledger_quantity
             FROM flash_sale_items fsi
             LEFT JOIN flash_sale_reservations fsr
               ON fsr.flash_sale_item_id = fsi.id
             {$where}
             GROUP BY fsi.id, fsi.flash_sale_id, fsi.product_id,
                      fsi.allocation_quantity, fsi.sold_quantity
             ORDER BY fsi.flash_sale_id ASC, fsi.id ASC"
        );
        $stmt->execute($params);

        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $sold = (int)$row['sold_quantity'];
            $ledger = (int)$row['ledger_quantity'];
            $result[] = [
                'flash_sale_item_id' => (int)$row['flash_sale_item_id'],
                'flash_sale_id' => (int)$row['flash_sale_id'],
                'product_id' => (int)$row['product_id'],
                'allocation_quantity' => (int)$row['allocation_quantity'],
                'sold_quantity' => $sold,
                'ledger_quantity' => $ledger,
                'difference' => $sold - $ledger,
                'is_consistent' => $sold === $ledger,
            ];
        }

        return $result;
    }

    /**
     * Không cho xóa item đã có lịch sử reservation, kể cả đã release.
     * Nhờ đó audit trail và khóa ngoại không bị phá vỡ.
     */
    public static function assertItemRemovable(PDO $db, int $flashSaleItemId): void
    {
        self::requireTransaction($db, 'Kiểm tra xóa Flash Sale item');
        if ($flashSaleItemId <= 0) {
            throw new InvalidArgumentException('Flash Sale item ID không hợp lệ.');
        }

        $itemStmt = $db->prepare(
            'SELECT id, sold_quantity FROM flash_sale_items WHERE id = :id LIMIT 1 FOR UPDATE'
        );
        $itemStmt->execute([':id' => $flashSaleItemId]);
        $item = $itemStmt->fetch(PDO::FETCH_ASSOC);
        if (!$item) {
            throw new RuntimeException('Flash Sale item không tồn tại.');
        }
        if ((int)$item['sold_quantity'] !== 0) {
            throw new RuntimeException(
                'Không thể xóa sản phẩm Flash Sale đang có quota đã giữ/bán; hãy xử lý drift hoặc dừng chiến dịch.'
            );
        }

        $reservationStmt = $db->prepare(
            'SELECT id FROM flash_sale_reservations
             WHERE flash_sale_item_id = :flash_sale_item_id
             ORDER BY id ASC LIMIT 1 FOR UPDATE'
        );
        $reservationStmt->execute([':flash_sale_item_id' => $flashSaleItemId]);
        if ($reservationStmt->fetchColumn()) {
            throw new RuntimeException(
                'Không thể xóa sản phẩm Flash Sale đã có lịch sử giữ suất; hãy dừng chiến dịch thay vì xóa.'
            );
        }
    }

    private static function buyerUsage(PDO $db, int $flashSaleItemId, string $buyerKey): int
    {
        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(quantity), 0)
             FROM flash_sale_reservations
             WHERE flash_sale_item_id = :flash_sale_item_id
               AND buyer_key = :buyer_key
               AND status IN ('reserved', 'committed')"
        );
        $stmt->execute([
            ':flash_sale_item_id' => $flashSaleItemId,
            ':buyer_key' => $buyerKey,
        ]);
        return (int)$stmt->fetchColumn();
    }

    private static function lockOrder(PDO $db, int $orderId): void
    {
        if ($orderId <= 0) {
            throw new InvalidArgumentException('Order ID không hợp lệ.');
        }

        $stmt = $db->prepare('SELECT id FROM orders WHERE id = :id FOR UPDATE');
        $stmt->execute([':id' => $orderId]);
        if (!$stmt->fetchColumn()) {
            throw new RuntimeException('Không tìm thấy đơn hàng để xử lý Flash Sale reservation.');
        }
    }

    private static function assertSameReservation(
        array $reservation,
        int $flashSaleItemId,
        int $orderId,
        int $orderItemId,
        ?int $userId,
        string $buyerKey,
        int $quantity,
        float $unitPrice
    ): void {
        $storedUserId = $reservation['user_id'] !== null ? (int)$reservation['user_id'] : null;
        $matches = (int)$reservation['flash_sale_item_id'] === $flashSaleItemId
            && (int)$reservation['order_id'] === $orderId
            && (int)$reservation['order_item_id'] === $orderItemId
            && $storedUserId === $userId
            && hash_equals((string)$reservation['buyer_key'], $buyerKey)
            && (int)$reservation['quantity'] === $quantity
            && abs((float)$reservation['unit_price'] - $unitPrice) <= 0.001;

        if (!$matches) {
            throw new RuntimeException('Reservation hiện có không khớp payload idempotent.');
        }
    }
}
