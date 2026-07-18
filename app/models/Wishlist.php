<?php
require_once ROOT_PATH . '/config/database.php';

class Wishlist
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Lấy danh sách sản phẩm yêu thích */
    public function getItems(int $userId): array
    {
        if ($this->db === null) return [];

        $stmt = $this->db->prepare(
            'SELECT p.*, b.name as brand_name 
             FROM wishlists w
             JOIN products p ON w.product_id = p.id
             LEFT JOIN brands b ON p.brand_id = b.id
             WHERE w.user_id = :user_id AND p.status = :status
             ORDER BY w.created_at DESC'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':status' => 'active'
        ]);
        return $stmt->fetchAll();
    }

    /** Thêm sản phẩm vào wishlist */
    public function add(int $userId, int $productId): bool
    {
        if ($this->db === null) return false;

        $stmt = $this->db->prepare('INSERT IGNORE INTO wishlists (user_id, product_id) VALUES (:user_id, :product_id)');
        return $stmt->execute([':user_id' => $userId, ':product_id' => $productId]);
    }

    /** Xóa sản phẩm khỏi wishlist */
    public function remove(int $userId, int $productId): bool
    {
        if ($this->db === null) return false;

        $stmt = $this->db->prepare('DELETE FROM wishlists WHERE user_id = :user_id AND product_id = :product_id');
        return $stmt->execute([':user_id' => $userId, ':product_id' => $productId]);
    }
}
