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
        $maxPrice = filter_input(INPUT_GET, 'max_price', FILTER_VALIDATE_FLOAT) ?: 0.0;
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

        // Chỉ trả về các trường cần thiết cho giao diện gợi ý
        $products = array_map(function ($p) {
            return [
                'id'            => $p['id'],
                'name'          => $p['name'],
                'slug'          => $p['slug'],
                'image'         => $p['image'] ?? '',
                'price'         => $p['price'],
                'category_name' => $p['category_name'] ?? '',
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
                    '15000000' => 'Dưới 15 triệu',
                    '20000000' => 'Từ 15 - 20 triệu',
                    '25000000' => 'Từ 20 - 25 triệu',
                    '30000000' => 'Từ 25 - 30 triệu',
                    '50000000' => 'Trên 30 triệu',
                ]
            ]
        ];

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

        return $base;
    }
}
