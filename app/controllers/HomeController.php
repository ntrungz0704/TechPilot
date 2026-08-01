<?php

class HomeController extends Controller
{
    public function index(): void
    {
        $productModel = $this->model('Product');
        $brandModel   = $this->model('Brand');
        $bannerModel  = $this->model('Banner');
        $postModel    = $this->model('Post');
        $reviewModel  = $this->model('Review');

        $data = [
            'isHome'                 => true,
            'pageTitle'              => 'Trang chủ - TechPilot',
            'categories'             => $productModel->getCategories(),
            'flashSale'              => $productModel->getFlashSale(20),
            'featuredProducts'       => $productModel->getFeaturedProducts(6),
            'newProducts'            => $productModel->getNewProducts(6),
            'promoProducts'          => $productModel->getPromoProducts(6),

            // Các danh mục sản phẩm lớn ở trang chủ
            'laptopGaming'           => $productModel->getByCategorySlug('laptop-gaming', 6),
            'laptopVanPhong'         => $productModel->getByCategorySlug('laptop-van-phong', 6),
            'pcBuildSan'             => $productModel->getByCategorySlug('pc-build-san', 6),
            'pcLinhKien'             => $productModel->getByCategorySlug('pc-linh-kien', 6),
            'gamingGear'             => $productModel->getByCategorySlug('gaming-gear', 6),
            'monHinh'                => $productModel->getByCategorySlug('man-hinh', 6),
            'apple'                  => $productModel->getByCategorySlug('may-tinh-bo', 6),

            // Dữ liệu cho Best Seller Tabs
            'bestSellersLaptop'      => $productModel->getBestSellersByTab('laptop', 6),
            'bestSellersGaming'      => $productModel->getBestSellersByTab('gaming', 6),
            'bestSellersComponents'  => $productModel->getBestSellersByTab('components', 6),
            'bestSellersMonitor'     => $productModel->getBestSellersByTab('monitor', 6),
            'bestSellersAccessories'  => $productModel->getBestSellersByTab('accessories', 6),

            // Banners quảng cáo
            'heroBanners'            => $bannerModel->getByType('hero'),
            'sidebarBanners'         => $bannerModel->getByType('hero_sidebar'),
            'midBanners'             => $bannerModel->getByType('mid_banner'),
            'longBanners'            => $bannerModel->getByType('long_banner'),

            // Đối tác, Tin tức, Đánh giá khách hàng
            'brands'                 => $brandModel->getAll(),
            'posts'                  => $postModel->getLatest(4),
            'reviews'                => $reviewModel->getLatestReviews(6),
        ];

        $this->render('home/index', $data);
    }

