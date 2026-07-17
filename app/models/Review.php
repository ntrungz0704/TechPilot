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

        $stmt = $this->db->prepare('SELECT * FROM reviews ORDER BY id DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Lấy đánh giá theo sản phẩm */
    public function getByProduct(int $productId): array
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->prepare('SELECT * FROM reviews WHERE product_id = :product_id ORDER BY id DESC');
        $stmt->execute([':product_id' => $productId]);
        return $stmt->fetchAll();
    }
}
