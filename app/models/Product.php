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

    /** Lấy sản phẩm đang Flash Sale cùng thông tin đếm ngược, số lượng bán */
    public function getFlashSale(int $limit = 6): array
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT p.*, fsi.discount_price, fsi.allocation_quantity as fs_stock, fsi.sold_quantity as fs_sold, fs.end_time 
             FROM products p
             JOIN flash_sale_items fsi ON p.id = fsi.product_id
             JOIN flash_sales fs ON fsi.flash_sale_id = fs.id
             WHERE fs.start_time <= NOW() AND fs.end_time >= NOW() AND fs.status = \'active\'
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
    public function search(string $keyword = '', string $categorySlug = '', int $limit = 24): array
    {
        if ($this->db === null) {
            return [];
        }

        $query = 'SELECT p.*, b.name as brand_name FROM products p LEFT JOIN brands b ON p.brand_id = b.id';

        if (!empty($categorySlug)) {
            $query .= ' JOIN categories c ON p.category_id = c.id';
        }

        $query .= ' WHERE 1=1';

        if (!empty($keyword)) {
            $query .= ' AND (p.name LIKE :keyword1 OR p.short_desc LIKE :keyword2 OR p.description LIKE :keyword3)';
        }

        if (!empty($categorySlug)) {
            $query .= ' AND c.slug = :category';
        }

        $query .= ' ORDER BY p.id DESC LIMIT :limit';

        $stmt = $this->db->prepare($query);

        if (!empty($keyword)) {
            $kw = '%' . $keyword . '%';
            $stmt->bindValue(':keyword1', $kw);
            $stmt->bindValue(':keyword2', $kw);
            $stmt->bindValue(':keyword3', $kw);
        }

        if (!empty($categorySlug)) {
            $stmt->bindValue(':category', $categorySlug);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Lấy sản phẩm theo danh mục không giới hạn */
    public function getByCategory(string $slug, int $limit = 24): array
    {
        return $this->getByCategorySlug($slug, $limit);
    }
}
