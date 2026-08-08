<?php

require_once ROOT_PATH . '/app/core/helpers.php';
require_once ROOT_PATH . '/app/services/CartService.php';

class CartController extends Controller
{
    private function getDbConnection()
    {
        require_once ROOT_PATH . '/config/database.php';
        return Database::getConnection();
    }

    private function hasValidUser(array $user, PDO $db): bool
    {
        $userId = (int)($user['id'] ?? 0);
        if ($userId <= 0) return false;
        $stmt = $db->prepare("SELECT id FROM users WHERE id = :id AND status = 'active' LIMIT 1");
        $stmt->execute([':id' => $userId]);
        return (bool)$stmt->fetchColumn();
    }

    private function clearStaleLogin(): void
    {
        unset($_SESSION['user'], $_SESSION['cart']);
        flash('error', 'Phiên đăng nhập cũ không còn hợp lệ. Vui lòng đăng nhập hoặc đăng ký lại tài khoản.');
        $this->redirect('auth/login');
    }

    private function getOrCreateCartId(int $userId, PDO $db): int
    {
        $stmt = $db->prepare("SELECT id FROM carts WHERE user_id = :user_id AND status = 'active' ORDER BY id DESC LIMIT 1");
        $stmt->execute([':user_id' => $userId]);
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cart) {
            return (int)$cart['id'];
        }

