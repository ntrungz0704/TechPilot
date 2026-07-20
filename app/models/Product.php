<?php
require_once ROOT_PATH . '/config/database.php';

class Product
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Lấy toàn bộ sản phẩm, có thể giới hạn số lượng */
    public function getAll(int $limit = 100): array
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->prepare('SELECT p.*, b.name as brand_name FROM products p LEFT JOIN brands b ON p.brand_id = b.id ORDER BY p.id DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getFlashSale(int $limit = 6): array
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT p.*, p.sale_price as discount_price, p.stock as fs_stock, 
                    0 as fs_sold, fs.end_time 
             FROM products p
             CROSS JOIN flash_sales fs
             WHERE fs.start_time <= NOW() AND fs.end_time >= NOW() AND fs.status = \'active\'
               AND p.is_flash_sale = 1 AND p.sale_price IS NOT NULL
             ORDER BY p.id DESC LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Lấy sản phẩm theo slug danh mục */
    public function getByCategorySlug(string $slug, int $limit = 6): array
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT p.*, c.name as category_name, b.name as brand_name 
             FROM products p
             JOIN categories c ON p.category_id = c.id
             LEFT JOIN brands b ON p.brand_id = b.id
             WHERE c.slug = :slug
             ORDER BY p.id DESC LIMIT :limit'
        );
        $stmt->bindValue(':slug', $slug);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Lấy 1 sản phẩm theo id */
    public function getById(int $id): array|false
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT p.*, c.name as category_name, c.slug as category_slug, b.name as brand_name 
             FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             LEFT JOIN brands b ON p.brand_id = b.id 
             WHERE p.id = :id LIMIT 1'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /** Lấy 1 sản phẩm theo slug (trang chi tiết) */
    public function getBySlug(string $slug): array|false
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT p.*, c.name as category_name, c.slug as category_slug, b.name as brand_name 
             FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             LEFT JOIN brands b ON p.brand_id = b.id 
             WHERE p.slug = :slug LIMIT 1'
        );
        $stmt->bindValue(':slug', $slug);
        $stmt->execute();
        return $stmt->fetch();
    }

    /** Lấy danh sách ảnh phụ từ product_images */
    public function getProductImages(int $productId): array
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->prepare('SELECT * FROM product_images WHERE product_id = :product_id');
        $stmt->execute([':product_id' => $productId]);
        return $stmt->fetchAll();
    }

    /** Lấy sản phẩm liên quan (cùng danh mục, khác id hiện tại) */
    public function getRelated(int $categoryId, int $excludeId, int $limit = 4): array
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT p.*, b.name as brand_name 
             FROM products p
             LEFT JOIN brands b ON p.brand_id = b.id
             WHERE p.category_id = :cat AND p.id != :id 
             ORDER BY RAND() LIMIT :limit'
        );
        $stmt->bindValue(':cat', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $excludeId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Danh sách toàn bộ danh mục */
    public function getCategories(): array
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->query('SELECT * FROM categories ORDER BY id ASC');
        return $stmt->fetchAll();
    }

    /** Lấy sản phẩm best seller hoặc phân loại theo tab cho Best Sellers */
    public function getBestSellersByTab(string $tab, int $limit = 6): array
    {
        if ($this->db === null) {
            return [];
        }

        $slugs = [];
        switch (strtolower($tab)) {
            case 'laptop':
                $slugs = ['laptop-gaming', 'laptop-van-phong'];
                break;
            case 'gaming':
                $slugs = ['gaming-gear', 'laptop-gaming'];
                break;
            case 'components':
                $slugs = ['pc-linh-kien'];
                break;
            case 'monitor':
                $slugs = ['man-hinh'];
                break;
            case 'accessories':
                $slugs = ['gaming-gear', 'office-gear'];
                break;
            default:
                return $this->getBestSellers($limit);
        }

        $inQuery = implode(',', array_fill(0, count($slugs), '?'));
        $sql = "SELECT p.*, b.name as brand_name 
                FROM products p
                JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE c.slug IN ($inQuery) AND p.is_best_seller = 1
                ORDER BY p.id DESC LIMIT ?";

        $stmt = $this->db->prepare($sql);
        foreach ($slugs as $k => $slug) {
            $stmt->bindValue($k + 1, $slug);
        }
        $stmt->bindValue(count($slugs) + 1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getBestSellers(int $limit = 6): array
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->prepare('SELECT p.*, b.name as brand_name FROM products p LEFT JOIN brands b ON p.brand_id = b.id WHERE p.is_best_seller = 1 ORDER BY p.id DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getNewArrivals(int $limit = 6): array
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->prepare('SELECT p.*, b.name as brand_name FROM products p LEFT JOIN brands b ON p.brand_id = b.id WHERE p.is_new_arrival = 1 ORDER BY p.id DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAiRecommend(int $limit = 6): array
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->prepare('SELECT p.*, b.name as brand_name FROM products p LEFT JOIN brands b ON p.brand_id = b.id WHERE p.is_ai_recommend = 1 ORDER BY p.id DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Tìm kiếm sản phẩm theo từ khóa và danh mục */
    public function search(string $keyword = '', string $categorySlug = '', int $limit = 24, int $minPrice = 0, int $maxPrice = 50000000): array
    {
        if ($this->db === null) {
            return [];
        }

        // 1. Chuẩn hóa keyword bằng helper
        $keyword = normalizeSearchKeyword($keyword);
        $minPrice = max(0, $minPrice);
        $maxPrice = max(0, $maxPrice);

        if ($maxPrice < $minPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        $searchAliases = [
            'máy tính'       => ['pc', 'desktop', 'máy tính để bàn'],
            'máy bộ'         => ['pc build sẵn', 'desktop'],
            'máy chơi game'  => ['pc gaming', 'laptop gaming'],
            'card màn hình'  => ['vga', 'gpu'],
            'ổ cứng'         => ['ssd', 'hdd'],
            'bộ nhớ'         => ['ram'],
            'tai nghe game'  => ['gaming headset'],
            'bo mạch chủ'    => ['mainboard', 'main'],
            'nguồn máy tính' => ['psu', 'nguồn pc'],
            'tản nhiệt'      => ['cooler', 'fan cpu'],
        ];

        // 2. Tìm từ đồng nghĩa
        $aliasesFound = [];
        if (!empty($keyword)) {
            foreach ($searchAliases as $key => $values) {
                if (str_contains($keyword, $key)) {
                    $aliasesFound = array_merge($aliasesFound, $values);
                }
                foreach ($values as $val) {
                    if (str_contains($keyword, $val)) {
                        $aliasesFound[] = $key;
                        $aliasesFound = array_merge($aliasesFound, array_diff($values, [$val]));
                    }
                }
            }
            $aliasesFound = array_unique($aliasesFound);
        }

        // 3. Xây dựng SQL Relevance
        // NOTE: $relevanceSql chỉ nhúng MỘT LẦN vào SELECT (alias 'relevance').
        // Không nhúng lại vào WHERE để tránh lỗi HY093 (duplicate named placeholders).
        $relevanceSql = '0';
        $params = [];

        if (!empty($keyword)) {
            $relevanceSql = '
                (CASE WHEN p.name = :exactName THEN 100 ELSE 0 END) +
                (CASE WHEN p.name LIKE :startsName THEN 70 ELSE 0 END) +
                (CASE WHEN p.name LIKE :containsName THEN 50 ELSE 0 END) +
                (CASE WHEN c.name LIKE :containsCat THEN 30 ELSE 0 END) +
                (CASE WHEN b.name LIKE :containsBrand THEN 20 ELSE 0 END) +
                (CASE WHEN p.description LIKE :containsDesc THEN 5 ELSE 0 END)
            ';

            $params[':exactName']     = $keyword;
            $params[':startsName']    = $keyword . '%';
            $params[':containsName']  = '%' . $keyword . '%';
            $params[':containsCat']   = '%' . $keyword . '%';
            $params[':containsBrand'] = '%' . $keyword . '%';
            $params[':containsDesc']  = '%' . $keyword . '%';

            // Cộng thêm điểm cho các alias
            $aliasIdx = 1;
            foreach ($aliasesFound as $alias) {
                $aliasParam = ':alias_' . $aliasIdx;
                $relevanceSql .= " + (CASE WHEN p.name LIKE $aliasParam THEN 35 ELSE 0 END)";
                $params[$aliasParam] = '%' . $alias . '%';
                $aliasIdx++;
            }
        }

        // $relevanceSql chỉ xuất hiện MỘT LẦN trong SELECT để tạo alias 'relevance'
        $query = "
            SELECT p.*, b.name as brand_name, c.name as category_name, ($relevanceSql) as relevance
            FROM products p
            LEFT JOIN brands b ON p.brand_id = b.id
            JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'active'
        ";

        if (!empty($keyword)) {
            // Dùng param TÊN MỚI :filterName / :filterDesc để tránh trùng với params trong $relevanceSql
            $query .= ' AND (p.name LIKE :filterName OR p.description LIKE :filterDesc)';
            $params[':filterName'] = '%' . $keyword . '%';
            $params[':filterDesc'] = '%' . $keyword . '%';
        }

        if (!empty($categorySlug)) {
            $query .= ' AND c.slug = :category';
            $params[':category'] = $categorySlug;
        }

        $query .= ' AND COALESCE(p.sale_price, p.price) BETWEEN :minPrice AND :maxPrice';
        $params[':minPrice'] = $minPrice;
        $params[':maxPrice'] = $maxPrice;

        if (!empty($keyword)) {
            // Dùng alias 'relevance' từ SELECT — KHÔNG nhúng lại $relevanceSql
            $query .= ' ORDER BY relevance DESC, p.created_at DESC';
        } else {
            $query .= ' ORDER BY p.id DESC';
        }

        $query .= ' LIMIT :limit';

        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $val) {
            if ($key === ':minPrice' || $key === ':maxPrice') {
                $stmt->bindValue($key, $val, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $val);
            }
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Lấy sản phẩm theo danh mục không giới hạn */
    public function getByCategory(string $slug, int $limit = 24): array
    {
        return $this->getByCategorySlug($slug, $limit);
    }

    /** Lấy danh sách sản phẩm từ list IDs */
    public function getProductsByIds(array $ids): array
    {
        $ids = array_values($ids);
        if ($this->db === null || empty($ids)) return [];

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare(
            "SELECT p.*, b.name as brand_name, c.name as category_name 
             FROM products p
             LEFT JOIN brands b ON p.brand_id = b.id
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.id IN ($placeholders) AND p.status = 'active'"
        );
        $stmt->execute(array_map('intval', $ids));
        return $stmt->fetchAll();
    }
}
