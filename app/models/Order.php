<?php
require_once ROOT_PATH . '/config/database.php';

class Order
{
    private ?PDO $db;
    private bool $useFallback;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->useFallback = $this->db === null;
    }

    public function create(array $payload): array|false
    {
        $orderCode = 'TP-' . date('YmdHis') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

        if ($this->useFallback) {
            return [
                'id' => 0,
                'order_code' => $orderCode,
                'customer_name' => $payload['customer_name'] ?? '',
                'phone' => $payload['phone'] ?? '',
                'address' => $payload['address'] ?? '',
                'note' => $payload['note'] ?? '',
                'payment_method' => $payload['payment_method'] ?? 'COD',
                'status' => 'pending',
                'subtotal' => (float)($payload['subtotal'] ?? 0),
                'shipping_fee' => (float)($payload['shipping_fee'] ?? 0),
                'total_amount' => (float)($payload['total_amount'] ?? 0),
            ];
        }

        $this->db->beginTransaction();

        try {
            $userId = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
            $couponId = isset($payload['coupon_id']) ? (int)$payload['coupon_id'] : null;
            $discountAmount = isset($payload['discount_amount']) ? (float)$payload['discount_amount'] : 0.0;

            $stmt = $this->db->prepare(
                "INSERT INTO orders (order_code, user_id, coupon_id, customer_name, phone, address, note, payment_method, payment_status, subtotal, discount_amount, shipping_fee, total_amount, status, inventory_status, inventory_reserved_at)
                 VALUES (:order_code, :user_id, :coupon_id, :customer_name, :phone, :address, :note, :payment_method, :payment_status, :subtotal, :discount_amount, :shipping_fee, :total_amount, :status, 'pending', NOW())"
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
                ':subtotal' => (float)($payload['subtotal'] ?? 0),
                ':discount_amount' => $discountAmount,
                ':shipping_fee' => (float)($payload['shipping_fee'] ?? 0),
                ':total_amount' => (float)($payload['total_amount'] ?? 0),
                ':status' => 'pending',
            ]);

            $orderId = (int)$this->db->lastInsertId();

            if ($orderId <= 0) {
                throw new RuntimeException('Không thể lấy ID đơn hàng vừa tạo.');
            }

            // Cập nhật tăng lượt dùng cho coupon
            if ($couponId) {
                $couponUpdateStmt = $this->db->prepare(
                    'UPDATE coupons SET used_count = used_count + 1 WHERE id = :coupon_id'
                );
                $couponUpdateStmt->execute([':coupon_id' => $couponId]);
            }

            $productCheckStmt = $this->db->prepare(
                'SELECT name, price, stock FROM products WHERE id = :id FOR UPDATE'
            );

            $itemStmt = $this->db->prepare(
                'INSERT INTO order_items (order_id, product_id, product_name, price, quantity, line_total)
                 VALUES (:order_id, :product_id, :product_name, :price, :quantity, :line_total)'
            );

            foreach ($payload['items'] ?? [] as $item) {
                $productId = (int)($item['product_id'] ?? 0);
                $qty = max(1, (int)($item['quantity'] ?? 1));

                // 1. Khóa và lấy thông tin giá thực tế từ Database
                $productCheckStmt->execute([':id' => $productId]);
                $dbProduct = $productCheckStmt->fetch();

                if (!$dbProduct) {
                    throw new Exception('Sản phẩm không tồn tại.');
                }

                $dbPrice = (float)$dbProduct['price'];

                // Kiểm tra giá ưu đãi Flash Sale đang active nếu có
                $fsStmt = $this->db->prepare(
                    "SELECT fsi.discount_price 
                     FROM flash_sale_items fsi
                     JOIN flash_sales fs ON fsi.flash_sale_id = fs.id
                     WHERE fsi.product_id = :product_id 
                       AND fs.status = 'active'
                       AND fs.start_time <= NOW()
                       AND fs.end_time > NOW()
                     LIMIT 1"
                );
                $fsStmt->execute([':product_id' => $productId]);
                $fsItem = $fsStmt->fetch(PDO::FETCH_ASSOC);
                if ($fsItem && !empty($fsItem['discount_price']) && (float)$fsItem['discount_price'] > 0) {
                    $dbPrice = (float)$fsItem['discount_price'];
                }
                $dbName = $dbProduct['name'];

                // 2. Ghi chi tiết đơn hàng
                $itemStmt->execute([
                    ':order_id' => $orderId,
                    ':product_id' => $productId,
                    ':product_name' => $dbName,
                    ':price' => $dbPrice,
                    ':quantity' => $qty,
                    ':line_total' => $dbPrice * $qty,
                ]);
            }

            // 3. Gọi Trừ tồn kho và Ghi vết Audit Log nguyên tử qua InventoryService
            require_once ROOT_PATH . '/app/services/InventoryService.php';
            InventoryService::reserveOrderInventory($this->db, $orderId);

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
                    ':content' => 'Khách hàng ' . ($payload['customer_name'] ?? 'Khách') . ' vừa đặt đơn hàng #' . $orderCode . ' tổng trị giá ' . number_format((float)($payload['total_amount'] ?? 0), 0, ',', '.') . 'đ.'
                ]);
            } catch (Throwable $e) {}

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
                'subtotal' => (float)($payload['subtotal'] ?? 0),
                'shipping_fee' => (float)($payload['shipping_fee'] ?? 0),
                'total_amount' => (float)($payload['total_amount'] ?? 0),
            ];
        } catch (Throwable $e) {
            error_log('Order::create failed: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            if ($this->db && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
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
                $this->db->commit();
                return true;
            }

            if ($status === 'paid') {
                $up = $this->db->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = :id");
                $up->execute([':id' => $order['id']]);
                $this->db->commit();
                return true;
            } elseif ($status === 'failed') {
                if (($order['inventory_status'] ?? '') === 'reserved') {
                    InventoryService::releaseOrderInventory($this->db, (int)$order['id'], 'vnpay_failed');
                }
                $up = $this->db->prepare("UPDATE orders SET payment_status = 'failed', status = 'cancelled' WHERE id = :id");
                $up->execute([':id' => $order['id']]);
                $this->db->commit();
                return true;
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
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
