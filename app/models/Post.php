<?php
require_once ROOT_PATH . '/config/database.php';

class Post
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Lấy danh sách tin tức công nghệ mới nhất */
    public function getLatest(int $limit = 4): array
    {
        if ($this->db === null) {
            return [
                [
                    'id' => 1,
                    'title' => 'Đánh giá chi tiết NVIDIA RTX 50 Series: Bước nhảy vọt hiệu năng AI',
                    'slug' => 'nvidia-rtx-50-series-danh-gia',
                    'summary' => 'Những thông tin mới nhất về hiệu năng, giá bán và ngày ra mắt card đồ họa thế hệ tiếp theo của NVIDIA.',
                    'image' => 'news-rtx-50.jpg',
                    'created_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'id' => 2,
                    'title' => 'Intel Core Ultra 9: CPU thế hệ mới dành cho các dòng laptop mỏng nhẹ 2026',
                    'slug' => 'intel-core-ultra-9-laptop-thin-light',
                    'summary' => 'Dòng chip sở hữu NPU chuyên biệt phục vụ các tác vụ trí tuệ nhân tạo trực tiếp trên thiết bị.',
                    'image' => 'news-intel-ultra.jpg',
                    'created_at' => date('Y-m-d H:i:s'),
                ],
            ];
        }

        $stmt = $this->db->prepare('SELECT * FROM posts ORDER BY id DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
