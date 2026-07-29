<?php
require_once ROOT_PATH . '/app/core/helpers.php';
require_once ROOT_PATH . '/app/models/Compare.php';
require_once ROOT_PATH . '/app/services/ProductComparisonService.php';

class CompareController extends Controller
{
    private Compare $model;

    public function __construct()
    {
        $this->model = new Compare();
    }

    public function index(): void
    {
        if (!isset($_SESSION['compare'])) {
            $_SESSION['compare'] = [];
        }

        // Xử lý nạp nhanh qua parameter ?add=ID
        if (!empty($_GET['add'])) {
            $this->handleAddProductId((int)$_GET['add']);
        }

        // Xử lý API tìm kiếm sản phẩm theo từ khóa để thêm trực tiếp tại trang Compare
        if (isset($_GET['search_ajax'])) {
            $this->handleSearchAjax();
            return;
        }

        $ids = $_SESSION['compare'];
        $products = [];
        if (!empty($ids)) {
            $products = $this->model->getProductsByIds($ids);
        }

        $compareConfig = require ROOT_PATH . '/config/product-comparison.php';

        // Xác định danh mục active
        $activeCategorySlug = trim($_GET['cat'] ?? '');
        if (empty($activeCategorySlug) && !empty($products[0])) {
            $activeCategorySlug = $products[0]['category_slug'] ?? 'laptop';
        }
        if (empty($activeCategorySlug)) {
            $activeCategorySlug = 'laptop';
        }

        $this->render('compare/index', [
            'pageTitle'          => 'So sánh sản phẩm theo Persona (TechPilot Compare 4.0)',
            'products'           => $products,
            'compareConfig'      => $compareConfig,
            'activeCategorySlug' => $activeCategorySlug,
            'csrf_token'         => $_SESSION['csrf_token'] ?? '',
            'flashes'            => pullFlashes()
        ], false);
    }

    public function add(): void
    {
        $productId = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? $_GET['add'] ?? 0);
        if ($productId > 0) {
            $this->handleAddProductId($productId);
        }