    /** Trang tìm kiếm sản phẩm */
    public function search(): void
    {
        $keyword = trim($_GET['q'] ?? '');
        $categorySlug = trim($_GET['cat'] ?? '');
        $brandSlug = trim($_GET['brand'] ?? '');
        $minPrice = filter_input(INPUT_GET, 'min_price', FILTER_VALIDATE_FLOAT) ?: 0.0;
        $maxPrice = filter_input(INPUT_GET, 'max_price', FILTER_VALIDATE_FLOAT) ?: (filter_input(INPUT_GET, 'price', FILTER_VALIDATE_FLOAT) ?: 0.0);
        $inStockOnly = ($_GET['stock'] ?? '') === '1';
        $promoOnly = ($_GET['promo'] ?? '') === '1';
        $sort = $_GET['sort'] ?? ($keyword !== '' ? 'relevance' : 'newest');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 24;

        $productModel = $this->model('Product');

        // 1. Đếm tổng kết quả trước
        $totalResults = $productModel->countSearch(
            $keyword, $categorySlug, $brandSlug, $minPrice, $maxPrice, $inStockOnly, $promoOnly
        );

        // 2. Tính toán số trang và Clamp page nếu xơ vơ vượt quá
        $totalPages = max(1, (int)ceil($totalResults / $limit));
        if ($page > $totalPages && $totalResults > 0) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * $limit;

        // 3. Query danh sách sản phẩm theo Search Plan thống nhất
        $searchResult = $productModel->search(
            $keyword, $categorySlug, $limit, $offset, $brandSlug, $minPrice, $maxPrice, $sort, $inStockOnly, $promoOnly
        );

        $searchError = false;
        $products = [];

        if ($searchResult === false) {
            $searchError = true;
            $products = [];
            error_log("SEARCH_ERROR: Query failed for q={$keyword}, cat={$categorySlug}");
        } else {
            $products = $searchResult;
        }

        // 4. Invariant Assertion Check
        if ($totalResults > 0 && empty($products) && !$searchError) {
            error_log("SEARCH_RESULT_INVARIANT_BROKEN: totalResults={$totalResults}, products empty, page={$page}, limit={$limit}, offset={$offset}, q={$keyword}, cat={$categorySlug}");
        }

        require_once ROOT_PATH . '/app/services/CatalogGroupService.php';
        $isStopwordQuery = CatalogGroupService::isPureStopword($keyword) && empty($categorySlug);

        $pageTitle = 'Kết quả tìm kiếm';
        if ($promoOnly) {
            $pageTitle = 'Sản phẩm đang Khuyến mãi';
        } elseif (!empty($keyword) && !empty($categorySlug)) {
            $categoryName = CatalogGroupService::getDisplayName($categorySlug);
            $pageTitle = 'Tìm kiếm: ' . $keyword . ' trong ' . $categoryName;
        } elseif (!empty($keyword)) {
            $pageTitle = 'Tìm kiếm: ' . $keyword;
        } elseif (!empty($categorySlug)) {
            $pageTitle = CatalogGroupService::getDisplayName($categorySlug);
        }

        // 5. Load brands & subgroups for filter chips
        $activeBrands = [];
        $subgroups = [];
        $filterConfig = [];

        if (!empty($categorySlug)) {
            $sourceSlugs = CatalogGroupService::resolveSourceSlugs($categorySlug);
            $activeBrands = $productModel->getBrandsForCategories($sourceSlugs);
            
            // Load subgroups from virtual group
            $groupKey = CatalogGroupService::resolveGroupKey($categorySlug);
            if ($groupKey) {
                $allGroups = CatalogGroupService::getAllVirtualGroups();
                if (isset($allGroups[$groupKey]['subgroups'])) {
                    $subgroups = $allGroups[$groupKey]['subgroups'];
                }
            }

            // Per-category filter config
            $filterConfig = $this->getFilterConfig($categorySlug);
        }

        $this->render('home/search', [
            'pageTitle'        => $pageTitle,
            'keyword'          => $keyword,
            'categorySlug'     => $categorySlug,
            'brandSlug'        => $brandSlug,
            'minPrice'         => $minPrice,
            'maxPrice'         => $maxPrice,
            'inStockOnly'      => $inStockOnly,
            'promoOnly'        => $promoOnly,
            'sort'             => $sort,
            'page'             => $page,
            'limit'            => $limit,
            'products'         => $products,
            'categories'       => $productModel->getCategories(),
            'totalResults'     => $totalResults,
            'isStopwordQuery'  => $isStopwordQuery,
            'searchError'      => $searchError,
            'activeBrands'     => $activeBrands,
            'subgroups'        => $subgroups,
            'filterConfig'     => $filterConfig,
            'pageStyles'       => ['assets/css/search-filters.css?v=1.0'],
            'pageScripts'      => ['assets/js/search-filters.js?v=1.0'],
        ]);
    }


    /** Trang danh mục — redirect sang search để dùng chung giao diện */
    public function category(string $slug = ''): void
    {
        header('Location: ' . url('home/search?cat=' . urlencode($slug)), true, 301);
        exit;
    }

