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

        $ids = $_SESSION['compare'];
        $products = [];
        if (!empty($ids)) {
            $products = $this->model->getProductsByIds($ids);
        }

        $this->render('compare/index', [
            'pageTitle' => 'So sánh sản phẩm',
            'products' => $products,
            'flashes' => pullFlashes()
        ], false);
    }

    public function add(): void
    {
        if ($this->isPost()) {
            $productId = (int)($_POST['product_id'] ?? 0);
            if ($productId > 0) {
                if (!isset($_SESSION['compare'])) {
                    $_SESSION['compare'] = [];
                }

                $productModel = $this->model('Product');
                $newProduct = $productModel->getById($productId);
                
                if (!$newProduct) {
                    flash('error', 'Sản phẩm không tồn tại.');
                    $this->redirect($_SERVER['HTTP_REFERER'] ?? 'compare');
                    return;
                }

                if (in_array($productId, $_SESSION['compare'])) {
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
                        flash('success', 'Đã thêm sản phẩm vào danh sách so sánh.');
                    }
                }
            }
        }

        $this->redirect($_SERVER['HTTP_REFERER'] ?? 'compare');
    }

    public function remove(): void
    {
        if ($this->isPost()) {
            $productId = (int)($_POST['product_id'] ?? 0);
            if ($productId > 0 && isset($_SESSION['compare'])) {
                $key = array_search($productId, $_SESSION['compare']);
                if ($key !== false) {
                    unset($_SESSION['compare'][$key]);
                    $_SESSION['compare'] = array_values($_SESSION['compare']);
                    flash('success', 'Đã xóa sản phẩm khỏi danh sách so sánh.');
                }
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
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn ít nhất 2 sản phẩm để so sánh bằng AI.']);
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
}
