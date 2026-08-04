<?php
require_once ROOT_PATH . '/config/database.php';

class ProductAiChatHistory
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Lấy lịch sử trò chuyện của user đối với 1 sản phẩm cụ thể
     */
    public function getHistory(int $userId, int $productId, int $limit = 100): array
    {
        if ($this->db === null || $userId <= 0 || $productId <= 0) {
            return [];
        }

        try {
            $stmt = $this->db->prepare("
                SELECT id, user_id, product_id, role, message, created_at
                FROM product_ai_chat_histories
                WHERE user_id = :user_id AND product_id = :product_id
                ORDER BY id ASC
                LIMIT :limit
            ");
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ProductAiChatHistory::getHistory Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lưu một tin nhắn mới (User hoặc Assistant) vào DB
     */
    public function saveMessage(int $userId, int $productId, string $role, string $message): bool
    {
        if ($this->db === null || $userId <= 0 || $productId <= 0 || empty(trim($message))) {
            return false;
        }

        $role = in_array($role, ['user', 'assistant']) ? $role : 'user';

        try {
            $stmt = $this->db->prepare("
                INSERT INTO product_ai_chat_histories (user_id, product_id, role, message, created_at)
                VALUES (:user_id, :product_id, :role, :message, NOW())
            ");
            return $stmt->execute([
                ':user_id'    => $userId,
                ':product_id' => $productId,
                ':role'       => $role,
                ':message'    => trim($message),
            ]);
        } catch (Exception $e) {
            error_log("ProductAiChatHistory::saveMessage Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Đồng bộ mảng tin nhắn guest từ sessionStorage lên DB khi vừa đăng nhập
     */
    public function syncGuestMessages(int $userId, array $messages): int
    {
        if ($this->db === null || $userId <= 0 || empty($messages)) {
            return 0;
        }

        $syncedCount = 0;
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO product_ai_chat_histories (user_id, product_id, role, message, created_at)
                VALUES (:user_id, :product_id, :role, :message, :created_at)
            ");

            foreach ($messages as $msg) {
                $productId = (int)($msg['product_id'] ?? 0);
                $role = strtolower(trim($msg['role'] ?? 'user'));
                $messageText = trim($msg['message'] ?? $msg['text'] ?? '');
                $timestamp = isset($msg['timestamp']) && is_numeric($msg['timestamp']) ? (int)($msg['timestamp'] / 1000) : time();
                $createdAt = date('Y-m-d H:i:s', $timestamp);

                if ($productId > 0 && !empty($messageText) && in_array($role, ['user', 'assistant'])) {
                    $stmt->execute([
                        ':user_id'    => $userId,
                        ':product_id' => $productId,
                        ':role'       => $role,
                        ':message'    => $messageText,
                        ':created_at' => $createdAt,
                    ]);
                    $syncedCount++;
                }
            }

            $this->db->commit();
            return $syncedCount;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("ProductAiChatHistory::syncGuestMessages Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Lấy danh sách các sản phẩm user đã từng chat AI (cho trang Lịch sử cá nhân)
     */
    public function getUserChatSessions(int $userId): array
    {
        if ($this->db === null || $userId <= 0) {
            return [];
        }

        try {
            $sql = "
                SELECT 
                    p.id AS product_id,
                    p.name AS product_name,
                    p.slug AS product_slug,
                    p.image AS product_image,
                    p.price AS product_price,
                    COUNT(h.id) AS total_messages,
                    MAX(h.created_at) AS last_chat_at,
                    (
                        SELECT h2.message 
                        FROM product_ai_chat_histories h2 
                        WHERE h2.user_id = :user_id1 AND h2.product_id = p.id 
                        ORDER BY h2.id DESC LIMIT 1
                    ) AS last_message,
                    (
                        SELECT h3.role 
                        FROM product_ai_chat_histories h3 
                        WHERE h3.user_id = :user_id2 AND h3.product_id = p.id 
                        ORDER BY h3.id DESC LIMIT 1
                    ) AS last_message_role
                FROM product_ai_chat_histories h
                JOIN products p ON h.product_id = p.id
                WHERE h.user_id = :user_id3
                GROUP BY p.id, p.name, p.slug, p.image, p.price
                ORDER BY last_chat_at DESC
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id1' => $userId,
                ':user_id2' => $userId,
                ':user_id3' => $userId,
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ProductAiChatHistory::getUserChatSessions Error: " . $e->getMessage());
            return [];
        }
    }
}