    /** Tìm kiếm AJAX realtime */
    public function ajaxSearch(): void
    {
        $keyword      = trim($_GET['q'] ?? '');
        $categorySlug = trim($_GET['cat'] ?? '');

        header('Content-Type: application/json; charset=utf-8');

        // Require at least 2 characters (mirrors client-side guard)
        if (safe_strlen($keyword) < 2) {
            echo json_encode([]);
            return;
        }

        $productModel = $this->model('Product');
        // Chỉ lấy 6 sản phẩm để hiển thị dropdown
        $all = $productModel->search($keyword, $categorySlug, 6);

        // Chỉ trả về các trường cần thiết cho giao diện gợi ý với giá đồng bộ tuyệt đối
        $products = array_map(function ($p) {
            $eff = getEffectiveProductData($p);
            return [
                'id'              => $p['id'],
                'name'            => $p['name'],
                'slug'            => $p['slug'],
                'image'           => $p['image'] ?? '',
                'price'           => $eff['final_price'],
                'original_price'  => $eff['original_price'],
                'price_formatted' => formatPrice($eff['final_price']),
                'has_discount'    => $eff['has_discount'],
                'category_name'   => $p['category_name'] ?? '',
            ];
        }, $all);

        echo json_encode($products);
    }

    public function trade_in(): void
    {
        $this->render('home/trade_in', [
            'pageTitle' => 'Thu cũ đổi mới máy cũ - TechPilot'
        ]);
    }

    public function notFound(): void
    {
        http_response_code(404);
        $this->render('home/404', ['pageTitle' => 'Không tìm thấy trang']);
    }

