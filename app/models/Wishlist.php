<?php
require_once ROOT_PATH . '/config/database.php';

class Wishlist
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Lấy hoặc tạo wishlist của user */
    public function getOrCreateWishlistId(int $userId): int|false
    {
        if ($this->db === null) return false;

        $stmt = $this->db->prepare('SELECT id FROM wishlists WHERE user_id = :user_id LIMIT 1');
        $stmt->execute([':user_id' => $userId]);
        $row = $stmt->fetch();
        if ($row) {
            return (int)$row['id'];
        }

        $stmt = $this->db->prepare('INSERT INTO wishlists (user_id) VALUES (:user_id)');
        $stmt->execute([':user_id' => $userId]);
        return (int)$this->db->lastInsertId();
    }

    /** Lấy danh sách sản phẩm yêu thích */
    public function getItems(int $userId): array
    {
        if ($this->db === null) return [];

        $wishlistId = $this->getOrCreateWishlistId($userId);
        if (!$wishlistId) return [];

        $stmt = $this->db->prepare(
            'SELECT p.*, b.name as brand_name 
             FROM wishlist_items wi
             JOIN products p ON wi.product_id = p.id
             LEFT JOIN brands b ON p.brand_id = b.id
             WHERE wi.wishlist_id = :wishlist_id AND p.status = :status
             ORDER BY wi.created_at DESC'
        );
        $stmt->execute([
            ':wishlist_id' => $wishlistId,
            ':status' => 'active'
        ]);
        return $stmt->fetchAll();
    }

    /** Thêm sản phẩm vào wishlist */
    public function add(int $userId, int $productId): bool
    {
        if ($this->db === null) return false;

        $wishlistId = $this->getOrCreateWishlistId($userId);
        if (!$wishlistId) return false;

        // Kiểm tra xem đã có chưa
        $stmt = $this->db->prepare('SELECT id FROM wishlist_items WHERE wishlist_id = :wishlist_id AND product_id = :product_id');
        $stmt->execute([':wishlist_id' => $wishlistId, ':product_id' => $productId]);
        if ($stmt->fetch()) {
            return true; // đã có
        }

        $stmt = $this->db->prepare('INSERT INTO wishlist_items (wishlist_id, product_id) VALUES (:wishlist_id, :product_id)');
        return $stmt->execute([':wishlist_id' => $wishlistId, ':product_id' => $productId]);
    }

    /** Xóa sản phẩm khỏi wishlist */
    public function remove(int $userId, int $productId): bool
    {
        if ($this->db === null) return false;

        $wishlistId = $this->getOrCreateWishlistId($userId);
        if (!$wishlistId) return false;

        $stmt = $this->db->prepare('DELETE FROM wishlist_items WHERE wishlist_id = :wishlist_id AND product_id = :product_id');
        return $stmt->execute([':wishlist_id' => $wishlistId, ':product_id' => $productId]);
    }
}
