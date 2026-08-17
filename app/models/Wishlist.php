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
            'SELECT p.*, c.slug as category_slug, b.name as brand_name 
             FROM wishlists w
             JOIN products p ON w.product_id = p.id
             LEFT JOIN categories c ON p.category_id = c.id
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

    /** Kiểm tra xem sản phẩm đã có trong wishlist chưa */
    public function has(int $userId, int $productId): bool
    {
        if ($this->db === null) return false;

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM wishlists WHERE user_id = :user_id AND product_id = :product_id');
        $stmt->execute([':user_id' => $userId, ':product_id' => $productId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /** Lấy danh sách ID sản phẩm yêu thích của user */
    public function getUserWishlistIds(int $userId): array
    {
        if ($this->db === null) return [];

        $stmt = $this->db->prepare('SELECT product_id FROM wishlists WHERE user_id = :user_id');
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Đếm tổng số sản phẩm yêu thích */
    public function count(int $userId): int
    {
        if ($this->db === null) return 0;

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM wishlists WHERE user_id = :user_id');
        $stmt->execute([':user_id' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    /** Bật/Tắt yêu thích (Toggle) */
    public function toggle(int $userId, int $productId): array
    {
        if ($this->has($userId, $productId)) {
            $this->remove($userId, $productId);
            $inWishlist = false;
            $msg = 'Đã xóa khỏi danh sách yêu thích.';
        } else {
            $this->add($userId, $productId);
            $inWishlist = true;
            $msg = 'Đã thêm vào danh sách yêu thích.';
        }

        return [
            'inWishlist' => $inWishlist,
            'count' => $this->count($userId),
            'message' => $msg
        ];
    }
}
