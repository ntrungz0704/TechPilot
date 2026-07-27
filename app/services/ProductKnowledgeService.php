<?php

/**
 * ProductKnowledgeService - Single Source of Truth for fetching structured product & store knowledge from MySQL.
 */
class ProductKnowledgeService
{
    private static function getDb(): PDO
    {
        return Database::getConnection();
    }

    /**
     * Lấy ngữ cảnh thông tin đầy đủ của một sản phẩm cụ thể từ Database.
     */
    public static function getProductContext(int $productId): ?array
    {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT p.*, c.name AS category_name, c.slug AS category_slug, b.name AS brand_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE p.id = :id
        ");
        $stmt->execute(['id' => $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            return null;
        }

        // Lấy danh sách ảnh phụ
        $imgStmt = $db->prepare("SELECT image_url, alt_text FROM product_images WHERE product_id = :pid ORDER BY position ASC");
        $imgStmt->execute(['pid' => $productId]);
        $images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);

        // Lấy thông số kỹ thuật raw
        $specs = json_decode($product['specs'] ?? '{}', true) ?: [];

        // Lấy 3 đánh giá mới nhất
        $revStmt = $db->prepare("SELECT rating, reviewer_name, comment FROM reviews WHERE product_id = :pid ORDER BY created_at DESC LIMIT 3");
        $revStmt->execute(['pid' => $productId]);
        $reviews = $revStmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'id' => (int)$product['id'],
            'name' => $product['name'],
            'category' => $product['category_name'] ?? 'Chưa rõ',
            'category_slug' => $product['category_slug'] ?? '',
            'brand' => $product['brand_name'] ?? 'TechPilot',
            'price' => (float)$product['price'],
            'old_price' => !empty($product['old_price']) ? (float)$product['old_price'] : null,
            'stock' => (int)$product['stock'],
            'warranty_months' => (int)($product['warranty_months'] ?? 36),
            'short_desc' => $product['short_desc'] ?? '',
            'description' => strip_tags(html_entity_decode($product['description'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')),
            'highlights' => json_decode($product['highlights'] ?? '[]', true) ?: [],
            'limitations' => json_decode($product['limitations'] ?? '[]', true) ?: [],
            'specs' => $specs,
            'image_count' => count($images) + 1,
            'recent_reviews' => $reviews
        ];
    }

    /**
     * Lấy ngữ cảnh kho hàng ngắn gọn khi tư vấn mua sắm theo từ khóa hoặc danh mục.
     */
    public static function getCandidateProducts(?string $keyword = null, ?string $categorySlug = null, int $limit = 5): array
    {
        $db = self::getDb();
        $sql = "
            SELECT p.id, p.name, p.price, p.stock, p.specs, c.name AS category_name, b.name AS brand_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE p.status = 'active'
        ";
        $params = [];

        if ($categorySlug) {
            $sql .= " AND c.slug = :cat";
            $params['cat'] = $categorySlug;
        }

        if ($keyword) {
            $sql .= " AND (p.name LIKE :kw OR p.description LIKE :kw)";
            $params['kw'] = "%$keyword%";
        }

        $sql .= " ORDER BY p.is_featured DESC, p.id DESC LIMIT " . (int)$limit;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id' => (int)$row['id'],
                'name' => $row['name'],
                'category' => $row['category_name'],
                'brand' => $row['brand_name'],
                'price' => (float)$row['price'],
                'stock' => (int)$row['stock'],
                'specs' => json_decode($row['specs'] ?? '{}', true) ?: []
            ];
        }
        return $result;
    }
}