        $this->redirect('compare');
    }

    public function remove(): void
    {
        $productId = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
        if ($productId > 0 && isset($_SESSION['compare'])) {
            $key = array_search($productId, $_SESSION['compare']);
            if ($key !== false) {
                unset($_SESSION['compare'][$key]);
                $_SESSION['compare'] = array_values($_SESSION['compare']);
                flash('success', 'Đã xóa sản phẩm khỏi danh sách so sánh.');
            }
        }

        $this->redirect('compare');
    }

    /**
     * API: Phân tích so sánh 2-4 sản phẩm theo Persona & Tiêu chí ưu tiên
     */
    public function aiCompare(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['compare']) || count($_SESSION['compare']) < 2) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn từ 2 đến 4 sản phẩm cùng danh mục để so sánh bằng AI.']);
            exit;
        }

        // Xác thực CSRF Token
        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf'] ?? ($_POST['csrf_token'] ?? ''));
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        if (!empty($sessionToken) && (empty($csrfToken) || !hash_equals($sessionToken, $csrfToken))) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Xác thực CSRF thất bại. Vui lòng làm mới trang.']);
            exit;
        }

        $ids = $_SESSION['compare'];
        $products = $this->model->getProductsByIds($ids);

        $options = [
            'category'          => trim($_POST['category'] ?? ($products[0]['category_slug'] ?? 'laptop')),
            'persona'           => trim($_POST['persona'] ?? 'developer'),
            'priorities'        => (array)($_POST['priorities'] ?? ['performance']),
            'budget_max'        => !empty($_POST['budget_max']) ? (float)$_POST['budget_max'] : null,
            'min_ram'           => !empty($_POST['min_ram']) ? (int)$_POST['min_ram'] : 0,
            'min_storage'       => !empty($_POST['min_storage']) ? (int)$_POST['min_storage'] : 0,
            'min_refresh_rate'  => !empty($_POST['min_refresh_rate']) ? (int)$_POST['min_refresh_rate'] : 0
        ];

        try {
            $result = ProductComparisonService::analyzeComparison($products, $options);
            echo json_encode($result);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi phân tích so sánh: ' . $e->getMessage()]);
        }
        exit;
    }

    private function handleAddProductId(int $productId): void
    {
        $productModel = $this->model('Product');
        $product = $productModel->getById($productId);
        if (!$product) {
            flash('error', 'Sản phẩm không tồn tại.');
            return;
        }

        if (!empty($_SESSION['compare'])) {
            $firstProduct = $productModel->getById($_SESSION['compare'][0]);
            if ($firstProduct && $firstProduct['category_id'] != $product['category_id']) {
                flash('error', 'Chỉ có thể so sánh các sản phẩm trong CÙNG DẠNG DANH MỤC.');
                return;
            }
        }

        if (in_array($productId, $_SESSION['compare'])) {
            flash('info', 'Sản phẩm đã có trong danh sách so sánh.');
            return;
        }

        if (count($_SESSION['compare']) >= 4) {
            flash('warning', 'Chỉ được so sánh tối đa 4 sản phẩm cùng lúc.');
            return;
        }

        $_SESSION['compare'][] = $productId;
        flash('success', 'Đã thêm sản phẩm vào danh sách so sánh.');
    }

    private function handleSearchAjax(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $db = Database::getConnection();
        if (!$db) {
            echo json_encode(['success' => false, 'message' => 'Lỗi DB']);
            exit;
        }

        $query = trim($_GET['search_ajax'] ?? '');
        $requestedCatSlug = trim($_GET['category_slug'] ?? ($_GET['cat'] ?? ''));

        // 1. ÁNH XẠ DANH MỤC BẮT BUỘC (CATEGORY-FIRST FILTERING)
        $compareConfig = require ROOT_PATH . '/config/product-comparison.php';
        $categoryIdFilter = 0;
        $categorySlugsFilter = [];

        // Nếu đã có sản phẩm trong compare session -> Khóa chặt danh mục của sản phẩm đó
        if (!empty($_SESSION['compare'])) {
            $productModel = $this->model('Product');
            $firstProduct = $productModel->getById($_SESSION['compare'][0]);
            if ($firstProduct) {
                $categoryIdFilter = (int)$firstProduct['category_id'];
            }
        } elseif (!empty($requestedCatSlug) && isset($compareConfig['categories'][$requestedCatSlug])) {
            $categorySlugsFilter = $compareConfig['categories'][$requestedCatSlug]['slugs'];
        }

        try {
            $sql = "SELECT p.id, p.name, p.price, p.image, p.category_id, c.name as category_name, c.slug as category_slug, b.name as brand_name";
            if ($query !== '') {
                $sql .= ", CASE 
                    WHEN c.slug = ? OR LOWER(c.name) = LOWER(?) THEN 1
                    WHEN p.name LIKE ? THEN 2
                    WHEN c.name LIKE ? OR c.slug LIKE ? THEN 3
                    WHEN b.name LIKE ? THEN 4
                    ELSE 5
                END as relevance";
            } else {
                $sql .= ", 1 as relevance";
            }

            $sql .= " FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    LEFT JOIN brands b ON p.brand_id = b.id 
                    WHERE p.status = 'active'";

            $params = [];

            if ($query !== '') {
                $params[] = strtolower($query);
                $params[] = strtolower($query);
                $params[] = $query . '%';
                $params[] = '%' . $query . '%';
                $params[] = '%' . $query . '%';
                $params[] = '%' . $query . '%';

                $sql .= " AND (p.name LIKE ? OR c.name LIKE ? OR c.slug LIKE ? OR b.name LIKE ?)";
                $params[] = '%' . $query . '%';
                $params[] = '%' . $query . '%';
                $params[] = '%' . $query . '%';
                $params[] = '%' . $query . '%';
            }

            // FILTER THEO CATEGORY DÃ KHÓA HOẶC CATEGORY ĐÃ CHỌN
            if ($categoryIdFilter > 0) {
                $sql .= " AND p.category_id = ?";
                $params[] = $categoryIdFilter;
            } elseif (!empty($categorySlugsFilter)) {
                $placeholdersCat = implode(',', array_fill(0, count($categorySlugsFilter), '?'));
                $sql .= " AND c.slug IN ($placeholdersCat)";
                foreach ($categorySlugsFilter as $sSlug) {
                    $params[] = $sSlug;
                }
            }

            if (!empty($_SESSION['compare'])) {
                $placeholders = implode(',', array_fill(0, count($_SESSION['compare']), '?'));
                $sql .= " AND p.id NOT IN ($placeholders)";
                foreach ($_SESSION['compare'] as $cId) {
                    $params[] = (int)$cId;
                }
            }

            $sql .= " ORDER BY relevance ASC, p.rating DESC, p.id DESC LIMIT 8";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as &$r) {
                $r['price_formatted'] = formatPrice((float)$r['price']);
                $r['image_url']       = productImageUrl($r['image'] ?? '', $r['category_slug'] ?? '', (int)$r['id']);
            }

            echo json_encode(['success' => true, 'data' => $rows]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
