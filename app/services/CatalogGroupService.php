<?php

/**
 * Service quản lý 7 Virtual Catalog Groups tại Service Layer.
 * Single Source of Truth cho toàn bộ ánh xạ danh mục, virtual route và alias tìm kiếm.
 */
class CatalogGroupService
{
    /**
     * Contract định nghĩa 7 Virtual Catalog Groups cố định.
     * Nguồn sự thật duy nhất (Single Source of Truth) cho source_slugs & aliases.
     */
    private static array $groupDefinitions = [
        'laptop' => [
            'key'             => 'laptop',
            'name'            => 'Laptop',
            'virtual_slug'    => 'laptop',
            'icon'            => 'fa-solid fa-laptop',
            'source_slugs'    => ['laptop-gaming', 'laptop-van-phong'],
            'aliases'         => ['laptop', 'laptop-gaming', 'laptop-van-phong', 'may-tinh-xach-tay', 'lap'],
            'keyword_aliases' => ['laptop', 'laptop gaming', 'laptop van phong', 'laptop văn phòng', 'máy tính xách tay', 'may tinh xach tay', 'lap'],
        ],
        'pc' => [
            'key'             => 'pc',
            'name'            => 'PC & Build PC',
            'virtual_slug'    => 'pc',
            'icon'            => 'fa-solid fa-desktop',
            'source_slugs'    => ['pc-build-san', 'may-tinh-bo'],
            'aliases'         => ['pc', 'pc-build-san', 'may-tinh-bo', 'pc-gaming', 'pc-van-phong', 'may-bo', 'may-tinh-de-ban'],
            'keyword_aliases' => ['pc', 'máy tính để bàn', 'may tinh de ban', 'máy bộ', 'may bo', 'pc build sẵn', 'pc build san'],
        ],
        'pc-linh-kien' => [
            'key'             => 'pc-linh-kien',
            'name'            => 'Linh kiện PC',
            'virtual_slug'    => 'pc-linh-kien',
            'icon'            => 'fa-solid fa-microchip',
            'source_slugs'    => ['pc-linh-kien', 'cpu', 'mainboard', 'ram', 'vga', 'ssd', 'hdd', 'psu', 'case', 'tan-nhiet'],
            'aliases'         => ['pc-linh-kien', 'linh-kien-pc', 'linh-kien', 'cpu', 'mainboard', 'ram', 'vga', 'ssd', 'hdd', 'psu', 'case', 'tan-nhiet'],
            'keyword_aliases' => ['linh kiện', 'linh kien', 'linh kiện pc', 'linh kien pc'],
        ],
        'man-hinh' => [
            'key'             => 'man-hinh',
            'name'            => 'Màn hình',
            'virtual_slug'    => 'man-hinh',
            'icon'            => 'fa-solid fa-tv',
            'source_slugs'    => ['man-hinh'],
            'aliases'         => ['man-hinh', 'monitor'],
            'keyword_aliases' => ['màn hình', 'man hinh', 'monitor'],
        ],
        'gaming-gear' => [
            'key'             => 'gaming-gear',
            'name'            => 'Gaming Gear',
            'virtual_slug'    => 'gaming-gear',
            'icon'            => 'fa-solid fa-gamepad',
            'source_slugs'    => ['gaming-gear'],
            'aliases'         => ['gaming-gear', 'gear'],
            'keyword_aliases' => ['gaming gear', 'gear'],
        ],
        'office-gear' => [
            'key'             => 'office-gear',
            'name'            => 'Thiết bị văn phòng',
            'virtual_slug'    => 'office-gear',
            'icon'            => 'fa-solid fa-print',
            'source_slugs'    => ['office-gear'],
            'aliases'         => ['office-gear', 'thiet-bi-van-phong'],
            'keyword_aliases' => ['thiết bị văn phòng', 'thiet bi van phong', 'văn phòng', 'van phong'],
        ],
        'networking' => [
            'key'             => 'networking',
            'name'            => 'Thiết bị mạng',
            'virtual_slug'    => 'networking',
            'icon'            => 'fa-solid fa-wifi',
            'source_slugs'    => ['networking'],
            'aliases'         => ['networking', 'thiet-bi-mang'],
            'keyword_aliases' => ['thiết bị mạng', 'thiet bi mang', 'mạng', 'mang'],
        ],
    ];

