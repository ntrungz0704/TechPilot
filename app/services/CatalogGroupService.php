<?php

/**
 * Service quản lý 7 Virtual Catalog Groups tại Service Layer.
 * Single Source of Truth cho toàn bộ ánh xạ danh mục, virtual route và alias tìm kiếm.
 */
class CatalogGroupService
{
    /**
     * Contract định nghĩa 7 Virtual Catalog Groups cố định.
     */
    private static array $groupDefinitions = [
        'laptop' => [
            'key'             => 'laptop',
            'name'            => 'Laptop',
            'virtual_slug'    => 'laptop',
            'icon'            => 'fa-solid fa-laptop',
            'source_slugs'    => ['laptop-gaming', 'laptop-van-phong'],
            'group_aliases'   => ['laptop', 'may-tinh-xach-tay', 'lap'],
        ],
        'pc' => [
            'key'             => 'pc',
            'name'            => 'PC & Build PC',
            'virtual_slug'    => 'pc',
            'icon'            => 'fa-solid fa-desktop',
            'source_slugs'    => ['pc-build-san', 'may-tinh-bo'],
            'group_aliases'   => ['pc', 'pc-gaming', 'pc-van-phong', 'may-bo', 'may-tinh-de-ban'],
        ],
        'pc-linh-kien' => [
            'key'             => 'pc-linh-kien',
            'name'            => 'Linh kiện PC',
            'virtual_slug'    => 'pc-linh-kien',
            'icon'            => 'fa-solid fa-microchip',
            'source_slugs'    => ['pc-linh-kien', 'cpu', 'mainboard', 'ram', 'vga', 'ssd', 'hdd', 'psu', 'case', 'tan-nhiet'],
            'group_aliases'   => ['pc-linh-kien', 'linh-kien-pc', 'linh-kien'],
        ],
        'man-hinh' => [
            'key'             => 'man-hinh',
            'name'            => 'Màn hình',
            'virtual_slug'    => 'man-hinh',
            'icon'            => 'fa-solid fa-tv',
            'source_slugs'    => ['man-hinh'],
            'group_aliases'   => ['man-hinh', 'monitor'],
        ],
        'gaming-gear' => [
            'key'             => 'gaming-gear',
            'name'            => 'Gaming Gear',
            'virtual_slug'    => 'gaming-gear',
            'icon'            => 'fa-solid fa-gamepad',
            'source_slugs'    => ['gaming-gear'],
            'group_aliases'   => ['gaming-gear', 'gear'],
        ],
        'office-gear' => [
            'key'             => 'office-gear',
            'name'            => 'Thiết bị văn phòng',
            'virtual_slug'    => 'office-gear',
            'icon'            => 'fa-solid fa-print',
            'source_slugs'    => ['office-gear'],
            'group_aliases'   => ['office-gear', 'thiet-bi-van-phong'],
        ],
        'networking' => [
            'key'             => 'networking',
            'name'            => 'Thiết bị mạng',
            'virtual_slug'    => 'networking',
            'icon'            => 'fa-solid fa-wifi',
            'source_slugs'    => ['networking'],
            'group_aliases'   => ['networking', 'thiet-bi-mang'],
        ],
    ];

    /**
     * Map keyword search cố định có target riêng (Single Source of Truth)
     */
    private static array $keywordAliasMap = [
        'laptop'            => ['laptop-gaming', 'laptop-van-phong'],
        'laptop gaming'     => ['laptop-gaming'],
        'laptop van phong'  => ['laptop-van-phong'],
        'laptop văn phòng'  => ['laptop-van-phong'],
        'máy tính xách tay' => ['laptop-gaming', 'laptop-van-phong'],
        'may tinh xach tay' => ['laptop-gaming', 'laptop-van-phong'],
        'lap'               => ['laptop-gaming', 'laptop-van-phong'],

        'pc'                => ['pc-build-san', 'may-tinh-bo'],
        'pc build sẵn'      => ['pc-build-san'],
        'pc build san'      => ['pc-build-san'],
        'máy bộ'            => ['pc-build-san', 'may-tinh-bo'],
        'may bo'            => ['pc-build-san', 'may-tinh-bo'],
        'máy tính để bàn'   => ['pc-build-san', 'may-tinh-bo'],
        'may tinh de ban'   => ['pc-build-san', 'may-tinh-bo'],

        'linh kiện'         => ['pc-linh-kien', 'cpu', 'mainboard', 'ram', 'vga', 'ssd', 'hdd', 'psu', 'case', 'tan-nhiet'],
        'linh kien'         => ['pc-linh-kien', 'cpu', 'mainboard', 'ram', 'vga', 'ssd', 'hdd', 'psu', 'case', 'tan-nhiet'],
        'linh kiện pc'      => ['pc-linh-kien', 'cpu', 'mainboard', 'ram', 'vga', 'ssd', 'hdd', 'psu', 'case', 'tan-nhiet'],
        'linh kien pc'      => ['pc-linh-kien', 'cpu', 'mainboard', 'ram', 'vga', 'ssd', 'hdd', 'psu', 'case', 'tan-nhiet'],

        'cpu'               => ['cpu'],
        'mainboard'         => ['mainboard'],
        'main'              => ['mainboard'],
        'ram'               => ['ram'],
        'vga'               => ['vga'],
        'card màn hình'     => ['vga'],
        'card man hinh'     => ['vga'],
        'card đồ họa'       => ['vga'],
        'card do hoa'       => ['vga'],
        'ssd'               => ['ssd'],
        'hdd'               => ['hdd'],
        'psu'               => ['psu'],
        'nguồn'             => ['psu'],
        'nguon'             => ['psu'],
        'case'              => ['case'],
        'vỏ máy'            => ['case'],
        'tản nhiệt'         => ['tan-nhiet'],
        'tan nhiet'         => ['tan-nhiet'],

        'màn hình'          => ['man-hinh'],
        'man hinh'          => ['man-hinh'],
        'monitor'           => ['man-hinh'],

        'gaming gear'       => ['gaming-gear'],
        'gear'              => ['gaming-gear'],

        'thiết bị văn phòng'=> ['office-gear'],
        'thiet bi van phong'=> ['office-gear'],
        'văn phòng'         => ['office-gear'],
        'van phong'         => ['office-gear'],

        'thiết bị mạng'     => ['networking'],
        'thiet bi mang'     => ['networking'],
        'mạng'              => ['networking'],
        'mang'              => ['networking'],
    ];

