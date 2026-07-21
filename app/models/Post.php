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
            return [];
        }

        $stmt = $this->db->prepare('SELECT * FROM posts WHERE status = "published" ORDER BY COALESCE(published_at, created_at) DESC, id DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Lấy bài viết tiêu điểm (mới nhất hoặc is_featured) */
    public function getFeatured(): ?array
    {
        if ($this->db === null) return null;

        // Cố gắng tìm bài featured
        $stmt = $this->db->prepare('SELECT * FROM posts WHERE status = "published" AND is_featured = 1 ORDER BY COALESCE(published_at, created_at) DESC, id DESC LIMIT 1');
        $stmt->execute();
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        // Nếu không có, lấy bài mới nhất published
        if (!$post) {
            $stmt = $this->db->prepare('SELECT * FROM posts WHERE status = "published" ORDER BY COALESCE(published_at, created_at) DESC, id DESC LIMIT 1');
            $stmt->execute();
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return $post ?: null;
    }

    /** Lấy danh sách bài viết phổ biến (nhiều lượt xem nhất, ưu tiên 30 ngày gần đây) */
    public function getPopular(int $limit = 3): array
    {
        if ($this->db === null) return [];
        // Ưu tiên bài viết trong 30 ngày qua, sau đó mới tính đến view chung
        $sql = '
            SELECT * FROM posts 
            WHERE status = "published" 
            ORDER BY 
                CASE 
                    WHEN COALESCE(published_at, created_at) >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1
                    ELSE 0 
                END DESC,
                views DESC, 
                COALESCE(published_at, created_at) DESC 
            LIMIT :limit
        ';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Build where clause cho filter type, category và legacy tag, bao gồm cả từ khóa tìm kiếm (q) */
    private function buildFilterWhereClause(string $type = '', string $category = '', string $tag = '', string $q = '', array &$params = []): string
    {
        $sql = '';

        // Nếu dùng tag cũ mà chưa có type/category thì map từ tag
        if (!empty($tag) && empty($type) && empty($category)) {
            $tagMap = [
                'danh-gia'     => ['type' => 'review'],
                'thu-thuat'    => ['type' => 'guide'],
                'tin-moi'      => ['type' => 'news'],
                'so-sanh'      => ['type' => 'comparison'],
                'laptop'       => ['category' => 'laptop'],
                'gaming'       => ['category' => 'pc-gaming'],
                'pc-linh-kien' => ['category' => 'pc-linh-kien'],
            ];
            if (isset($tagMap[$tag])) {
                $type = $tagMap[$tag]['type'] ?? '';
                $category = $tagMap[$tag]['category'] ?? '';
            } else {
                $category = $tag;
            }
        }

        if (!empty($type)) {
            $sql .= ' AND post_type = :type';
            $params[':type'] = $type;
        }

        if (!empty($category)) {
            if ($category === 'pc-gaming') {
                $sql .= ' AND category_slug IN ("pc-gaming", "gaming")';
            } elseif ($category === 'ai-cong-nghe-moi') {
                $sql .= ' AND category_slug IN ("ai-cong-nghe-moi", "ai")';
            } else {
                $sql .= ' AND category_slug = :category';
                $params[':category'] = $category;
            }
        }

        if (!empty($q)) {
            $sql .= ' AND (title LIKE :keyword OR summary LIKE :keyword OR content LIKE :keyword)';
            $params[':keyword'] = '%' . $q . '%';
        }

        return $sql;
    }

    /** Đếm số lượng bài viết để phân trang (hỗ trợ excludeId để đồng bộ với getAll) */
    public function countAll(string $type = '', string $category = '', string $tag = '', string $q = '', ?int $excludeId = null): int
    {
        if ($this->db === null) return 0;

        $sql = 'SELECT COUNT(*) FROM posts WHERE status = "published"';
        $params = [];
        $sql .= $this->buildFilterWhereClause($type, $category, $tag, $q, $params);

        if ($excludeId !== null) {
            $sql .= ' AND id != :excludeId';
            $params[':excludeId'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            if ($key === ':excludeId') {
                $stmt->bindValue($key, $val, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $val, PDO::PARAM_STR);
            }
        }

        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /** Lấy danh sách bài viết phân trang */
    public function getAll(int $offset, int $limit, string $type = '', string $category = '', string $tag = '', string $q = '', ?int $excludeId = null): array
    {
        if ($this->db === null) return [];

        $sql = 'SELECT * FROM posts WHERE status = "published"';
        $params = [];

        $sql .= $this->buildFilterWhereClause($type, $category, $tag, $q, $params);

        if ($excludeId !== null) {
            $sql .= ' AND id != :excludeId';
            $params[':excludeId'] = $excludeId;
        }

        $sql .= ' ORDER BY COALESCE(published_at, created_at) DESC, id DESC LIMIT :limit OFFSET :offset';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        foreach ($params as $key => $val) {
            if ($key === ':excludeId') {
                $stmt->bindValue($key, $val, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $val, PDO::PARAM_STR);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /** Lấy bài viết liên quan dựa trên độ tương đồng */
    public function getRelatedPosts(int $postId, string $categorySlug, string $postType, int $limit = 3): array
    {
        if ($this->db === null) return [];

        $sql = '
            SELECT *,
            (CASE WHEN category_slug = :cat1 AND post_type = :type1 THEN 3
                  WHEN category_slug = :cat2 THEN 2
                  WHEN post_type = :type2 THEN 1
                  ELSE 0 END) as relevance_score
            FROM posts
            WHERE status = "published" AND id != :postId
            HAVING relevance_score > 0
            ORDER BY relevance_score DESC, COALESCE(published_at, created_at) DESC
            LIMIT :limit
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':cat1', $categorySlug, PDO::PARAM_STR);
        $stmt->bindValue(':type1', $postType, PDO::PARAM_STR);
        $stmt->bindValue(':cat2', $categorySlug, PDO::PARAM_STR);
        $stmt->bindValue(':type2', $postType, PDO::PARAM_STR);
        $stmt->bindValue(':postId', $postId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Lấy chi tiết bài viết theo slug */
    public function getBySlug(string $slug): ?array
    {
        if ($this->db === null) return null;
        $stmt = $this->db->prepare('
            SELECT p.*, COALESCE(u.full_name, p.author_name) as author_name
            FROM posts p
            LEFT JOIN users u ON p.author_id = u.id
            WHERE p.slug = :slug AND p.status = "published"
            LIMIT 1
        ');
        $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
        $stmt->execute();
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($post) {
            if (empty($post['author_name'])) {
                $post['author_name'] = 'Đội ngũ TechPilot';
            }
            if (empty($post['reading_minutes']) || $post['reading_minutes'] == 0) {
                // Dùng regex UTF-8 để đếm từ tiếng Việt chính xác
                $text = strip_tags($post['content'] ?? '');
                preg_match_all('/[\p{L}\p{N}]+/u', $text, $m);
                $post['reading_minutes'] = max(1, (int)ceil(count($m[0]) / 200));
            }
        }
        return $post ?: null;
    }

    /** Tăng lượt xem bài viết */
    public function incrementViews(int $id): void
    {
        if ($this->db === null) return;
        $stmt = $this->db->prepare('UPDATE posts SET views = views + 1 WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    /** Helper: Slugify (Tạo slug tiếng Việt chuẩn) */
    public static function slugify(string $text): string
    {
        $utf8 = [
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ|Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'd' => 'đ|Đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ|É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'i' => 'í|ì|ỉ|ĩ|ị|Í|Ì|Ỉ|Ĩ|Ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ|Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự|Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ|Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        ];
        foreach ($utf8 as $ascii => $uni) {
            $text = preg_replace("/($uni)/i", $ascii, $text);
        }
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
        $text = trim($text, '-');

        if (empty($text)) {
            $text = 'bai-viet-' . time();
        }
        return $text;
    }

    /** Helper: Check unique slug */
    public function isSlugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($this->db === null) return false;

        $sql = 'SELECT id FROM posts WHERE slug = :slug';
        $params = [':slug' => $slug];

        if ($excludeId !== null) {
            $sql .= ' AND id != :excludeId';
            $params[':excludeId'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetchColumn();
    }
}
