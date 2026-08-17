<?php
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/core/helpers.php';

class Order
{
    private ?PDO $db;
    private bool $useFallback;
    private string $lastError = '';

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->useFallback = $this->db === null;
    }

    public function getLastError(): string
    {
        return $this->lastError;
    }

    public function create(array $payload): array|false
    {
        $orderCode = 'TP-' . date('YmdHis') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        if ($this->useFallback) {
            return false;
        }

        $this->db->beginTransaction();

        try {
            $userId = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
            $requestedCouponId = isset($payload['coupon_id']) ? (int)$payload['coupon_id'] : 0;

            // Chuẩn hóa số lượng theo product_id để payload trùng ID không tạo nhiều dòng ngoài ý muốn.
            $quantities = [];
            foreach ($payload['items'] ?? [] as $item) {
                $productId = (int)($item['product_id'] ?? 0);
                if ($productId <= 0) {
                    throw new RuntimeException('Sản phẩm trong giỏ hàng không hợp lệ.');
                }
                $quantities[$productId] = ($quantities[$productId] ?? 0)
                    + max(1, (int)($item['quantity'] ?? 1));
            }

            if ($quantities === []) {
                throw new RuntimeException('Giỏ hàng không có sản phẩm hợp lệ.');
            }
            ksort($quantities, SORT_NUMERIC);

            require_once ROOT_PATH . '/app/services/FlashSaleService.php';
            $buyerKey = FlashSaleService::buyerKey(
                $userId,
                (string)($payload['phone'] ?? '')
            );

            $productCheckStmt = $this->db->prepare(
                "SELECT p.id, p.name, p.slug, p.image, p.price, p.sale_price, p.stock
                 FROM products p
                 WHERE p.id = :id AND p.status = 'active'
                 FOR UPDATE"
            );

            $resolvedItems = [];
            $calculatedSubtotal = 0.0;
            foreach ($quantities as $productId => $qty) {
                $productCheckStmt->execute([':id' => $productId]);
                $dbProduct = $productCheckStmt->fetch(PDO::FETCH_ASSOC);

                if (!$dbProduct) {
                    throw new RuntimeException('Sản phẩm không tồn tại hoặc đã ngừng bán.');
                }
                if ($qty > (int)$dbProduct['stock']) {
                    throw new RuntimeException('Sản phẩm ' . $dbProduct['name'] . ' không đủ tồn kho.');
                }

                // getEffectiveProductData($dbProduct)
                $flashQuote = FlashSaleService::quoteForPurchase(
                    $this->db,
                    (int)$productId,
                    $qty,
                    $buyerKey
                );
                $flashQuoteStatus = (string)($flashQuote['status'] ?? 'none');

                $flashItem = ($flashQuoteStatus === 'eligible' && is_array($flashQuote['item'] ?? null))
                    ? $flashQuote['item']
                    : null;

                $flashQty = ($flashItem !== null) ? min($qty, max(0, (int)($flashQuote['flash_qty'] ?? 0))) : 0;
                $regularQty = $qty - $flashQty;

                $basePrice = (float)$dbProduct['price'];
                $salePrice = (float)($dbProduct['sale_price'] ?? 0);
                $regularPrice = ($salePrice > 0 && $salePrice < $basePrice) ? $salePrice : $basePrice;

                if ($flashItem !== null && $flashQty > 0) {
                    $flashPrice = (float)$flashItem['discount_price'];
                    $lineTotal = ($flashQty * $flashPrice) + ($regularQty * $regularPrice);
                } else {
                    $lineTotal = $qty * $regularPrice;
                    $flashPrice = $regularPrice;
                }

                $unitPrice = $lineTotal / $qty;
                $calculatedSubtotal += $lineTotal;

                $resolvedItems[] = [
                    'product_id' => (int)$productId,
                    'name' => (string)$dbProduct['name'],
                    'product_name' => (string)$dbProduct['name'],
                    'slug' => (string)($dbProduct['slug'] ?? ''),
                    'image' => (string)($dbProduct['image'] ?? ''),
                    'price' => $unitPrice,
                    'quantity' => $qty,
                    'line_total' => $lineTotal,
                    'price_source' => ($flashItem !== null && $flashQty > 0) ? 'flash' : 'regular',
                    'flash_sale_item_id' => ($flashItem !== null && $flashQty > 0) ? (int)$flashItem['id'] : null,
                    'flash_qty' => $flashQty,
                    'flash_unit_price' => $flashPrice,
                ];
            }

            // Coupon cũng được đọc và khóa lại trong cùng transaction; không tin discount từ session.
            $couponId = null;
            $discountAmount = 0.0;
            if ($requestedCouponId > 0) {
                $couponStmt = $this->db->prepare(
                    "SELECT * FROM coupons
                     WHERE id = :id
                       AND status = 'active'
                       AND start_date <= NOW()
                       AND end_date >= NOW()
                     LIMIT 1 FOR UPDATE"
                );
                $couponStmt->execute([':id' => $requestedCouponId]);
                $coupon = $couponStmt->fetch(PDO::FETCH_ASSOC);

                $hasGlobalUsage = $coupon
                    && ($coupon['usage_limit'] === null || (int)$coupon['used_count'] < (int)$coupon['usage_limit']);
                $hasUserUsage = true;
                if ($coupon && $userId) {
                    $usageStmt = $this->db->prepare(
                        "SELECT COUNT(*) FROM orders
                         WHERE user_id = :user_id AND coupon_id = :coupon_id AND status != 'cancelled'"
                    );
                    $usageStmt->execute([':user_id' => $userId, ':coupon_id' => $requestedCouponId]);
                    $maxPerUser = $coupon['usage_limit_per_user'] !== null
                        ? (int)$coupon['usage_limit_per_user']
                        : 1;
                    $hasUserUsage = (int)$usageStmt->fetchColumn() < $maxPerUser;
                }

                if ($coupon && $hasGlobalUsage && $hasUserUsage
                    && $calculatedSubtotal >= (float)$coupon['min_order_value']) {
                    $discountAmount = calculateCouponDiscount($coupon, $calculatedSubtotal);
                    if ($discountAmount > 0) {
                        $couponId = $requestedCouponId;
                    }
                }
            }

            $shippingFee = shippingFee($calculatedSubtotal);
            $calculatedTotal = max(0.0, $calculatedSubtotal - $discountAmount + $shippingFee);

            $expectedTotal = (float)($payload['expected_total_amount'] ?? $payload['expected_total'] ?? 0);
            if ($expectedTotal > 0 && abs($calculatedTotal - $expectedTotal) > 0.01) {
                throw new RuntimeException(
                    '⚡ <strong>Hạn mức Flash Sale vừa thay đổi!</strong> Do có khách hàng khác vừa chốt đơn trước, số suất ưu đãi sản phẩm trong giỏ của bạn vừa cập nhật lại.<br>'
                    . '• Giá tổng đơn hàng mới: <strong>' . formatPrice($calculatedTotal) . '</strong>.<br>'
                    . '👉 Nếu bạn đồng ý với mức giá mới, hãy kiểm tra lại và bấm <strong>[Xác nhận đặt hàng ngay]</strong> bên dưới. Hoặc <a href="' . url('cart') . '" style="display: inline-block; padding: 2px 10px; background: #ffffff; color: #2563eb; font-weight: 700; border-radius: 4px; text-decoration: none; margin-left: 4px; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">Quay lại Giỏ hàng</a> để điều chỉnh lại số lượng.'
                );
            }

            $stmt = $this->db->prepare(
                "INSERT INTO orders (order_code, user_id, coupon_id, customer_name, phone, address, note, payment_method, payment_status, subtotal, discount_amount, shipping_fee, total_amount, status, inventory_status) VALUES (:order_code, :user_id, :coupon_id, :customer_name, :phone, :address, :note, :payment_method, :payment_status, :subtotal, :discount_amount, :shipping_fee, :total_amount, :status, :inventory_status)"
            );

            $stmt->execute([
                ':order_code' => $orderCode,
                ':user_id' => $userId,
                ':coupon_id' => $couponId,
                ':customer_name' => $payload['customer_name'] ?? '',
                ':phone' => $payload['phone'] ?? '',
                ':address' => $payload['address'] ?? '',
                ':note' => $payload['note'] ?? '',
                ':payment_method' => $payload['payment_method'] ?? 'COD',
                ':payment_status' => $payload['payment_status'] ?? 'unpaid',
                ':subtotal' => $calculatedSubtotal,
                ':discount_amount' => $discountAmount,
                ':shipping_fee' => $shippingFee,
                ':total_amount' => $calculatedTotal,
                ':status' => 'pending',
                ':inventory_status' => 'not_reserved',
            ]);

            $orderId = (int)$this->db->lastInsertId();

            if ($orderId <= 0) {
                throw new RuntimeException('Không thể lấy ID đơn hàng vừa tạo.');
            }

            // Cập nhật tăng lượt dùng cho coupon
            if ($couponId) {
                $couponUpdateStmt = $this->db->prepare(
                    'UPDATE coupons
                     SET used_count = used_count + 1
                     WHERE id = :coupon_id
                       AND (usage_limit IS NULL OR used_count < usage_limit)'
                );
                $couponUpdateStmt->execute([':coupon_id' => $couponId]);
                if ($couponUpdateStmt->rowCount() !== 1) {
                    throw new RuntimeException('Mã giảm giá vừa hết lượt sử dụng.');
                }
            }

            $itemStmt = $this->db->prepare(
                'INSERT INTO order_items (order_id, product_id, product_name, price, quantity, line_total)
                 VALUES (:order_id, :product_id, :product_name, :price, :quantity, :line_total)'
            );

            foreach ($resolvedItems as $item) {
                $itemStmt->execute([
                    ':order_id' => $orderId,
                    ':product_id' => $item['product_id'],
                    ':product_name' => $item['product_name'],
                    ':price' => $item['price'],
                    ':quantity' => $item['quantity'],
                    ':line_total' => $item['line_total'],
                ]);

                $orderItemId = (int)$this->db->lastInsertId();
                if ($orderItemId <= 0) {
                    throw new RuntimeException('Không thể lấy ID chi tiết đơn hàng vừa tạo.');
                }

                if (!empty($item['flash_sale_item_id']) && !empty($item['flash_qty'])) {
                    FlashSaleService::reserveOrderItem(
                        $this->db,
                        (int)$item['flash_sale_item_id'],
                        $orderId,
                        $orderItemId,
                        $userId,
                        $buyerKey,
                        (int)$item['flash_qty'],
                        (float)$item['flash_unit_price']
                    );
                }
            }

            // 3. Gọi Trừ tồn kho và Ghi vết Audit Log nguyên tử qua InventoryService
            require_once ROOT_PATH . '/app/services/InventoryService.php';
            if (!InventoryService::reserveOrderInventory($this->db, $orderId)) {
                throw new RuntimeException('Không thể giữ tồn kho cho đơn hàng.');
            }

            // Xóa/đóng giỏ hàng active của user trong database
            if ($userId) {
                $cartStmt = $this->db->prepare("SELECT id FROM carts WHERE user_id = :user_id AND status = 'active' LIMIT 1");
                $cartStmt->execute([':user_id' => $userId]);
                $cart = $cartStmt->fetch();
                if ($cart) {
                    $cartId = (int)$cart['id'];
                    // Chuyển status giỏ hàng sang converted
                    $updateCartStmt = $this->db->prepare("UPDATE carts SET status = 'converted' WHERE id = :cart_id");
                    $updateCartStmt->execute([':cart_id' => $cartId]);
                    // Xóa sạch các mặt hàng trong giỏ
                    $clearItemsStmt = $this->db->prepare("DELETE FROM cart_items WHERE cart_id = :cart_id");
                    $clearItemsStmt->execute([':cart_id' => $cartId]);
                }
            }

            // Gửi thông báo cho Admin khi có đơn hàng mới
            try {
                $adminNotif = $this->db->prepare(
                    'INSERT INTO notifications (user_id, title, content) VALUES (1, :title, :content)'
                );
                $adminNotif->execute([
                    ':title' => 'Đơn hàng mới #' . $orderCode,
                    ':content' => 'Khách hàng ' . ($payload['customer_name'] ?? 'Khách') . ' vừa đặt đơn hàng #' . $orderCode . ' tổng trị giá ' . number_format($calculatedTotal, 0, ',', '.') . 'đ',
                ]);
            } catch (Throwable $e) {}

            // Gửi thông báo cho Customer xác nhận đơn hàng
            if ($userId > 0) {
                try {
                    $custNotif = $this->db->prepare(
                        'INSERT INTO notifications (user_id, title, content) VALUES (:uid, :title, :content)'
                    );
                    $custNotif->execute([
                        ':uid' => $userId,
                        ':title' => 'Đặt hàng thành công #' . $orderCode,
                        ':content' => 'Đơn hàng #' . $orderCode . ' của bạn đã được tiếp nhận. Tổng trị giá: ' . number_format($calculatedTotal, 0, ',', '.') . 'đ. Chúng tôi sẽ xử lý trong thời gian sớm nhất.',
                    ]);
                } catch (Throwable $e) {}
            }

            $this->db->commit();

            return [
                'id' => $orderId,
                'order_code' => $orderCode,
                'customer_name' => $payload['customer_name'] ?? '',
                'phone' => $payload['phone'] ?? '',
                'address' => $payload['address'] ?? '',
                'note' => $payload['note'] ?? '',
                'payment_method' => $payload['payment_method'] ?? 'COD',
                'status' => 'pending',
                'subtotal' => $calculatedSubtotal,
                'discount_amount' => $discountAmount,
                'shipping_fee' => $shippingFee,
                'total_amount' => $calculatedTotal,
                'items' => $resolvedItems,
            ];
        } catch (RuntimeException $e) {
            // Expected business validation / domain-level failures: rollback and return false.
            if ($this->db && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->lastError = $e->getMessage();
            error_log('Order::create: ' . $e->getMessage());
            return false;
        } catch (Throwable $e) {
            // Unexpected errors: rollback and rethrow so CI can surface programming bugs.
            if ($this->db && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Order::create unexpected error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            throw $e;
        }
    }

    public function getByUserId(int $userId): array
    {
        if ($this->useFallback) {
            return [];
        }

        $stmt = $this->db->prepare('SELECT * FROM orders WHERE user_id = :user_id ORDER BY id DESC');
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByCode(string $orderCode): array|false
    {
        if ($this->useFallback) return false;
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE order_code = :code LIMIT 1');
        $stmt->execute([':code' => $orderCode]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePayment(string $orderCode, string $status): bool
    {
        if ($this->useFallback || !in_array($status, ['pending', 'paid', 'failed'], true)) return false;

        require_once ROOT_PATH . '/app/services/InventoryService.php';
        require_once ROOT_PATH . '/app/services/FlashSaleService.php';

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT id, payment_status, status, inventory_status FROM orders WHERE order_code = :code AND payment_method = 'VNPAY' FOR UPDATE");
            $stmt->execute([':code' => $orderCode]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                $this->db->rollBack();
                return false;
            }
            if (($order['payment_status'] ?? '') === 'paid') {
                // Callback lặp lại phải vừa idempotent, vừa tự chữa trường hợp
                // payment đã paid nhưng quota Flash Sale chưa kịp commit.
                FlashSaleService::commitOrderReservations($this->db, (int)$order['id']);
                $this->db->commit();
                return true;
            }

            if ($status === 'paid') {
                FlashSaleService::commitOrderReservations($this->db, (int)$order['id']);
                $up = $this->db->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = :id");
                $up->execute([':id' => $order['id']]);
                $this->db->commit();
                return true;
            } elseif ($status === 'failed') {
                require_once ROOT_PATH . '/app/services/CouponService.php';
                CouponService::releaseOrderCoupon($this->db, (int)$order['id']);

                if (($order['inventory_status'] ?? '') === 'reserved') {
                    InventoryService::releaseOrderInventory($this->db, (int)$order['id'], 'vnpay_failed');
                }
                FlashSaleService::releaseOrderReservations($this->db, (int)$order['id'], 'vnpay_failed');
                $up = $this->db->prepare("UPDATE orders SET payment_status = 'failed', status = 'cancelled' WHERE id = :id");
                $up->execute([':id' => $order['id']]);
                $this->db->commit();
                return true;
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function getById(int $id, int $userId): array|false
    {
        if ($this->useFallback) {
            return false;
        }

        // Chặn IDOR: luôn lọc theo cả id và user_id của chủ sở hữu đơn hàng
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE id = :id AND user_id = :user_id LIMIT 1');
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return false;
        }

        // Lấy chi tiết sản phẩm của đơn hàng
        $itemStmt = $this->db->prepare('SELECT oi.*, p.image FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = :order_id');
        $itemStmt->execute([':order_id' => $id]);
        $order['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

        return $order;
    }
}
