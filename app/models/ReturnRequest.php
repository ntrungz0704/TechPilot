<?php
require_once ROOT_PATH . '/config/database.php';

class ReturnRequest
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Tạo yêu cầu đổi trả */
    public function create(int $userId, int $orderId, string $reason, string $description, array $items): bool
    {
        if ($this->db === null) return false;

        $returnCode = 'RET-' . date('YmdHis') . '-' . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare(
                'INSERT INTO return_requests (return_code, order_id, user_id, reason, description, status) 
                 VALUES (:return_code, :order_id, :user_id, :reason, :description, :status)'
            );
            $stmt->execute([
                ':return_code' => $returnCode,
                ':order_id' => $orderId,
                ':user_id' => $userId,
                ':reason' => $reason,
                ':description' => $description,
                ':status' => 'requested'
            ]);

            $requestId = (int)$this->db->lastInsertId();

            $itemStmt = $this->db->prepare(
                'INSERT INTO return_items (return_request_id, order_item_id, quantity, resolution) 
                 VALUES (:return_request_id, :order_item_id, :quantity, :resolution)'
            );

            foreach ($items as $item) {
                $itemStmt->execute([
                    ':return_request_id' => $requestId,
                    ':order_item_id' => (int)$item['order_item_id'],
                    ':quantity' => (int)$item['quantity'],
                    ':resolution' => $item['resolution'] ?? 'refund'
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /** Lấy các yêu cầu đổi trả của user */
    public function getByUserId(int $userId): array
    {
        if ($this->db === null) return [];

        $stmt = $this->db->prepare(
            'SELECT r.*, o.order_code 
             FROM return_requests r
             JOIN orders o ON r.order_id = o.id
             WHERE r.user_id = :user_id
             ORDER BY r.created_at DESC'
        );
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }
}
