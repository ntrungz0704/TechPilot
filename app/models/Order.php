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
            $stmt = $this->db->prepare(
                'INSERT INTO orders (order_code, user_id, customer_name, phone, address, note, payment_method, subtotal, shipping_fee, total_amount, status)
                 VALUES (:order_code, :user_id, :customer_name, :phone, :address, :note, :payment_method, :subtotal, :shipping_fee, :total_amount, :status)'
            );

            $stmt->execute([
                ':order_code' => $orderCode,
                ':user_id' => $userId,
                ':customer_name' => $payload['customer_name'] ?? '',
                ':phone' => $payload['phone'] ?? '',
                ':address' => $payload['address'] ?? '',
                ':note' => $payload['note'] ?? '',
                ':payment_method' => $payload['payment_method'] ?? 'COD',
                ':subtotal' => (float)($payload['subtotal'] ?? 0),
                ':shipping_fee' => (float)($payload['shipping_fee'] ?? 0),
                ':total_amount' => (float)($payload['total_amount'] ?? 0),
                ':status' => 'pending',
            ]);

            $orderId = (int)$this->db->lastInsertId();

            $productCheckStmt = $this->db->prepare(
                'SELECT name, price, stock FROM products WHERE id = :id FOR UPDATE'
            );

            $updateStockStmt = $this->db->prepare(
                'UPDATE products SET stock = stock - :qty WHERE id = :id'
            );

            $itemStmt = $this->db->prepare(
                'INSERT INTO order_items (order_id, product_id, product_name, price, quantity, line_total)
                 VALUES (:order_id, :product_id, :product_name, :price, :quantity, :line_total)'
            );

            foreach ($payload['items'] ?? [] as $item) {
                $productId = (int)($item['product_id'] ?? 0);
                $qty = max(1, (int)($item['quantity'] ?? 1));

                // 1. Khóa và lấy thông tin tồn kho & giá thực tế từ Database
                $productCheckStmt->execute([':id' => $productId]);
                $dbProduct = $productCheckStmt->fetch();

                if (!$dbProduct) {
                    throw new Exception('Sản phẩm không tồn tại.');
                }

                $dbStock = (int)$dbProduct['stock'];
                $dbPrice = (float)$dbProduct['price'];
                $dbName = $dbProduct['name'];

                // 2. Kiểm tra tồn kho
                if ($dbStock < $qty) {
                    throw new Exception("Sản phẩm '{$dbName}' không đủ hàng tồn kho (Còn lại: {$dbStock}).");
                }

                // 3. Ghi chi tiết đơn hàng (lấy giá gốc từ DB)
                $itemStmt->execute([
                    ':order_id' => $orderId,
                    ':product_id' => $productId,
                    ':product_name' => $dbName,
                    ':price' => $dbPrice,
                    ':quantity' => $qty,
                    ':line_total' => $dbPrice * $qty,
                ]);

                // 4. Trừ tồn kho
                $updateStockStmt->execute([
                    ':qty' => $qty,
                    ':id' => $productId,
                ]);
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
                'subtotal' => (float)($payload['subtotal'] ?? 0),
                'shipping_fee' => (float)($payload['shipping_fee'] ?? 0),
                'total_amount' => (float)($payload['total_amount'] ?? 0),
            ];
        } catch (Throwable $e) {
            $this->db->rollBack();
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
        $itemStmt = $this->db->prepare('SELECT * FROM order_items WHERE order_id = :order_id');
        $itemStmt->execute([':order_id' => $id]);
        $order['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

        return $order;
    }
}