    /** Connection Provider Seam cho testing / DI */
    private static mixed $customConnectionProvider = null;

    /**
     * Thiết lập custom Connection Provider dùng cho testing / dependency injection
     */
    public static function setConnectionProvider(?callable $provider): void
    {
        self::$customConnectionProvider = $provider;
    }

    /**
     * Lấy PDO connection (thông qua provider hoăc mặc định từ Database)
     */
    private static function getConnection(): ?PDO
    {
        if (self::$customConnectionProvider !== null) {
            return (self::$customConnectionProvider)();
        }

        require_once ROOT_PATH . '/config/database.php';
        return Database::getConnection();
    }

    /**
     * 1. Resolver API: Tìm key của Virtual Group từ slug hoặc alias bất kỳ
     */
    public static function resolveGroupKey(string $slugOrAlias): ?string
    {
        $input = strtolower(trim($slugOrAlias));
        if ($input === '') {
            return null;
        }

        foreach (self::$groupDefinitions as $key => $def) {
            if (
                $key === $input ||
                $def['virtual_slug'] === $input ||
                in_array($input, $def['source_slugs'], true) ||
                in_array($input, $def['aliases'], true) ||
                in_array($input, $def['keyword_aliases'], true)
            ) {
                return $key;
            }
        }

        return null;
    }

    /**
     * 2. Resolver API: Chuyển đổi slug/alias thành danh sách source_slugs hợp lệ cho SQL query
     */
    public static function resolveSourceSlugs(string $slugOrAlias): array
    {
        $input = strtolower(trim($slugOrAlias));
        if ($input === '') {
            return [];
        }

        $groupKey = self::resolveGroupKey($input);
        if ($groupKey !== null && isset(self::$groupDefinitions[$groupKey])) {
            return self::$groupDefinitions[$groupKey]['source_slugs'];
        }

        // Nếu không khớp group nào, giữ nguyên slug truyền vào
        return [$input];
    }

    /**
     * 3. Resolver API: Lấy định nghĩa tĩnh (static contract) của một Virtual Group theo key
     */
    public static function getStaticGroupDefinition(string $key): ?array
    {
        $groupKey = self::resolveGroupKey($key) ?? $key;
        return self::$groupDefinitions[$groupKey] ?? null;
    }

    /**
     * 4. Resolver API: Lấy Tên hiển thị chuẩn (Human-readable Display Name)
     */
    public static function getDisplayName(string $slugOrAlias): string
    {
        $groupKey = self::resolveGroupKey($slugOrAlias);
        if ($groupKey !== null && isset(self::$groupDefinitions[$groupKey])) {
            return self::$groupDefinitions[$groupKey]['name'];
        }

        // Fallback tên nếu là danh mục đơn từ DB
        $db = self::getConnection();
        if ($db !== null) {
            try {
                $stmt = $db->prepare("SELECT name FROM categories WHERE slug = :slug AND status = 'active'");
                $stmt->execute([':slug' => $slugOrAlias]);
                $catName = $stmt->fetchColumn();
                if ($catName) {
                    return $catName;
                }
            } catch (Exception $e) {}
        }

        return ucfirst(str_replace('-', ' ', $slugOrAlias));
    }

    /**
     * Tra cứu tất cả keyword aliases để phục vụ Product search mapping
     */
    public static function getKeywordAliasMap(): array
    {
        $map = [];
        foreach (self::$groupDefinitions as $def) {
            $sourceSlugs = $def['source_slugs'];
            foreach ($def['keyword_aliases'] as $kw) {
                $map[$kw] = $sourceSlugs;
            }
        }
        return $map;
    }

