<?php
require_once ROOT_PATH . '/config/database.php';

class Review
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Lấy toàn bộ đánh giá của khách hàng ở trang chủ */
    public function getLatestReviews(int $limit = 6): array
    {
        if ($this->db === null) {
            return [
                [
                    'reviewer_name' => 'Nguyễn Hoàng Nam',
                    'rating' => 5.0,
                    'comment' => 'Sản phẩm chính hãng, màn hình OLED siêu đẹp, chơi game mượt mà cực kỳ thích! Giao hàng nhanh.',
                ],
                [
                    'reviewer_name' => 'Trần Minh Đức',
                    'rating' => 4.5,
                    'comment' => 'Thiết kế mỏng nhẹ tiện mang đi làm, hiệu năng i9 siêu mạnh nhưng máy hơi ấm lên khi chơi game nặng lâu.',
                ],
            ];
        }

        $stmt = $this->db->prepare('SELECT * FROM reviews WHERE status = \'approved\' ORDER BY id DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Lấy đánh giá theo sản phẩm */
    public function getByProduct(int $productId): array
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->prepare('SELECT * FROM reviews WHERE product_id = :product_id AND status = \'approved\' ORDER BY id DESC');
        $stmt->execute([':product_id' => $productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Kiểm tra xem user đã từng mua sản phẩm này (đơn completed) chưa */
    public function hasPurchasedProduct(int $userId, int $productId): bool
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM order_items oi
             JOIN orders o ON oi.order_id = o.id
             WHERE o.user_id = :user_id 
               AND oi.product_id = :product_id 
               AND o.status = \'completed\''
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':product_id' => $productId
        ]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /** Thêm review mới với comment được escape chống XSS */
    public function create(int $productId, int $userId, string $reviewerName, int $rating, string $comment): bool
    {
        if ($this->db === null) {
            return false;
        }

        $cleanComment = htmlspecialchars(trim($comment), ENT_QUOTES, 'UTF-8');

        $stmt = $this->db->prepare(
            'INSERT INTO reviews (product_id, user_id, reviewer_name, rating, comment, status)
             VALUES (:product_id, :user_id, :reviewer_name, :rating, :comment, \'approved\')'
        );
        return $stmt->execute([
            ':product_id' => $productId,
            ':user_id' => $userId,
            ':reviewer_name' => $reviewerName,
            ':rating' => $rating,
            ':comment' => $cleanComment
        ]);
    }
}
