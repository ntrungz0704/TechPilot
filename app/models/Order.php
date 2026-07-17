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

            $itemStmt = $this->db->prepare(
                'INSERT INTO order_items (order_id, product_id, product_name, price, quantity, line_total)
                 VALUES (:order_id, :product_id, :product_name, :price, :quantity, :line_total)'
            );

            foreach ($payload['items'] ?? [] as $item) {
                $qty = max(1, (int)($item['quantity'] ?? 1));
                $price = (float)($item['price'] ?? 0);
                $itemStmt->execute([
                    ':order_id' => $orderId,
                    ':product_id' => (int)($item['product_id'] ?? 0),
                    ':product_name' => $item['name'] ?? 'Sản phẩm',
                    ':price' => $price,
                    ':quantity' => $qty,
                    ':line_total' => $price * $qty,
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
}