    /**
     * Lấy toàn bộ Virtual Groups được hydrate dữ liệu thời gian thực từ CSDL
     */
    public static function getAllVirtualGroups(): array
    {
        $db = self::getConnection();
        if ($db === null) {
            return self::getFallbackGroups();
        }

        try {
            // Lấy danh mục active và map slug -> id linh hoạt
            $stmtCats = $db->query("SELECT id, parent_id, name, slug, status FROM categories WHERE status = 'active'");
            $allCats = $stmtCats->fetchAll(PDO::FETCH_ASSOC);

            if (empty($allCats)) {
                return self::getFallbackGroups();
            }

            // Lấy sản phẩm active thuộc các category active
            $stmtProds = $db->query("
                SELECT p.id, p.category_id, p.price, p.sale_price, b.id as brand_id, b.name as brand_name, b.slug as brand_slug
                FROM products p
                JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE p.status = 'active' AND c.status = 'active'
            ");
            $allProds = $stmtProds->fetchAll(PDO::FETCH_ASSOC);

            $result = [];

            foreach (self::$groupDefinitions as $key => $def) {
                $result[$key] = self::hydrateGroupData($def, $allCats, $allProds);
            }

            return $result;
        } catch (Exception $e) {
            error_log('CatalogGroupService error: ' . $e->getMessage());
            return self::getFallbackGroups();
        }
    }

    /**
     * Lấy danh sách các Virtual Groups sẵn sàng hiển thị trên Storefront Menu
     */
    public static function getStorefrontGroups(): array
    {
        $db = self::getConnection();
        if ($db === null) {
            return []; // Mảng rỗng an toàn khi DB unavailable
        }

        $allGroups = self::getAllVirtualGroups();
        $storefront = [];

        foreach ($allGroups as $group) {
            if (($group['status'] ?? 'not_ready') === 'ready' && ($group['product_count'] ?? 0) > 0) {
                $storefront[] = $group;
            }
        }

        return $storefront;
    }

    /**
     * Hydrate dữ liệu CSDL runtime cho 1 group definition
     */
    private static function hydrateGroupData(array $def, array $allCats, array $allProds): array
    {
        $sourceSlugs = $def['source_slugs'];

        // Map động source_slugs thành source_category_ids từ DB
        $sourceCatIds = [];
        $catMap = [];
        foreach ($allCats as $cat) {
            $catMap[$cat['id']] = $cat;
            if (in_array($cat['slug'], $sourceSlugs, true)) {
                $sourceCatIds[] = (int)$cat['id'];
            }
        }

        // Lọc sản phẩm thuộc các source_category_ids
        $groupProds = array_filter($allProds, function ($p) use ($sourceCatIds) {
            return in_array((int)$p['category_id'], $sourceCatIds, true);
        });

        $productCount = count($groupProds);
        $status = ($productCount > 0) ? 'ready' : 'not_ready';

        // Lấy thương hiệu duy nhất
        $brandMap = [];
        foreach ($groupProds as $p) {
            if (!empty($p['brand_name']) && !empty($p['brand_slug'])) {
                $bSlug = $p['brand_slug'];
                if (!isset($brandMap[$bSlug])) {
                    $brandMap[$bSlug] = [
                        'name'  => $p['brand_name'],
                        'slug'  => $bSlug,
                        'query' => 'brand=' . urlencode($bSlug),
                    ];
                }
            }
        }
        ksort($brandMap);
        $brands = array_values($brandMap);

        // Lấy subgroups thực sự có sản phẩm (>0)
        $subgroups = [];
        foreach ($allCats as $cat) {
            $catId = (int)$cat['id'];
            if (in_array($catId, $sourceCatIds, true)) {
                $subProds = array_filter($groupProds, fn($p) => (int)$p['category_id'] === $catId);
                $subCount = count($subProds);
                if ($subCount > 0) {
                    $subgroups[] = [
                        'id'            => $catId,
                        'name'          => $cat['name'],
                        'slug'          => $cat['slug'],
                        'product_count' => $subCount,
                    ];
                }
            }
        }

        // Mức giá effective
        $effectivePrices = array_map(function ($p) {
            $sale = (float)($p['sale_price'] ?? 0);
            $orig = (float)($p['price'] ?? 0);
            return ($sale > 0) ? $sale : $orig;
        }, $groupProds);

        sort($effectivePrices);

        $minPrice = !empty($effectivePrices) ? min($effectivePrices) : null;
        $maxPrice = !empty($effectivePrices) ? max($effectivePrices) : null;
        $medianPrice = null;
        if (!empty($effectivePrices)) {
            $cnt = count($effectivePrices);
            if ($cnt % 2 === 1) {
                $medianPrice = $effectivePrices[floor($cnt / 2)];
            } else {
                $medianPrice = ($effectivePrices[($cnt / 2) - 1] + $effectivePrices[$cnt / 2]) / 2;
            }
        }

        $priceRanges = self::generatePriceRanges($def['key'], $minPrice, $maxPrice);

        return [
            'key'                     => $def['key'],
            'name'                    => $def['name'],
            'canonical_slug'           => $def['virtual_slug'], // Virtual slug làm link đại diện duy nhất
            'virtual_slug'            => $def['virtual_slug'],
            'icon'                    => $def['icon'],
            'source_category_ids'     => array_values(array_unique($sourceCatIds)),
            'source_slugs'            => $sourceSlugs,
            'aliases'                 => $def['aliases'],
            'status'                  => $status,
            'product_count'           => $productCount,
            'subgroups'               => $subgroups,
            'brands'                  => $brands,
            'price_ranges'            => $priceRanges,
            'min_effective_price'    => $minPrice,
            'median_effective_price' => $medianPrice,
            'max_effective_price'    => $maxPrice,
        ];
    }

    /**
     * Tạo mảng khoảng giá linh hoạt theo nhóm
     */
    private static function generatePriceRanges(string $groupKey, ?float $minPrice, ?float $maxPrice): array
    {
        if ($minPrice === null || $maxPrice === null) {
            return [];
        }

        if ($groupKey === 'laptop' || $groupKey === 'pc') {
            return [
                ['name' => 'Dưới 15 triệu', 'query' => 'min_price=0&max_price=15000000'],
                ['name' => 'Từ 15 - 20 triệu', 'query' => 'min_price=15000000&max_price=20000000'],
                ['name' => 'Từ 20 - 30 triệu', 'query' => 'min_price=20000000&max_price=30000000'],
                ['name' => 'Trên 30 triệu', 'query' => 'min_price=30000000'],
            ];
        }

        if ($groupKey === 'man-hinh') {
            return [
                ['name' => 'Dưới 5 triệu', 'query' => 'min_price=0&max_price=5000000'],
                ['name' => 'Từ 5 - 10 triệu', 'query' => 'min_price=5000000&max_price=10000000'],
                ['name' => 'Trên 10 triệu', 'query' => 'min_price=10000000'],
            ];
        }

        if ($groupKey === 'gaming-gear' || $groupKey === 'office-gear') {
            return [
                ['name' => 'Dưới 2 triệu', 'query' => 'min_price=0&max_price=2000000'],
                ['name' => 'Từ 2 - 5 triệu', 'query' => 'min_price=2000000&max_price=5000000'],
                ['name' => 'Trên 5 triệu', 'query' => 'min_price=5000000'],
            ];
        }

        return [
            ['name' => 'Dưới 2 triệu', 'query' => 'min_price=0&max_price=2000000'],
            ['name' => 'Từ 2 - 5 triệu', 'query' => 'min_price=2000000&max_price=5000000'],
            ['name' => 'Từ 5 - 10 triệu', 'query' => 'min_price=5000000&max_price=10000000'],
            ['name' => 'Trên 10 triệu', 'query' => 'min_price=10000000'],
        ];
    }

    /**
     * Fallback Contract an toàn khi database unavailable.
     * KHÔNG group nào có status = ready khi DB ngắt kết nối.
     */
    public static function getFallbackGroups(): array
    {
        $fallback = [];
        foreach (self::$groupDefinitions as $key => $def) {
            $fallback[$key] = [
                'key'                     => $def['key'],
                'name'                    => $def['name'],
                'canonical_slug'           => $def['virtual_slug'],
                'virtual_slug'            => $def['virtual_slug'],
                'icon'                    => $def['icon'],
                'source_category_ids'     => [],
                'source_slugs'            => $def['source_slugs'],
                'aliases'                 => $def['aliases'],
                'status'                  => 'unavailable', // Không bao giờ trả ready khi DB unavailable
                'product_count'          => 0,
                'subgroups'              => [],
                'brands'                 => [],
                'price_ranges'           => [],
                'min_effective_price'    => null,
                'median_effective_price' => null,
                'max_effective_price'    => null,
            ];
        }
        return $fallback;
    }
}
