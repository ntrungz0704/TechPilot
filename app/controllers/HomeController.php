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
            'flashSale'              => $productModel->getFlashSale(6),
            
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
        $sort = trim($_GET['sort'] ?? 'newest');
        $maxPrice = (int)($_GET['max_price'] ?? 0);

        $productModel = $this->model('Product');
        $products = $productModel->search($keyword, $categorySlug, 48);

        // Filter by max_price if set
        if ($maxPrice > 0) {
            $products = array_filter($products, fn($p) => (float)$p['price'] <= $maxPrice);
        }

        // Apply Sorting
        if ($sort === 'price-low') {
            usort($products, fn($a, $b) => (float)$a['price'] <=> (float)$b['price']);
        } elseif ($sort === 'price-high') {
            usort($products, fn($a, $b) => (float)$b['price'] <=> (float)$a['price']);
        } elseif ($sort === 'rating') {
            usort($products, fn($a, $b) => ((float)($b['rating'] ?? 0)) <=> ((float)($a['rating'] ?? 0)));
        }

        $pageTitle = 'Kết quả tìm kiếm';
        if (!empty($keyword) && !empty($categorySlug)) {
            $categoryName = '';
            foreach ($productModel->getCategories() as $cat) {
                if ($cat['slug'] === $categorySlug) {
                    $categoryName = $cat['name'];
                    break;
                }
            }
            $pageTitle = 'Tìm kiếm: ' . $keyword . ' trong ' . ($categoryName ?: $categorySlug);
        } elseif (!empty($keyword)) {
            $pageTitle = 'Tìm kiếm: ' . $keyword;
        } elseif (!empty($categorySlug)) {
            foreach ($productModel->getCategories() as $cat) {
                if ($cat['slug'] === $categorySlug) {
                    $pageTitle = $cat['name'];
                    break;
                }
            }
        }

        $this->render('home/search', [
            'pageTitle'    => $pageTitle,
            'keyword'      => $keyword,
            'categorySlug' => $categorySlug,
            'sort'         => $sort,
            'maxPrice'     => $maxPrice,
            'products'     => array_values($products),
            'categories'   => $productModel->getCategories(),
            'totalResults' => count($products),
        ]);
    }

    /** Trang danh mục */
    public function category(string $slug = ''): void
    {
        $productModel = $this->model('Product');
        $products = $productModel->getByCategory($slug, 24);
        $categories = $productModel->getCategories();

        $categoryName = '';
        foreach ($categories as $cat) {
            if ($cat['slug'] === $slug) {
                $categoryName = $cat['name'];
                break;
            }
        }

        if (empty($categoryName)) {
            $this->notFound();
            return;
        }

        $this->render('home/search', [
            'pageTitle'    => $categoryName,
            'keyword'      => '',
            'categorySlug' => $slug,
            'products'     => $products,
            'categories'   => $categories,
            'totalResults' => count($products),
        ]);
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

    public function notFound(): void
    {
        http_response_code(404);
        $this->render('home/404', ['pageTitle' => 'Không tìm thấy trang']);
    }
}
