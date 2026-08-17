<?php
require_once ROOT_PATH . '/app/core/helpers.php';
require_once ROOT_PATH . '/app/models/Wishlist.php';

class WishlistController extends Controller
{
    private Wishlist $model;

    public function __construct()
    {
        $this->model = new Wishlist();
    }

    // =========================================================================
    // ===== Chức năng Quản lý Danh sách yêu thích Wishlist (UC21) =====
    // =========================================================================
    public function index(): void
    {
        $user = currentUser();
        if (!$user) {
            flash('error', 'Vui lòng đăng nhập để xem sản phẩm yêu thích.');
            $this->redirect('auth/login');
            return;
        }

        $items = $this->model->getItems((int)$user['id']);

        $this->render('wishlist/index', [
            'pageTitle' => 'Sản phẩm yêu thích',
            'items' => $items,
            'flashes' => pullFlashes()
        ], false);
    }

    public function add(): void
    {
        $user = currentUser();
        if (!$user) {
            flash('error', 'Vui lòng đăng nhập để lưu sản phẩm yêu thích.');
            $this->redirect('auth/login');
            return;
        }

        if ($this->isPost()) {
            $productId = (int)($_POST['product_id'] ?? 0);
            if ($productId > 0) {
                $ok = $this->model->add((int)$user['id'], $productId);
                if ($ok) {
                    flash('success', 'Đã thêm sản phẩm vào danh sách yêu thích.');
                } else {
                    flash('error', 'Không thể thêm sản phẩm.');
                }
            }
        }

        $this->redirect($_SERVER['HTTP_REFERER'] ?? 'wishlist');
        return;
    }

    public function remove(): void
    {
        $user = currentUser();
        if (!$user) {
            $this->redirect('auth/login');
            return;
        }

        if ($this->isPost()) {
            $productId = (int)($_POST['product_id'] ?? 0);
            if ($productId > 0) {
                $this->model->remove((int)$user['id'], $productId);
                flash('success', 'Đã xóa sản phẩm khỏi danh sách yêu thích.');
            }
        }

        $this->redirect('wishlist');
        return;
    }

    public function toggle(): void
    {
        header('Content-Type: application/json');
        $user = currentUser();
        if (!$user) {
            echo json_encode([
                'success' => false,
                'requireLogin' => true,
                'message' => 'Vui lòng đăng nhập để lưu sản phẩm yêu thích.'
            ]);
            exit;
        }

        $productId = (int)($_POST['product_id'] ?? 0);
        if ($productId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Sản phẩm không hợp lệ.'
            ]);
            exit;
        }

        $res = $this->model->toggle((int)$user['id'], $productId);
        echo json_encode([
            'success' => true,
            'inWishlist' => $res['inWishlist'],
            'count' => $res['count'],
            'message' => $res['message']
        ]);
        exit;
    }
    // =========================================================================
    // ===== Hoàn thành chức năng Quản lý Danh sách yêu thích Wishlist (UC21) =====
    // =========================================================================
}
