<?php

/**
 * Service quản lý 7 Virtual Catalog Groups tại Service Layer
 * Thống nhất dữ liệu điều hướng & danh mục mà không làm thay đổi CSDL CSDL
 */
class CatalogGroupService
{
    /**
     * Định nghĩa mảng 7 nhóm Virtual Catalog chuẩn
     */
    private static array $groupDefinitions = [
        'laptop' => [
            'key'                 => 'laptop',
            'name'                => 'Laptop',
            'canonical_slug'       => 'laptop-gaming', // Dùng canonical slug phổ biến cho URL storefront
            'virtual_slug'        => 'laptop',
            'icon'                => 'fa-solid fa-laptop',
            'source_category_ids' => [1, 2],
            'source_slugs'        => ['laptop-gaming', 'laptop-van-phong'],
            'aliases'             => ['laptop', 'laptop-gaming', 'laptop-van-phong', 'may-tinh-xach-tay', 'lap'],
        ],
        'pc' => [
            'key'                 => 'pc',
            'name'                => 'PC & Build PC',
            'canonical_slug'       => 'pc-build-san',
            'virtual_slug'        => 'pc',
            'icon'                => 'fa-solid fa-desktop',
            'source_category_ids' => [3, 6],
            'source_slugs'        => ['pc-build-san', 'may-tinh-bo'],
            'aliases'             => ['pc-build-san', 'may-tinh-bo', 'pc-gaming', 'pc-van-phong', 'pc', 'may-bo', 'may-tinh-de-ban'],
        ],
        'pc-linh-kien' => [
            'key'                 => 'pc-linh-kien',
            'name'                => 'Linh kiện PC',
            'canonical_slug'       => 'pc-linh-kien',
            'virtual_slug'        => 'pc-linh-kien',
            'icon'                => 'fa-solid fa-microchip',
            'source_category_ids' => [4, 10, 11, 12, 13, 14, 15, 16, 17, 18],
            'source_slugs'        => ['pc-linh-kien', 'cpu', 'mainboard', 'ram', 'vga', 'ssd', 'hdd', 'psu', 'case', 'tan-nhiet'],
            'aliases'             => ['pc-linh-kien', 'linh-kien-pc', 'linh-kien', 'cpu', 'mainboard', 'ram', 'vga', 'ssd', 'hdd', 'psu', 'case', 'tan-nhiet'],
        ],
        'man-hinh' => [
            'key'                 => 'man-hinh',
            'name'                => 'Màn hình',
            'canonical_slug'       => 'man-hinh',
            'virtual_slug'        => 'man-hinh',
            'icon'                => 'fa-solid fa-tv',
            'source_category_ids' => [5],
            'source_slugs'        => ['man-hinh'],
            'aliases'             => ['man-hinh', 'monitor'],
        ],
        'gaming-gear' => [
            'key'                 => 'gaming-gear',
            'name'                => 'Gaming Gear',
            'canonical_slug'       => 'gaming-gear',
            'virtual_slug'        => 'gaming-gear',
            'icon'                => 'fa-solid fa-gamepad',
            'source_category_ids' => [7],
            'source_slugs'        => ['gaming-gear'],
            'aliases'             => ['gaming-gear', 'gear'],
        ],
        'office-gear' => [
            'key'                 => 'office-gear',
            'name'                => 'Thiết bị văn phòng',
            'canonical_slug'       => 'office-gear',
            'virtual_slug'        => 'office-gear',
            'icon'                => 'fa-solid fa-print',
            'source_category_ids' => [8],
            'source_slugs'        => ['office-gear'],
            'aliases'             => ['office-gear', 'thiet-bi-van-phong'],
        ],
        'networking' => [
            'key'                 => 'networking',
            'name'                => 'Thiết bị mạng',
            'canonical_slug'       => 'networking',
            'virtual_slug'        => 'networking',
            'icon'                => 'fa-solid fa-wifi',
            'source_category_ids' => [9],
            'source_slugs'        => ['networking'],
            'aliases'             => ['networking', 'thiet-bi-mang'],
        ],
    ];

