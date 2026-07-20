<?php
require_once ROOT_PATH . '/config/database.php';

class Product
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Danh sách sản phẩm mẫu làm fallback khi DB chưa có dữ liệu */
    public static function getSampleProducts(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Laptop Gaming ASUS ROG Strix G16 (2024) i7-13650HX / RTX 4060 / 16GB / 512GB',
                'slug' => 'asus-rog-strix-g16-2024',
                'price' => 34990000,
                'sale_price' => 31990000,
                'discount_price' => 31990000,
                'image' => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=500&auto=format&fit=crop&q=60',
                'category_id' => 1,
                'category_slug' => 'laptop-gaming',
                'category_name' => 'Laptop Gaming',
                'brand_id' => 1,
                'brand_name' => 'ASUS',
                'stock' => 15,
                'is_flash_sale' => 1,
                'is_best_seller' => 1,
                'rating' => 4.9,
                'rating_count' => 32,
                'status' => 'active',
                'description' => 'Laptop Gaming cao cấp ASUS ROG Strix G16 màn hình 16 inch 165Hz sắc nét, cấu hình RTX 4060 mạnh mẽ.',
            ],
            [
                'id' => 2,
                'name' => 'Laptop Gaming MSI Katana 15 B13VFK i7-13620H / RTX 4060 8GB / 16GB RAM',
                'slug' => 'msi-katana-15-b13vfk',
                'price' => 28990000,
                'sale_price' => 25990000,
                'discount_price' => 25990000,
                'image' => 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=500&auto=format&fit=crop&q=60',
                'category_id' => 1,
                'category_slug' => 'laptop-gaming',
                'category_name' => 'Laptop Gaming',
                'brand_id' => 2,
                'brand_name' => 'MSI',
                'stock' => 20,
                'is_flash_sale' => 1,
                'is_best_seller' => 1,
                'rating' => 4.8,
                'rating_count' => 28,
                'status' => 'active',
                'description' => 'Thiết kế chuẩn gaming MSI Katana 15 hệ thống tản nhiệt Cooler Boost 5 mát mẻ.',
            ],
            [
                'id' => 3,
                'name' => 'Apple MacBook Air M2 13.6 inch (8GB RAM / 256GB SSD) - Chính hãng Apple',
                'slug' => 'apple-macbook-air-m2-136',
                'price' => 26990000,
                'sale_price' => 23990000,
                'discount_price' => 23990000,
                'image' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=500&auto=format&fit=crop&q=60',
                'category_id' => 2,
                'category_slug' => 'laptop-van-phong',
                'category_name' => 'Laptop Văn Phòng',
                'brand_id' => 12,
                'brand_name' => 'Apple',
                'stock' => 12,
                'is_flash_sale' => 1,
                'is_best_seller' => 1,
                'rating' => 5.0,
                'rating_count' => 45,
                'status' => 'active',
                'description' => 'MacBook Air M2 mỏng nhẹ đẳng cấp, thời lượng pin ấn tượng lên đến 18 giờ liên tục.',
            ],
            [
                'id' => 4,
                'name' => 'PC Gaming TechPilot Ultra i7-14700K / RTX 4070 Ti Super 16GB / 32GB DDR5 / 1TB SSD',
                'slug' => 'pc-gaming-techpilot-ultra-rtx4070ti',
                'price' => 45990000,
                'sale_price' => 41990000,
                'discount_price' => 41990000,
                'image' => 'https://images.unsplash.com/photo-1587202372775-e229f172b9d7?w=500&auto=format&fit=crop&q=60',
                'category_id' => 3,
                'category_slug' => 'pc-build-san',
                'category_name' => 'PC Bán Sẵn',
                'brand_id' => 1,
                'brand_name' => 'TechPilot',
                'stock' => 8,
                'is_flash_sale' => 1,
                'is_best_seller' => 1,
                'rating' => 4.9,
                'rating_count' => 19,
                'status' => 'active',
                'description' => 'Bộ máy tính PC Gaming lắp sẵn TechPilot chiến mượt mọi tựa game AAA ở độ phân giải 4K.',
            ],
            [
                'id' => 5,
                'name' => 'Card màn hình GIGABYTE GeForce RTX 4070 SUPER WINDFORCE OC 12G',
                'slug' => 'vga-gigabyte-rtx-4070-super-windforce-oc-12g',
                'price' => 18490000,
                'sale_price' => 16990000,
                'discount_price' => 16990000,
                'image' => 'https://images.unsplash.com/photo-1591799264318-7e6ef8ddb7ea?w=500&auto=format&fit=crop&q=60',
                'category_id' => 4,
                'category_slug' => 'pc-linh-kien',
                'category_name' => 'Linh Kiện PC',
                'brand_id' => 3,
                'brand_name' => 'GIGABYTE',
                'stock' => 10,
                'is_flash_sale' => 1,
                'is_best_seller' => 1,
                'rating' => 4.9,
                'rating_count' => 15,
                'status' => 'active',
                'description' => 'Card đồ họa VGA RTX 4070 Super hiệu năng đồ họa khủng, trang bị kiến trúc Ada Lovelace mới nhất.',
            ],
            [
                'id' => 6,
                'name' => 'Màn hình Gaming ASUS ROG Swift PG27AQDM 27 inch OLED 240Hz 0.03ms 2K',
                'slug' => 'man-hinh-asus-rog-swift-pg27aqdm-oled-240hz',
                'price' => 22990000,
                'sale_price' => 20490000,
                'discount_price' => 20490000,
                'image' => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=500&auto=format&fit=crop&q=60',
                'category_id' => 6,
                'category_slug' => 'man-hinh',
                'category_name' => 'Màn Hình',
                'brand_id' => 1,
                'brand_name' => 'ASUS',
                'stock' => 14,
                'is_flash_sale' => 1,
                'is_best_seller' => 1,
                'rating' => 4.9,
                'rating_count' => 22,
                'status' => 'active',
                'description' => 'Màn hình OLED 240Hz thời gian phản hồi siêu tốc 0.03ms chuyên nghiệp cho các game thủ Esports.',
            ],
            [
                'id' => 7,
                'name' => 'Bàn phím cơ không dây Logitech G Pro X TKL LIGHTSPEED Tactile RGB',
                'slug' => 'ban-phim-co-logitech-g-pro-x-tkl-lightspeed',
                'price' => 4490000,
                'sale_price' => 3890000,
                'discount_price' => 3890000,
                'image' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=500&auto=format&fit=crop&q=60',
                'category_id' => 5,
                'category_slug' => 'gaming-gear',
                'category_name' => 'Gaming Gear',
                'brand_id' => 13,
                'brand_name' => 'Logitech',
                'stock' => 25,
                'is_flash_sale' => 0,
                'is_best_seller' => 1,
                'rating' => 4.8,
                'rating_count' => 40,
                'status' => 'active',
                'description' => 'Bàn phím cơ Logitech kết nối không dây Lightspeed độ trễ cực thấp.',
            ],
            [
                'id' => 8,
                'name' => 'Chuột Gaming không dây Razer DeathAdder V3 Pro Wireless Black 63g 30K DPI',
                'slug' => 'chuot-razer-deathadder-v3-pro-wireless',
                'price' => 3790000,
                'sale_price' => 3290000,
                'discount_price' => 3290000,
                'image' => 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?w=500&auto=format&fit=crop&q=60',
                'category_id' => 5,
                'category_slug' => 'gaming-gear',
                'category_name' => 'Gaming Gear',
                'brand_id' => 7,
                'brand_name' => 'Razer',
                'stock' => 30,
                'is_flash_sale' => 0,
                'is_best_seller' => 1,
                'rating' => 5.0,
                'rating_count' => 52,
                'status' => 'active',
                'description' => 'Chuột Razer siêu nhẹ 63g cảm biến Focus Pro 30K DPI chính xác tuyệt đối.',
            ],
            [
                'id' => 9,
                'name' => 'Máy tính đồng bộ Apple Mac Studio M2 Max 12-core CPU 30-core GPU 32GB 512GB',
                'slug' => 'apple-mac-studio-m2-max',
                'price' => 52990000,
                'sale_price' => 48990000,
                'discount_price' => 48990000,
                'image' => 'https://images.unsplash.com/photo-1541807084-5c52b6b3adef?w=500&auto=format&fit=crop&q=60',
                'category_id' => 7,
                'category_slug' => 'may-tinh-bo',
                'category_name' => 'Máy Tính Bộ',
                'brand_id' => 12,
                'brand_name' => 'Apple',
                'stock' => 5,
                'is_flash_sale' => 0,
                'is_best_seller' => 1,
                'rating' => 4.9,
                'rating_count' => 11,
                'status' => 'active',
                'description' => 'Máy tính để bàn Mac Studio M2 Max dành cho các nhà thiết kế đồ họa & dựng phim chuyên nghiệp.',
            ]
        ];
    }

    /** Lấy toàn bộ sản phẩm, có thể giới hạn số lượng */
    public function getAll(int $limit = 100): array
    {
        if ($this->db !== null) {
            try {
                $stmt = $this->db->prepare('SELECT p.*, b.name as brand_name, c.name as category_name, c.slug as category_slug FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN brands b ON p.brand_id = b.id ORDER BY p.id DESC LIMIT :limit');
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();
                $res = $stmt->fetchAll();
                if (!empty($res)) {
                    return $res;
                }
            } catch (Exception $e) {}
        }

        return array_slice(self::getSampleProducts(), 0, $limit);
    }

    public function getFlashSale(int $limit = 6): array
    {
        if ($this->db !== null) {
            try {
                $stmt = $this->db->prepare(
                    'SELECT p.*, COALESCE(p.sale_price, p.price * 0.85) as discount_price, p.stock as fs_stock, 
                            0 as fs_sold, COALESCE(fs.end_time, DATE_ADD(NOW(), INTERVAL 1 DAY)) as end_time 
                     FROM products p
                     LEFT JOIN flash_sales fs ON fs.status = \'active\' AND fs.start_time <= NOW() AND fs.end_time >= NOW()
                     WHERE (p.is_flash_sale = 1 OR p.sale_price IS NOT NULL)
                     ORDER BY p.id DESC LIMIT :limit'
                );
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();
                $res = $stmt->fetchAll();
                if (!empty($res)) {
                    return $res;
                }
            } catch (Exception $e) {}
        }

        $samples = array_filter(self::getSampleProducts(), fn($p) => !empty($p['is_flash_sale']));
        return array_slice(array_values($samples), 0, $limit);
    }

    /** Lấy sản phẩm theo slug danh mục (hỗ trợ cả danh mục con) */
    public function getByCategorySlug(string $slug, int $limit = 6): array
    {
        if ($this->db !== null) {
            try {
                $stmt = $this->db->prepare(
                    'SELECT p.*, c.name as category_name, c.slug as category_slug, b.name as brand_name 
                     FROM products p
                     JOIN categories c ON p.category_id = c.id
                     LEFT JOIN brands b ON p.brand_id = b.id
                     WHERE c.slug = :slug OR c.parent_id IN (SELECT id FROM categories WHERE slug = :slug)
                     ORDER BY p.id DESC LIMIT :limit'
                );
                $stmt->bindValue(':slug', $slug);
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();
                $res = $stmt->fetchAll();
                if (!empty($res)) {
                    return $res;
                }
            } catch (Exception $e) {}
        }

        $samples = array_filter(self::getSampleProducts(), fn($p) => ($p['category_slug'] ?? '') === $slug);
        if (empty($samples)) {
            // Trường hợp chưa match đúng slug sample, trả về mảng cắt từ sample products để UI luôn sinh động
            return array_slice(self::getSampleProducts(), 0, $limit);
        }
        return array_slice(array_values($samples), 0, $limit);
    }

    /** Lấy 1 sản phẩm theo id */
    public function getById(int $id): array|false
    {
        if ($this->db !== null) {
            try {
                $stmt = $this->db->prepare(
                    'SELECT p.*, c.name as category_name, c.slug as category_slug, b.name as brand_name 
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     LEFT JOIN brands b ON p.brand_id = b.id 
                     WHERE p.id = :id LIMIT 1'
                );
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $res = $stmt->fetch();
                if ($res) {
                    return $res;
                }
            } catch (Exception $e) {}
        }

        foreach (self::getSampleProducts() as $p) {
            if ($p['id'] == $id) {
                return $p;
            }
        }
        return self::getSampleProducts()[0] ?? false;
    }

    /** Lấy 1 sản phẩm theo slug (trang chi tiết) */
    public function getBySlug(string $slug): array|false
    {
        if ($this->db !== null) {
            try {
                $stmt = $this->db->prepare(
                    'SELECT p.*, c.name as category_name, c.slug as category_slug, b.name as brand_name 
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     LEFT JOIN brands b ON p.brand_id = b.id 
                     WHERE p.slug = :slug LIMIT 1'
                );
                $stmt->bindValue(':slug', $slug);
                $stmt->execute();
                $res = $stmt->fetch();
                if ($res) {
                    return $res;
                }
            } catch (Exception $e) {}
        }

        foreach (self::getSampleProducts() as $p) {
            if ($p['slug'] === $slug) {
                return $p;
            }
        }
        return self::getSampleProducts()[0] ?? false;
    }

    /** Lấy danh sách ảnh phụ từ product_images */
    public function getProductImages(int $productId): array
    {
        if ($this->db !== null) {
            try {
                $stmt = $this->db->prepare('SELECT * FROM product_images WHERE product_id = :product_id');
                $stmt->execute([':product_id' => $productId]);
                return $stmt->fetchAll();
            } catch (Exception $e) {}
        }
        return [];
    }

    /** Lấy sản phẩm liên quan (cùng danh mục, khác id hiện tại) */
    public function getRelated(int $categoryId, int $excludeId, int $limit = 4): array
    {
        if ($this->db !== null) {
            try {
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
                $res = $stmt->fetchAll();
                if (!empty($res)) {
                    return $res;
                }
            } catch (Exception $e) {}
        }

        $samples = array_filter(self::getSampleProducts(), fn($p) => $p['id'] != $excludeId);
        return array_slice(array_values($samples), 0, $limit);
    }

    /** Danh sách toàn bộ danh mục */
    public function getCategories(): array
    {
        if ($this->db !== null) {
            try {
                $stmt = $this->db->query('SELECT * FROM categories ORDER BY id ASC');
                $res = $stmt->fetchAll();
                if (!empty($res)) {
                    return $res;
                }
            } catch (Exception $e) {}
        }

        return [
            ['id' => 1, 'name' => 'Laptop Gaming', 'slug' => 'laptop-gaming', 'icon' => 'fa-solid fa-laptop-code'],
            ['id' => 2, 'name' => 'Laptop Văn Phòng', 'slug' => 'laptop-van-phong', 'icon' => 'fa-solid fa-laptop'],
            ['id' => 3, 'name' => 'PC Bán Sẵn', 'slug' => 'pc-build-san', 'icon' => 'fa-solid fa-desktop'],
            ['id' => 4, 'name' => 'Linh Kiện PC', 'slug' => 'pc-linh-kien', 'icon' => 'fa-solid fa-microchip'],
            ['id' => 5, 'name' => 'Gaming Gear', 'slug' => 'gaming-gear', 'icon' => 'fa-solid fa-gamepad'],
            ['id' => 6, 'name' => 'Màn Hình', 'slug' => 'man-hinh', 'icon' => 'fa-solid fa-tv'],
            ['id' => 7, 'name' => 'Máy Tính Bộ', 'slug' => 'may-tinh-bo', 'icon' => 'fa-solid fa-server'],
        ];
    }

    /** Lấy sản phẩm best seller hoặc phân loại theo tab cho Best Sellers */
    public function getBestSellersByTab(string $tab, int $limit = 6): array
    {
        if ($this->db !== null) {
            try {
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
                        WHERE (c.slug IN ($inQuery) OR c.parent_id IN (SELECT id FROM categories WHERE slug IN ($inQuery)))
                        ORDER BY p.id DESC LIMIT ?";

                $stmt = $this->db->prepare($sql);
                foreach ($slugs as $k => $slug) {
                    $stmt->bindValue($k + 1, $slug);
                }
                $stmt->bindValue(count($slugs) + 1, $limit, PDO::PARAM_INT);
                $stmt->execute();
                $res = $stmt->fetchAll();
                if (!empty($res)) {
                    return $res;
                }
            } catch (Exception $e) {}
        }

        return array_slice(self::getSampleProducts(), 0, $limit);
    }

    public function getBestSellers(int $limit = 6): array
    {
        if ($this->db !== null) {
            try {
                $stmt = $this->db->prepare('SELECT p.*, b.name as brand_name FROM products p LEFT JOIN brands b ON p.brand_id = b.id WHERE p.is_best_seller = 1 ORDER BY p.id DESC LIMIT :limit');
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();
                $res = $stmt->fetchAll();
                if (!empty($res)) {
                    return $res;
                }
            } catch (Exception $e) {}
        }

        return array_slice(self::getSampleProducts(), 0, $limit);
    }

    public function getNewArrivals(int $limit = 6): array
    {
        if ($this->db !== null) {
            try {
                $stmt = $this->db->prepare('SELECT p.*, b.name as brand_name FROM products p LEFT JOIN brands b ON p.brand_id = b.id WHERE p.is_new_arrival = 1 ORDER BY p.id DESC LIMIT :limit');
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();
                $res = $stmt->fetchAll();
                if (!empty($res)) {
                    return $res;
                }
            } catch (Exception $e) {}
        }

        return array_slice(self::getSampleProducts(), 0, $limit);
    }

    public function getAiRecommend(int $limit = 6): array
    {
        if ($this->db !== null) {
            try {
                $stmt = $this->db->prepare('SELECT p.*, b.name as brand_name FROM products p LEFT JOIN brands b ON p.brand_id = b.id WHERE p.is_ai_recommend = 1 ORDER BY p.id DESC LIMIT :limit');
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();
                $res = $stmt->fetchAll();
                if (!empty($res)) {
                    return $res;
                }
            } catch (Exception $e) {}
        }

        return array_slice(self::getSampleProducts(), 0, $limit);
    }

    /** Tìm kiếm sản phẩm theo từ khóa và danh mục */
    public function search(string $keyword = '', string $categorySlug = '', int $limit = 24): array
    {
        if ($this->db !== null) {
            try {
                $keyword = normalizeSearchKeyword($keyword);

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

                    $aliasIdx = 1;
                    foreach ($aliasesFound as $alias) {
                        $aliasParam = ':alias_' . $aliasIdx;
                        $relevanceSql .= " + (CASE WHEN p.name LIKE $aliasParam THEN 35 ELSE 0 END)";
                        $params[$aliasParam] = '%' . $alias . '%';
                        $aliasIdx++;
                    }
                }

                $query = "
                    SELECT p.*, b.name as brand_name, c.name as category_name, ($relevanceSql) as relevance
                    FROM products p
                    LEFT JOIN brands b ON p.brand_id = b.id
                    JOIN categories c ON p.category_id = c.id
                    WHERE p.status = 'active'
                ";

                if (!empty($keyword)) {
                    $query .= ' AND (p.name LIKE :filterName OR p.description LIKE :filterDesc)';
                    $params[':filterName'] = '%' . $keyword . '%';
                    $params[':filterDesc'] = '%' . $keyword . '%';
                }

                if (!empty($categorySlug)) {
                    $query .= ' AND (c.slug = :category OR c.parent_id IN (SELECT id FROM categories WHERE slug = :category))';
                    $params[':category'] = $categorySlug;
                }

                if (!empty($keyword)) {
                    $query .= ' ORDER BY relevance DESC, p.created_at DESC';
                } else {
                    $query .= ' ORDER BY p.id DESC';
                }

                $query .= ' LIMIT :limit';

                $stmt = $this->db->prepare($query);
                foreach ($params as $key => $val) {
                    $stmt->bindValue($key, $val);
                }
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();

                $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($res)) {
                    return $res;
                }
            } catch (Exception $e) {}
        }

        // Fallback search in sample products
        $all = self::getSampleProducts();
        if (!empty($categorySlug)) {
            $all = array_filter($all, fn($p) => ($p['category_slug'] ?? '') === $categorySlug);
        }
        if (!empty($keyword)) {
            $kw = mb_strtolower($keyword);
            $all = array_filter($all, fn($p) => str_contains(mb_strtolower($p['name']), $kw));
        }
        return array_slice(array_values($all), 0, $limit);
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
        if (empty($ids)) return [];

        if ($this->db !== null) {
            try {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $this->db->prepare(
                    "SELECT p.*, b.name as brand_name, c.name as category_name 
                     FROM products p
                     LEFT JOIN brands b ON p.brand_id = b.id
                     LEFT JOIN categories c ON p.category_id = c.id
                     WHERE p.id IN ($placeholders) AND p.status = 'active'"
                );
                $stmt->execute(array_map('intval', $ids));
                $res = $stmt->fetchAll();
                if (!empty($res)) {
                    return $res;
                }
            } catch (Exception $e) {}
        }

        $res = array_filter(self::getSampleProducts(), fn($p) => in_array($p['id'], $ids));
        return array_values($res);
    }
}
