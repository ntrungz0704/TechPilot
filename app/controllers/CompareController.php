<?php
require_once ROOT_PATH . '/app/core/helpers.php';
require_once ROOT_PATH . '/app/models/Compare.php';

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

        $this->render('compare/index', [
            'pageTitle' => 'So sánh sản phẩm (Tối đa 4 sản phẩm)',
            'products' => $products,
            'flashes' => pullFlashes()
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
     * API: Phân tích so sánh 2-4 sản phẩm sử dụng Gemini AI
     */
    public function aiCompare(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['compare']) || count($_SESSION['compare']) < 2) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn từ 2 đến 4 sản phẩm cùng danh mục để so sánh bằng AI.']);
            exit;
        }

        require_once ROOT_PATH . '/app/services/GeminiService.php';
        require_once ROOT_PATH . '/app/services/ProductIntelligenceService.php';

        $ids = $_SESSION['compare'];
        $products = $this->model->getProductsByIds($ids);

        try {
            $result = ProductIntelligenceService::analyzeComparison($products);
            echo json_encode([
                'success' => true,
                'analysis' => $result['analysis'],
                'recommended_id' => $result['recommended_id']
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    private function handleAddProductId(int $productId): void
    {
        if (!isset($_SESSION['compare'])) {
            $_SESSION['compare'] = [];
        }

        $productModel = $this->model('Product');
        $newProduct = $productModel->getById($productId);
        
        if (!$newProduct) {
            flash('error', 'Sản phẩm không tồn tại.');
            return;
        }

        if (in_array($productId, $_SESSION['compare'], true)) {
            flash('info', 'Sản phẩm đã có trong danh sách so sánh.');
        } elseif (count($_SESSION['compare']) >= 4) {
            flash('error', 'Chỉ có thể so sánh tối đa 4 sản phẩm cùng lúc.');
        } else {
            // Kiểm tra danh mục
            $isCategoryMatch = true;
            if (!empty($_SESSION['compare'])) {
                $firstProduct = $productModel->getById($_SESSION['compare'][0]);
                if ($firstProduct && (int)$firstProduct['category_id'] !== (int)$newProduct['category_id']) {
                    $isCategoryMatch = false;
                }
            }

            if (!$isCategoryMatch) {
                flash('error', 'Chỉ có thể so sánh các sản phẩm cùng danh mục.');
            } else {
                $_SESSION['compare'][] = $productId;
                flash('success', 'Đã thêm sản phẩm vào danh sách so sánh (Tối đa 4 sản phẩm).');
            }
        }
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

        $categoryIdFilter = 0;
        if (!empty($_SESSION['compare'])) {
            $productModel = $this->model('Product');
            $firstProduct = $productModel->getById($_SESSION['compare'][0]);
            if ($firstProduct) {
                $categoryIdFilter = (int)$firstProduct['category_id'];
            }
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

            if ($categoryIdFilter > 0) {
                $sql .= " AND p.category_id = ?";
                $params[] = $categoryIdFilter;
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
                $r['image_url'] = productImageUrl($r['image'] ?? '', $r['category_slug'] ?? '', (int)$r['id']);
            }

            echo json_encode(['success' => true, 'data' => $rows]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