        $stmt = $db->prepare("INSERT INTO carts (user_id, status) VALUES (:user_id, 'active')");
        $stmt->execute([':user_id' => $userId]);
        return (int)$db->lastInsertId();
    }

    private function syncCartSession(int $userId, PDO $db): void
    {
        $cartId = $this->getOrCreateCartId($userId, $db);
        
        $stmt = $db->prepare("
            SELECT ci.product_id, ci.quantity
            FROM cart_items ci
            WHERE ci.cart_id = :cart_id
        ");
        $stmt->execute([':cart_id' => $cartId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $sessionCart = [];
        foreach ($items as $item) {
            $sessionCart[(int)$item['product_id']] = [
                'product_id' => (int)$item['product_id'],
                'quantity' => (int)$item['quantity'],
            ];
        }
        $_SESSION['cart'] = $sessionCart;
    }

    // =========================================================================
    // ===== Chức năng Thêm, cập nhật & quản lý Giỏ hàng (Guest/Customer) (UC15) =====
    // =========================================================================

    public function index(): void
    {
        $user = currentUser();
        $db = $this->getDbConnection();

        if ($user) {
            if (($user['role'] ?? '') === 'admin') {
                flash('error', 'Tài khoản Quản trị viên (Admin) không được phép thực hiện mua hàng. Vui lòng sử dụng tài khoản Khách hàng.');
                $this->redirect('admin');
                return;
            }
            if ($db && !$this->hasValidUser($user, $db)) {
                $this->clearStaleLogin();
                return;
            }
            if ($db) {
                $this->syncCartSession((int)$user['id'], $db);
            }
        }

        $summary = (new CartService())->getSummary();

        $this->render('cart', [
            'pageTitle' => 'Giỏ hàng',
            'cartItems' => $summary['items'],
            'subtotal' => $summary['subtotal'],
            'shipping' => $summary['shipping'],
            'total' => $summary['total'],
            'hasUnavailableItems' => (bool)$summary['has_unavailable_items'],
            'canCheckout' => (bool)$summary['can_checkout'],
            'isGuest' => !$user,
            'flashes' => pullFlashes(),
        ]);
    }

    public function add(): void
    {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
               || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));

        $user = currentUser();
        if ($user && ($user['role'] ?? '') === 'admin') {
            $adminMsg = 'Tài khoản Quản trị viên (Admin) không được phép mua hàng. Vui lòng sử dụng tài khoản Khách hàng.';
            if ($isAjax) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => $adminMsg], JSON_UNESCAPED_UNICODE);
                return;
            }
            flash('error', $adminMsg);
            $this->redirect('admin');
            return;
        }

        if (!$this->isPost()) {
            if ($isAjax) {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
                return;
            }
            $this->redirect('cart');
            return;
        }

        if (!verifyCsrf()) {
            if ($isAjax) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ (CSRF Token mismatch).']);
                return;
            }
            flash('error', 'Yêu cầu không hợp lệ (CSRF Token mismatch). Vui lòng thử lại.');
            $this->redirect('cart');
            return;
        }

        $productId = (int)($_POST['product_id'] ?? 0);
        $slug = trim($_POST['slug'] ?? '');
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));
        $intent = trim($_POST['intent'] ?? 'add');

        $db = $this->getDbConnection();
        if (!$db) {
            if ($isAjax) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Không thể kết nối cơ sở dữ liệu.']);
                return;
            }
            flash('error', 'Không thể kết nối cơ sở dữ liệu.');
            $this->redirect('cart');
            return;
        }

        $productModel = $this->model('Product');
        $product = null;
        if ($productId > 0) {
            $product = $productModel->getActiveByIdStrict($productId);
        }
        if (!$product && $slug !== '') {
            $product = $productModel->getActiveBySlugStrict($slug);
        }

        if (!$product) {
            if ($isAjax) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Sản phẩm không hợp lệ hoặc đã dừng bán.']);
                return;
            }
            flash('error', 'Sản phẩm không hợp lệ hoặc đã dừng bán.');
            $this->redirect('cart');
            return;
        }

        $stock = (int)($product['stock'] ?? 0);
        $productId = (int)$product['id'];

        $user = currentUser();

        if (!$user) {
            $cartService = new CartService();
            $res = $cartService->storeGuestItem($productId, $quantity, $stock);
            
            $redirectUrl = !empty($slug) ? '/product/detail/' . $slug : '/';
            if (isset($_POST['return_url'])) {
                $ret = trim($_POST['return_url']);
                if (str_starts_with($ret, '/') && !str_contains($ret, '//')) {
                    $redirectUrl = $ret;
                }
            }

            if (!$res['ok']) {
                if ($isAjax) {
                    http_response_code(409);
                    echo json_encode(['success' => false, 'auth_required' => false, 'message' => $res['message']]);
                    return;
                }
                flash('error', $res['message']);
                $this->redirect(ltrim($redirectUrl, '/'));
                return;
            }

            if ($intent === 'buy_now') {
                $loginUrl = '/auth/login?redirect=' . urlencode('/checkout');
                if ($isAjax) {
                    http_response_code(401);
                    echo json_encode([
                        'success' => false,
                        'auth_required' => true,
                        'login_url' => $loginUrl,
                        'message' => 'Vui lòng đăng nhập để tiếp tục.'
                    ]);
                    return;
                }
                $this->redirect(ltrim($loginUrl, '/'));
                return;
            }

            // Normal add to cart for guest
            $successMsg = 'Đã thêm ' . ($product['name'] ?? 'sản phẩm') . ' vào giỏ hàng.';
            if ($isAjax) {
                echo json_encode([
                    'success' => true,
                    'message' => $successMsg,
                    'cart_count' => cartCount()
                ]);
                return;
            }
            flash('success', $successMsg);
            $this->redirect(ltrim($redirectUrl, '/'));
            return;
        }

        if (!$this->hasValidUser($user, $db)) {
            if ($isAjax) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Phiên đăng nhập cũ không còn hợp lệ.']);
                return;
            }
            $this->clearStaleLogin();
            return;
        }

        $cartId = $this->getOrCreateCartId((int)$user['id'], $db);

        try {
            $db->beginTransaction();
            $stmt = $db->prepare('SELECT quantity FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id FOR UPDATE');
            $stmt->execute([':cart_id' => $cartId, ':product_id' => $productId]);
            $currentQty = (int)($stmt->fetchColumn() ?: 0);
            $newQty = $currentQty + $quantity;

            if ($newQty > $stock) {
                throw new RuntimeException('Số lượng sản phẩm trong giỏ vượt quá tồn kho (' . $stock . ' sản phẩm).');
            }

            $stmt = $db->prepare(
                'INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (:cart_id, :product_id, :qty)
                 ON DUPLICATE KEY UPDATE quantity = VALUES(quantity), updated_at = CURRENT_TIMESTAMP'
            );
            $stmt->execute([':cart_id'=>$cartId, ':product_id'=>$productId, ':qty'=>$newQty]);
            $db->commit();
            $this->syncCartSession((int)$user['id'], $db);
            
            $successMsg = 'Đã thêm ' . ($product['name'] ?? 'sản phẩm') . ' vào giỏ hàng.';
            
            if ($isAjax) {
                echo json_encode([
                    'success' => true,
                    'message' => $successMsg,
                    'cart_count' => array_sum(array_column($_SESSION['cart'] ?? [], 'quantity')),
                    'product_id' => $productId
                ]);
                return;
            }

            flash('success', $successMsg);
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            if ($isAjax) {
                http_response_code(409); // Conflict
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                return;
            }
            flash('error', $e->getMessage());
            
            $redirectUrl = !empty($slug) ? 'product/detail/' . $slug : 'cart';
            if (isset($_POST['return_url'])) {
                $ret = trim($_POST['return_url']);
                if (str_starts_with($ret, '/') && !str_contains($ret, '//')) {
                    $redirectUrl = ltrim($ret, '/');
                }
            }
            $this->redirect($redirectUrl);
            return;
        }

        if ($intent === 'buy_now') {
            $this->redirect('checkout');
        } else {
            $redirectUrl = !empty($slug) ? 'product/detail/' . $slug : 'cart';
            if (isset($_POST['return_url'])) {
                $ret = trim($_POST['return_url']);
                if (str_starts_with($ret, '/') && !str_contains($ret, '//')) {
                    $redirectUrl = ltrim($ret, '/');
                }
            }
            $this->redirect($redirectUrl);
        }
    }

    public function update(): void
    {
        $user = currentUser();
        if (!$user) {
            flash('error', 'Vui lòng đăng nhập để cập nhật giỏ hàng.');
            $this->redirect('auth/login');
            return;
        }

        if (!$this->isPost()) {
            $this->redirect('cart');
        }

        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));

        $db = $this->getDbConnection();
        if (!$db || $productId <= 0) {
            $this->redirect('cart');
            return;
        }
        if (!$this->hasValidUser($user, $db)) {
            $this->clearStaleLogin();
            return;
        }

        // Kiểm tra tồn kho
        $productModel = $this->model('Product');
        $product = $productModel->getActiveByIdStrict($productId);
        if (!$product) {
            $this->redirect('cart');
            return;
        }

        $stock = (int)($product['stock'] ?? 0);
        if ($quantity > $stock) {
            flash('error', 'Số lượng cập nhật vượt quá tồn kho (' . $stock . ' sản phẩm).');
            $this->redirect('cart');
            return;
        }

        $cartId = $this->getOrCreateCartId((int)$user['id'], $db);
        $stmt = $db->prepare("UPDATE cart_items SET quantity = :qty WHERE cart_id = :cart_id AND product_id = :product_id");
        $stmt->execute([':qty' => $quantity, ':cart_id' => $cartId, ':product_id' => $productId]);

        $this->syncCartSession((int)$user['id'], $db);
        $this->redirect('cart');
    }

    public function remove(): void
    {
        $user = currentUser();
        if (!$user) {
            $this->redirect('auth/login');
            return;
        }

        if (!$this->isPost()) {
            $this->redirect('cart');
        }

        $productId = (int)($_POST['product_id'] ?? 0);

        $db = $this->getDbConnection();
        if ($db && !$this->hasValidUser($user, $db)) {
            $this->clearStaleLogin();
            return;
        }
        if ($db && $productId > 0) {
            $cartId = $this->getOrCreateCartId((int)$user['id'], $db);
            $stmt = $db->prepare("DELETE FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id");
            $stmt->execute([':cart_id' => $cartId, ':product_id' => $productId]);
            
            $this->syncCartSession((int)$user['id'], $db);
        }

        $this->redirect('cart');
    }
    // =========================================================================
    // ===== Hoàn thành chức năng Thêm, cập nhật & quản lý Giỏ hàng (UC15) =====
    // =========================================================================
}
