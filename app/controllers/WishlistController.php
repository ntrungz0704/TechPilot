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

    public function index(): void
    {
        $user = currentUser();
        if (!$user) {
            flash('error', 'Vui lòng đăng nhập để xem sản phẩm yêu thích.');
            $this->redirect('auth/login');
        }

        $items = $this->model->getItems((int)$user['id']);

        $this->render('wishlist/index', [
            'pageTitle' => 'Sản phẩm yêu thích',
            'items' => $items,
            'flashes' => pullFlashes()
        ]);
    }

    public function add(): void
    {
        $user = currentUser();
        if (!$user) {
            flash('error', 'Vui lòng đăng nhập để lưu sản phẩm yêu thích.');
            $this->redirect('auth/login');
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
    }

    public function remove(): void
    {
        $user = currentUser();
        if (!$user) {
            $this->redirect('auth/login');
        }

        if ($this->isPost()) {
            $productId = (int)($_POST['product_id'] ?? 0);
            if ($productId > 0) {
                $this->model->remove((int)$user['id'], $productId);
                flash('success', 'Đã xóa sản phẩm khỏi danh sách yêu thích.');
            }
        }

        $this->redirect('wishlist');
    }
}
