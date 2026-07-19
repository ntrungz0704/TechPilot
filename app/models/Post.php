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

        $stmt = $this->db->prepare('SELECT * FROM posts WHERE status = "published" ORDER BY id DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Lấy bài viết tiêu điểm (mới nhất) */
    public function getFeatured(): ?array
    {
        if ($this->db === null) return null;
        $stmt = $this->db->prepare('SELECT * FROM posts WHERE status = "published" ORDER BY id DESC LIMIT 1');
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /** Lấy danh sách bài viết phổ biến (nhiều lượt xem nhất) */
    public function getPopular(int $limit = 3): array
    {
        if ($this->db === null) return [];
        $stmt = $this->db->prepare('SELECT * FROM posts WHERE status = "published" ORDER BY views DESC, id DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Đếm số lượng bài viết để phân trang */
    public function countAll(string $tag = ''): int
    {
        if ($this->db === null) return 0;
        
        $sql = 'SELECT COUNT(*) FROM posts WHERE status = "published"';
        if (!empty($tag)) {
            $sql .= ' AND (title LIKE :tag OR summary LIKE :tag OR content LIKE :tag)';
        }
        
        $stmt = $this->db->prepare($sql);
        if (!empty($tag)) {
            $stmt->bindValue(':tag', '%' . $tag . '%', PDO::PARAM_STR);
        }
        
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /** Lấy danh sách bài viết phân trang */
    public function getAll(int $offset, int $limit, string $tag = '', ?int $excludeId = null): array
    {
        if ($this->db === null) return [];
        
        $sql = 'SELECT * FROM posts WHERE status = "published"';
        if (!empty($tag)) {
            $sql .= ' AND (title LIKE :tag OR summary LIKE :tag OR content LIKE :tag)';
        }
        if ($excludeId !== null) {
            $sql .= ' AND id != :excludeId';
        }
        
        $sql .= ' ORDER BY id DESC LIMIT :limit OFFSET :offset';
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        if (!empty($tag)) {
            $stmt->bindValue(':tag', '%' . $tag . '%', PDO::PARAM_STR);
        }
        if ($excludeId !== null) {
            $stmt->bindValue(':excludeId', $excludeId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Lấy chi tiết bài viết theo slug */
    public function getBySlug(string $slug): ?array
    {
        if ($this->db === null) return null;
        $stmt = $this->db->prepare('SELECT * FROM posts WHERE slug = :slug AND status = "published" LIMIT 1');
        $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /** Tăng lượt xem bài viết */
    public function incrementViews(int $id): void
    {
        if ($this->db === null) return;
        $stmt = $this->db->prepare('UPDATE posts SET views = views + 1 WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}
