<?php

class ProductController extends Controller
{
    /** Trang chi tiết sản phẩm: /product/detail/{slug} */
    public function detail(string $slug = ''): void
    {
        $productModel = $this->model('Product');
        $reviewModel  = $this->model('Review');
        $product = $productModel->getBySlug($slug);

        if (!$product) {
            http_response_code(404);
            $this->render('home/404', ['pageTitle' => 'Không tìm thấy sản phẩm']);
            return;
        }

        $specs = json_decode($product['specs'] ?? '{}', true) ?: [];
        $related = $productModel->getRelated((int)$product['category_id'], (int)$product['id'], 6);
        $productImages = $productModel->getProductImages((int)$product['id']);
        $reviews = $reviewModel->getByProduct((int)$product['id']);

        $canReview = false;
        $userId = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
        if ($userId) {
            $canReview = $reviewModel->hasPurchasedProduct($userId, (int)$product['id']);
        }

        // Logic lưu sản phẩm xem gần đây vào session
        if (!isset($_SESSION['recently_viewed'])) {
            $_SESSION['recently_viewed'] = [];
        }
        $recentlyViewed = $_SESSION['recently_viewed'];
        $productId = (int)$product['id'];
        $recentlyViewed = array_filter($recentlyViewed, function($id) use ($productId) {
            return $id !== $productId;
        });
        array_unshift($recentlyViewed, $productId);
        $recentlyViewed = array_slice($recentlyViewed, 0, 10);
        $_SESSION['recently_viewed'] = $recentlyViewed;

        $recentlyViewedProducts = [];
        if (!empty($recentlyViewed)) {
            $rvIds = array_filter($recentlyViewed, function($id) use ($productId) {
                return $id !== $productId;
            });
            if (!empty($rvIds)) {
                $recentlyViewedProducts = $productModel->getProductsByIds($rvIds);
            }
        }

        $this->render('product/detail', [
            'pageTitle'              => $product['name'],
            'product'                => $product,
            'specs'                  => $specs,
            'related'                => $related,
            'productImages'          => $productImages,
            'reviews'                => $reviews,
            'canReview'              => $canReview,
            'recentlyViewedProducts' => $recentlyViewedProducts,
        ]);
    }

    /** Xử lý gửi đánh giá: POST /product/review */
    public function review(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/');
            return;
        }

        $userId = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
        $userName = isset($_SESSION['user']['full_name']) ? $_SESSION['user']['full_name'] : '';
        
        $productId = (int)($_POST['product_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 5);
        $comment = trim($_POST['comment'] ?? '');

        if (!$userId) {
            flash('error', 'Bạn phải đăng nhập để viết đánh giá.');
            $this->redirect('/');
            return;
        }

        $reviewModel = $this->model('Review');
        $productModel = $this->model('Product');
        $product = $productModel->getById($productId);

        if (!$product) {
            $this->redirect('/');
            return;
        }

        if (!$reviewModel->hasPurchasedProduct($userId, $productId)) {
            flash('error', 'Chỉ những khách hàng đã mua sản phẩm này mới được đánh giá.');
            $this->redirect('product/detail/' . $product['slug']);
            return;
        }

        if ($comment === '') {
            flash('error', 'Vui lòng nhập nội dung đánh giá.');
            $this->redirect('product/detail/' . $product['slug']);
            return;
        }

        if ($reviewModel->create($productId, $userId, $userName, $rating, $comment)) {
            flash('success', 'Cảm ơn bạn đã đánh giá sản phẩm!');
        } else {
            flash('error', 'Không thể lưu đánh giá. Vui lòng thử lại sau.');
        }

        $this->redirect('product/detail/' . $product['slug']);
    }

    /**
     * API: Trò chuyện AI về sản phẩm cụ thể
     * POST /product/ai-chat
     */
    public function chat(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $productId = (int)($_POST['product_id'] ?? 0);
        $question = trim($_POST['q'] ?? '');

        if ($productId <= 0 || $question === '') {
            echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
            exit;
        }

        $productModel = $this->model('Product');
        $product = $productModel->getById($productId);

        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại.']);
            exit;
        }

        require_once ROOT_PATH . '/app/services/GeminiService.php';
        require_once ROOT_PATH . '/app/services/ProductIntelligenceService.php';

        try {
            $answer = ProductIntelligenceService::chatProduct($product, $question);
            echo json_encode(['success' => true, 'answer' => $answer]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