    /**
     * Lấy cấu hình bộ lọc phù hợp theo từng danh mục
     */
    private function getFilterConfig(string $categorySlug): array
    {
        $base = [
            'price' => [
                'label' => 'Giá',
                'options' => [
                    '5000000' => 'Dưới 5 triệu',
                    '10000000' => 'Từ 5 - 10 triệu',
                    '15000000' => 'Từ 10 - 15 triệu',
                    '20000000' => 'Từ 15 - 20 triệu',
                    '30000000' => 'Từ 20 - 30 triệu',
                    '50000000' => 'Trên 30 triệu',
                ]
            ]
        ];

        // 1. Laptop & Laptop Gaming
        if (in_array($categorySlug, ['laptop', 'laptop-gaming'], true)) {
            $base['cpu'] = [
                'label' => 'CPU',
                'options' => [
                    'i3' => 'Intel Core i3',
                    'i5' => 'Intel Core i5',
                    'i7' => 'Intel Core i7',
                    'i9' => 'Intel Core i9',
                    'Ultra' => 'Intel Core Ultra',
                    'Ryzen 5' => 'AMD Ryzen 5',
                    'Ryzen 7' => 'AMD Ryzen 7',
                    'Ryzen 9' => 'AMD Ryzen 9',
                    'M3' => 'Apple M3 / M3 Max',
                ]
            ];
            $base['screen'] = [
                'label' => 'Kích thước màn hình',
                'options' => [
                    '14' => '14 inch',
                    '15.6' => '15.6 inch',
                    '16' => '16 inch',
                    'OLED' => 'Màn hình OLED',
                    '144Hz' => '144Hz',
                    '240Hz' => '240Hz',
                ]
            ];
            $base['ram'] = [
                'label' => 'RAM',
                'options' => [
                    '8GB' => '8 GB',
                    '16GB' => '16 GB',
                    '32GB' => '32 GB',
                    '64GB' => '64 GB',
                ]
            ];
            $base['ssd'] = [
                'label' => 'SSD',
                'options' => [
                    '256GB' => '256 GB',
                    '512GB' => '512 GB',
                    '1TB' => '1 TB (1024GB)',
                    '2TB' => '2 TB',
                ]
            ];
            $base['vga'] = [
                'label' => 'VGA',
                'options' => [
                    'Intel' => 'Intel Graphics / UHD',
                    'RTX 3050' => 'NVIDIA RTX 3050',
                    'RTX 4050' => 'NVIDIA RTX 4050',
                    'RTX 4060' => 'NVIDIA RTX 4060',
                    'RTX 4070' => 'NVIDIA RTX 4070',
                    'RTX 4080' => 'NVIDIA RTX 4080',
                    'RTX 4090' => 'NVIDIA RTX 4090',
                ]
            ];
        }
        // 2. PC (PC Lắp sẵn / PC Gaming)
        elseif (in_array($categorySlug, ['pc', 'pc-gaming', 'pc-van-phong'], true)) {
            $base['cpu'] = [
                'label' => 'CPU',
                'options' => [
                    'i3' => 'Intel Core i3',
                    'i5' => 'Intel Core i5',
                    'i7' => 'Intel Core i7',
                    'i9' => 'Intel Core i9',
                    'Ultra 5' => 'Intel Core Ultra 5',
                    'Ultra 7' => 'Intel Core Ultra 7',
                    'Ryzen 5' => 'AMD Ryzen 5',
                    'Ryzen 7' => 'AMD Ryzen 7',
                ]
            ];
            $base['ram'] = [
                'label' => 'RAM',
                'options' => [
                    '8GB' => '8 GB',
                    '16GB' => '16 GB',
                    '32GB' => '32 GB',
                    '64GB' => '64 GB',
                ]
            ];
            $base['ssd'] = [
                'label' => 'SSD',
                'options' => [
                    '256GB' => '256 GB',
                    '512GB' => '512 GB',
                    '1TB' => '1 TB',
                    '2TB' => '2 TB',
                ]
            ];
            $base['vga'] = [
                'label' => 'VGA',
                'options' => [
                    'GTX 1650' => 'NVIDIA GTX 1650',
                    'RTX 3050' => 'NVIDIA RTX 3050',
                    'RTX 4060' => 'NVIDIA RTX 4060',
                    'RTX 4070' => 'NVIDIA RTX 4070',
                    'RTX 4080' => 'NVIDIA RTX 4080',
                    'RX 6500XT' => 'AMD RX 6500XT',
                    'RX 7600' => 'AMD RX 7600',
                ]
            ];
        }
        // 3. VGA (Card màn hình)
        elseif ($categorySlug === 'vga') {
            $base['vga_series'] = [
                'label' => 'Dòng VGA',
                'options' => [
                    'RTX 50 Series' => 'NVIDIA RTX 50 Series',
                    'RTX 40 Series' => 'NVIDIA RTX 40 Series',
                    'RTX 30 Series' => 'NVIDIA RTX 30 Series',
                    'RX 7000 Series' => 'AMD RX 7000 Series',
                    'RX 6000 Series' => 'AMD RX 6000 Series',
                ]
            ];
            $base['vram'] = [
                'label' => 'Dung lượng bộ nhớ',
                'options' => [
                    '6GB' => '6 GB',
                    '8GB' => '8 GB',
                    '12GB' => '12 GB',
                    '16GB' => '16 GB',
                    '24GB' => '24 GB',
                    '32GB' => '32 GB',
                ]
            ];
            $base['gpu_chip'] = [
                'label' => 'Nhân đồ họa',
                'options' => [
                    'NVIDIA' => 'NVIDIA',
                    'AMD' => 'AMD',
                    'Intel' => 'Intel Arc',
                ]
            ];
        }
        // 4. Mainboard
        elseif ($categorySlug === 'mainboard') {
            $base['chipset'] = [
                'label' => 'Chipset',
                'options' => [
                    'H610' => 'Intel H610',
                    'B760' => 'Intel B760',
                    'Z790' => 'Intel Z790',
                    'Z890' => 'Intel Z890',
                    'B650' => 'AMD B650',
                    'X670' => 'AMD X670',
                    'X870' => 'AMD X870',
                ]
            ];
            $base['form_factor'] = [
                'label' => 'Kích thước',
                'options' => [
                    'Micro ATX' => 'Micro ATX',
                    'ATX' => 'ATX',
                    'Mini ITX' => 'Mini ITX',
                ]
            ];
            $base['ram_type'] = [
                'label' => 'Loại RAM',
                'options' => [
                    'DDR4' => 'DDR4',
                    'DDR5' => 'DDR5',
                ]
            ];
            $base['socket'] = [
                'label' => 'Socket',
                'options' => [
                    'LGA 1700' => 'LGA 1700',
                    'LGA 1851' => 'LGA 1851',
                    'AM4' => 'AM4',
                    'AM5' => 'AM5',
                ]
            ];
        }
        // 5. CPU
        elseif ($categorySlug === 'cpu') {
            $base['cpu_series'] = [
                'label' => 'Dòng CPU',
                'options' => [
                    'Core i3' => 'Intel Core i3',
                    'Core i5' => 'Intel Core i5',
                    'Core i7' => 'Intel Core i7',
                    'Core i9' => 'Intel Core i9',
                    'Core Ultra 5' => 'Intel Core Ultra 5',
                    'Core Ultra 7' => 'Intel Core Ultra 7',
                    'Ryzen 5' => 'AMD Ryzen 5',
                    'Ryzen 7' => 'AMD Ryzen 7',
                    'Ryzen 9' => 'AMD Ryzen 9',
                ]
            ];
            $base['socket'] = [
                'label' => 'Socket',
                'options' => [
                    'LGA 1700' => 'LGA 1700',
                    'LGA 1851' => 'LGA 1851',
                    'AM4' => 'AM4',
                    'AM5' => 'AM5',
                ]
            ];
            $base['generation'] = [
                'label' => 'Thế hệ CPU',
                'options' => [
                    'Intel Core Gen 12' => 'Intel Gen 12',
                    'Intel Core Gen 13' => 'Intel Gen 13',
                    'Intel Core Gen 14' => 'Intel Gen 14',
                    'Intel Core Ultra' => 'Intel Core Ultra',
                    'AMD Ryzen 7000' => 'AMD Ryzen 7000',
                    'AMD Ryzen 9000' => 'AMD Ryzen 9000',
                ]
            ];
            $base['igpu'] = [
                'label' => 'Đồ họa tích hợp',
                'options' => [
                    '1' => 'Có đồ họa tích hợp',
                    '0' => 'Không (Yêu cầu card rời)',
                ]
            ];
        }
        // 6. Monitor
        elseif ($categorySlug === 'monitor') {
            $base['screen_size'] = [
                'label' => 'Kích thước',
                'options' => [
                    '24' => '24 inch',
                    '27' => '27 inch',
                    '32' => '32 inch',
                    '34' => '34 inch Ultrawide',
                ]
            ];
            $base['refresh_rate'] = [
                'label' => 'Tần số quét',
                'options' => [
                    '100Hz' => '100Hz',
                    '144Hz' => '144Hz',
                    '180Hz' => '180Hz',
                    '240Hz' => '240Hz',
                ]
            ];
            $base['panel'] = [
                'label' => 'Tấm nền',
                'options' => [
                    'IPS' => 'IPS',
                    'VA' => 'VA',
                    'OLED' => 'OLED',
                ]
            ];
        }
        // 7. RAM
        elseif ($categorySlug === 'ram') {
            $base['ram_type'] = [
                'label' => 'Loại RAM',
                'options' => ['DDR4' => 'DDR4', 'DDR5' => 'DDR5']
            ];
            $base['capacity'] = [
                'label' => 'Dung lượng',
                'options' => ['8GB' => '8 GB', '16GB' => '16 GB', '32GB' => '32 GB', '64GB' => '64 GB']
            ];
            $base['bus'] = [
                'label' => 'Bus RAM',
                'options' => ['3200MHz' => '3200 MHz', '3600MHz' => '3600 MHz', '5600MHz' => '5600 MHz', '6000MHz' => '6000 MHz']
            ];
        }
        // 8. Storage
        elseif ($categorySlug === 'storage') {
            $base['type'] = [
                'label' => 'Loại ổ cứng',
                'options' => ['SSD NVMe' => 'SSD NVMe M.2', 'SSD SATA' => 'SSD SATA 2.5"', 'HDD' => 'HDD 3.5"']
            ];
            $base['capacity'] = [
                'label' => 'Dung lượng',
                'options' => ['256GB' => '256 GB', '512GB' => '512 GB', '1TB' => '1 TB', '2TB' => '2 TB']
            ];
        }
        // 9. PSU
        elseif ($categorySlug === 'psu') {
            $base['wattage'] = [
                'label' => 'Công suất Nguồn',
                'options' => ['550W' => '550W', '650W' => '650W', '750W' => '750W', '850W' => '850W', '1000W' => '1000W', '1600W' => '1600W']
            ];
        }

        return $base;
    }
}