    /**
     * Lấy toàn bộ mảng Virtual Groups được hydrate dữ liệu thời gian thực từ CSDL
     */
    public static function getAllVirtualGroups(): array
    {
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if ($db === null) {
            return self::getFallbackGroups();
        }

        try {
            // Truy vấn danh mục CSDL
            $stmtCats = $db->query("SELECT id, parent_id, name, slug, status FROM categories WHERE status = 'active'");
            $allCats = $stmtCats->fetchAll(PDO::FETCH_ASSOC);

            // Truy vấn sản phẩm active kèm thương hiệu
            $stmtProds = $db->query("
                SELECT p.id, p.category_id, p.price, p.sale_price, b.id as brand_id, b.name as brand_name, b.slug as brand_slug
                FROM products p
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE p.status = 'active'
            ");
            $allProds = $stmtProds->fetchAll(PDO::FETCH_ASSOC);

            $result = [];

            foreach (self::$groupDefinitions as $key => $def) {
                $group = self::hydrateGroupData($def, $allCats, $allProds);
                $result[$key] = $group;
            }

            return $result;
        } catch (Exception $e) {
            error_log('CatalogGroupService error: ' . $e->getMessage());
            return self::getFallbackGroups();
        }
    }

    /**
     * Lấy danh sách các Virtual Groups sẵn sàng hiển thị trên Storefront Menu (count > 0, status = ready)
     */
    public static function getStorefrontGroups(): array
    {
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
     * Tra cứu Virtual Group theo slug hoặc alias bất kỳ
     */
    public static function getGroupBySlug(string $slug): ?array
    {
        $slug = strtolower(trim($slug));
        if ($slug === '') {
            return null;
        }

        $allGroups = self::getAllVirtualGroups();

        // 1. Khớp theo canonical_slug hoặc key
        foreach ($allGroups as $group) {
            if ($group['canonical_slug'] === $slug || $group['key'] === $slug || $group['virtual_slug'] === $slug) {
                return $group;
            }
        }

        // 2. Khớp trong source_slugs
        foreach ($allGroups as $group) {
            if (in_array($slug, $group['source_slugs'], true)) {
                return $group;
            }
        }

        // 3. Khớp trong aliases
        foreach ($allGroups as $group) {
            if (in_array($slug, $group['aliases'], true)) {
                return $group;
            }
        }

        return null;
    }

    /**
     * Tổng hợp và hydrate dữ liệu runtime cho 1 group definition
     */
    private static function hydrateGroupData(array $def, array $allCats, array $allProds): array
    {
        $sourceIds = $def['source_category_ids'];

        // Lọc sản phẩm thuộc các category source
        $groupProds = array_filter($allProds, function ($p) use ($sourceIds) {
            return in_array((int)$p['category_id'], $sourceIds, true);
        });

        $productCount = count($groupProds);
        $status = ($productCount > 0) ? 'ready' : 'not_ready';

        // Lấy thương hiệu duy nhất (không trùng lặp)
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
            if (in_array($catId, $sourceIds, true)) {
                // Kiểm tra số sản phẩm trong category này
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

        // Mức giá động phù hợp theo nhóm
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

        return array_merge($def, [
            'status'                 => $status,
            'product_count'          => $productCount,
            'subgroups'              => $subgroups,
            'brands'                 => $brands,
            'price_ranges'           => $priceRanges,
            'min_effective_price'    => $minPrice,
            'median_effective_price' => $medianPrice,
            'max_effective_price'    => $maxPrice,
        ]);
    }

    /**
     * Tạo khoảng giá linh hoạt và phù hợp theo loại Virtual Group
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

        // Cho Linh kiện PC
        return [
            ['name' => 'Dưới 2 triệu', 'query' => 'min_price=0&max_price=2000000'],
            ['name' => 'Từ 2 - 5 triệu', 'query' => 'min_price=2000000&max_price=5000000'],
            ['name' => 'Từ 5 - 10 triệu', 'query' => 'min_price=5000000&max_price=10000000'],
            ['name' => 'Trên 10 triệu', 'query' => 'min_price=10000000'],
        ];
    }

    /**
     * Safe Fallback khi CSDL không khả dụng
     */
    public static function getFallbackGroups(): array
    {
        $fallback = [];
        foreach (self::$groupDefinitions as $key => $def) {
            $isNotReady = ($key === 'networking');
            $fallback[$key] = array_merge($def, [
                'status'                 => $isNotReady ? 'not_ready' : 'ready',
                'product_count'          => 0,
                'subgroups'              => [],
                'brands'                 => [],
                'price_ranges'           => [],
                'min_effective_price'    => null,
                'median_effective_price' => null,
                'max_effective_price'    => null,
            ]);
        }
        return $fallback;
    }
}
