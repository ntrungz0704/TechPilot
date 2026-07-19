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

                if (in_array($productId, $_SESSION['compare'])) {
                    flash('info', 'Sản phẩm đã có trong danh sách so sánh.');
                } elseif (count($_SESSION['compare']) >= 3) {
                    flash('error', 'Chỉ có thể so sánh tối đa 3 sản phẩm cùng lúc.');
                } else {
                    $_SESSION['compare'][] = $productId;
                    flash('success', 'Đã thêm sản phẩm vào danh sách so sánh.');
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
}