    /** Connection Provider Seam cho testing / DI */
    private static mixed $customConnectionProvider = null;

    public static function setConnectionProvider(?callable $provider): void
    {
        self::$customConnectionProvider = $provider;
    }

    private static function getConnection(): ?PDO
    {
        if (self::$customConnectionProvider !== null) {
            return (self::$customConnectionProvider)();
        }

        require_once ROOT_PATH . '/config/database.php';
        return Database::getConnection();
    }

    /**
     * 1. Resolver API: Tìm key của Virtual Group nếu slugOrAlias khớp virtual_slug/key/group_alias
     */
    public static function resolveGroupKey(string $slugOrAlias): ?string
    {
        $input = strtolower(trim($slugOrAlias));
        if ($input === '') {
            return null;
        }

        foreach (self::$groupDefinitions as $key => $def) {
            if ($key === $input || $def['virtual_slug'] === $input) {
                return $key;
            }
        }

        foreach (self::$groupDefinitions as $key => $def) {
            if (in_array($input, $def['group_aliases'], true)) {
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

        foreach (self::$groupDefinitions as $def) {
            if (in_array($input, $def['source_slugs'], true)) {
                return [$input];
            }
        }

        return [$input];
    }

    public static function getStaticGroupDefinition(string $key): ?array
    {
        $groupKey = self::resolveGroupKey($key) ?? $key;
        return self::$groupDefinitions[$groupKey] ?? null;
    }

    /**
     * 4. Resolver API: Lấy Tên hiển thị chuẩn
     */
    public static function getDisplayName(string $slugOrAlias): string
    {
        $input = strtolower(trim($slugOrAlias));
        if ($input === '') {
            return '';
        }

        if (isset(self::$groupDefinitions[$input])) {
            return self::$groupDefinitions[$input]['name'];
        }

        $groupKey = self::resolveGroupKey($input);
        if ($groupKey !== null && !self::isExactSourceSlug($input)) {
            return self::$groupDefinitions[$groupKey]['name'];
        }

        $db = self::getConnection();
        if ($db !== null) {
            try {
                $stmt = $db->prepare("SELECT name FROM categories WHERE slug = :slug AND status = 'active'");
                $stmt->execute([':slug' => $input]);
                $catName = $stmt->fetchColumn();
                if ($catName) {
                    return $catName;
                }
            } catch (Exception $e) {}
        }

        return ucfirst(str_replace('-', ' ', $input));
    }

    private static function isExactSourceSlug(string $slug): bool
    {
        foreach (self::$groupDefinitions as $def) {
            if ($slug === 'pc-linh-kien') {
                return false;
            }
            if (in_array($slug, $def['source_slugs'], true)) {
                return true;
            }
        }
        return false;
    }

    public static function getKeywordAliasMap(): array
    {
        return self::$keywordAliasMap;
    }

    public static function getAllVirtualGroups(): array
    {
        $db = self::getConnection();
        if ($db === null) {
            return self::getFallbackGroups();
        }

        try {
            $stmtCats = $db->query("SELECT id, parent_id, name, slug, status FROM categories WHERE status = 'active'");
            $allCats = $stmtCats->fetchAll(PDO::FETCH_ASSOC);

            if (empty($allCats)) {
                return self::getFallbackGroups();
            }

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

    public static function getStorefrontGroups(): array
    {
        $db = self::getConnection();
        if ($db === null) {
            return [];
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

    private static function hydrateGroupData(array $def, array $allCats, array $allProds): array
    {
        $sourceSlugs = $def['source_slugs'];

        $sourceCatIds = [];
        foreach ($allCats as $cat) {
            if (in_array($cat['slug'], $sourceSlugs, true)) {
                $sourceCatIds[] = (int)$cat['id'];
            }
        }

        $groupProds = array_filter($allProds, function ($p) use ($sourceCatIds) {
            return in_array((int)$p['category_id'], $sourceCatIds, true);
        });

        $productCount = count($groupProds);
        $status = ($productCount > 0) ? 'ready' : 'not_ready';

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

        $priceRanges = self::generatePriceRanges($def['key'], $effectivePrices);

        return [
            'key'                     => $def['key'],
            'name'                    => $def['name'],
            'canonical_slug'           => $def['virtual_slug'],
            'virtual_slug'            => $def['virtual_slug'],
            'icon'                    => $def['icon'],
            'source_category_ids'     => array_values(array_unique($sourceCatIds)),
            'source_slugs'            => $sourceSlugs,
            'aliases'                 => $def['group_aliases'],
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
     * Tính toán khoảng giá động dựa trên dữ liệu runtime effective prices.
     * Chỉ trả về các range có product_count > 0 và các mốc giá không bị ranh giới chồng lấn.
     */
    private static function generatePriceRanges(string $groupKey, array $effectivePrices): array
    {
        if (empty($effectivePrices)) {
            return [];
        }

        $templates = [];
        if ($groupKey === 'laptop' || $groupKey === 'pc') {
            $templates = [
                ['name' => 'Dưới 15 triệu', 'min_price' => 0, 'max_price' => 15000000, 'query' => 'min_price=0&max_price=15000000'],
                ['name' => 'Từ 15 - 20 triệu', 'min_price' => 15000001, 'max_price' => 20000000, 'query' => 'min_price=15000001&max_price=20000000'],
                ['name' => 'Từ 20 - 30 triệu', 'min_price' => 20000001, 'max_price' => 30000000, 'query' => 'min_price=20000001&max_price=30000000'],
                ['name' => 'Trên 30 triệu', 'min_price' => 30000001, 'max_price' => null, 'query' => 'min_price=30000001'],
            ];
        } elseif ($groupKey === 'man-hinh') {
            $templates = [
                ['name' => 'Dưới 5 triệu', 'min_price' => 0, 'max_price' => 5000000, 'query' => 'min_price=0&max_price=5000000'],
                ['name' => 'Từ 5 - 10 triệu', 'min_price' => 5000001, 'max_price' => 10000000, 'query' => 'min_price=5000001&max_price=10000000'],
                ['name' => 'Trên 10 triệu', 'min_price' => 10000001, 'max_price' => null, 'query' => 'min_price=10000001'],
            ];
        } elseif ($groupKey === 'gaming-gear' || $groupKey === 'office-gear') {
            $templates = [
                ['name' => 'Dưới 2 triệu', 'min_price' => 0, 'max_price' => 2000000, 'query' => 'min_price=0&max_price=2000000'],
                ['name' => 'Từ 2 - 5 triệu', 'min_price' => 2000001, 'max_price' => 5000000, 'query' => 'min_price=2000001&max_price=5000000'],
                ['name' => 'Trên 5 triệu', 'min_price' => 5000001, 'max_price' => null, 'query' => 'min_price=5000001'],
            ];
        } else {
            // Linh kiện PC
            $templates = [
                ['name' => 'Dưới 2 triệu', 'min_price' => 0, 'max_price' => 2000000, 'query' => 'min_price=0&max_price=2000000'],
                ['name' => 'Từ 2 - 5 triệu', 'min_price' => 2000001, 'max_price' => 5000000, 'query' => 'min_price=2000001&max_price=5000000'],
                ['name' => 'Từ 5 - 10 triệu', 'min_price' => 5000001, 'max_price' => 10000000, 'query' => 'min_price=5000001&max_price=10000000'],
                ['name' => 'Trên 10 triệu', 'min_price' => 10000001, 'max_price' => null, 'query' => 'min_price=10000001'],
            ];
        }

        $ranges = [];
        foreach ($templates as $tpl) {
            $minP = (float)$tpl['min_price'];
            $maxP = $tpl['max_price'] !== null ? (float)$tpl['max_price'] : null;

            $matchingCount = 0;
            foreach ($effectivePrices as $price) {
                if ($price >= $minP && ($maxP === null || $price <= $maxP)) {
                    $matchingCount++;
                }
            }

            if ($matchingCount > 0) {
                $ranges[] = [
                    'name'          => $tpl['name'],
                    'min_price'     => $minP,
                    'max_price'     => $maxP,
                    'product_count' => $matchingCount,
                    'query'         => $tpl['query'],
                ];
            }
        }

        return $ranges;
    }

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
                'aliases'                 => $def['group_aliases'],
                'status'                  => 'unavailable',
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
