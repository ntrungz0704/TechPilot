<?php

require_once ROOT_PATH . '/app/core/helpers.php';

class CartController extends Controller
{
    private function getDbConnection()
    {
        require_once ROOT_PATH . '/config/database.php';
        return Database::getConnection();
    }

    private function getOrCreateCartId(int $userId, PDO $db): int
    {
        $stmt = $db->prepare("SELECT id FROM carts WHERE user_id = :user_id AND status = 'active' LIMIT 1");
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
            SELECT ci.product_id, ci.quantity, p.name, p.price, p.slug, p.image, p.stock
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.cart_id = :cart_id
        ");
        $stmt->execute([':cart_id' => $cartId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $sessionCart = [];
        foreach ($items as $item) {
            $sessionCart[(int)$item['product_id']] = [
                'product_id' => (int)$item['product_id'],
                'slug' => $item['slug'],
                'name' => $item['name'],
                'price' => (float)$item['price'],
                'quantity' => (int)$item['quantity'],
                'line_total' => (float)$item['price'] * (int)$item['quantity'],
                'image' => $item['image'],
                'stock' => (int)$item['stock'],
            ];
        }
        $_SESSION['cart'] = $sessionCart;
    }

    public function index(): void
    {
        $user = currentUser();
        if (!$user) {
            flash('error', 'Vui lòng đăng nhập để xem giỏ hàng.');
            $this->redirect('auth/login?redirect=/cart');
            return;
        }

        $db = $this->getDbConnection();
        if ($db) {
            $this->syncCartSession((int)$user['id'], $db);
        }

        $cartItems = $_SESSION['cart'] ?? [];
        $subtotal = 0.0;
        foreach ($cartItems as &$item) {
            $quantity = max(1, (int)($item['quantity'] ?? 1));
            $price = (float)($item['price'] ?? 0);
            $item['quantity'] = $quantity;
            $item['line_total'] = $price * $quantity;
            $subtotal += $item['line_total'];
        }
        unset($item);

        $this->render('cart', [
            'pageTitle' => 'Giỏ hàng',
            'cartItems' => array_values($cartItems),
            'subtotal' => $subtotal,
            'shipping' => 0.0,
            'total' => $subtotal,
        ]);
    }

    public function add(): void
    {
        $user = currentUser();
        if (!$user) {
            $slug = trim($_POST['slug'] ?? '');
            $redirectUrl = !empty($slug) ? '/product/detail/' . $slug : '/cart';
            flash('error', 'Vui lòng đăng nhập để thực hiện chức năng này.');
            $this->redirect('auth/login?redirect=' . urlencode($redirectUrl));
            return;
        }

        if (!$this->isPost()) {
            $this->redirect('cart');
        }

        $productId = (int)($_POST['product_id'] ?? 0);
        $slug = trim($_POST['slug'] ?? '');
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));

        $db = $this->getDbConnection();
        if (!$db) {
            flash('error', 'Không thể kết nối cơ sở dữ liệu.');
            $this->redirect('cart');
            return;
        }

        $productModel = $this->model('Product');
        $product = null;
        if ($productId > 0) {
            $product = $productModel->getById($productId);
        }
        if (!$product && $slug !== '') {
            $product = $productModel->getBySlug($slug);
        }

        if (!$product || ($product['status'] ?? 'active') !== 'active') {
            flash('error', 'Sản phẩm không hợp lệ hoặc đã dừng bán.');
            $this->redirect('cart');
            return;
        }

        $stock = (int)($product['stock'] ?? 0);
        $productId = (int)$product['id'];
        $cartId = $this->getOrCreateCartId((int)$user['id'], $db);

        // Kiểm tra số lượng tồn kho hiện tại trong giỏ hàng
        $stmt = $db->prepare("SELECT quantity FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id LIMIT 1");
        $stmt->execute([':cart_id' => $cartId, ':product_id' => $productId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        $newQty = $quantity;
        if ($existing) {
            $newQty += (int)$existing['quantity'];
        }

        if ($newQty > $stock) {
            flash('error', 'Số lượng sản phẩm trong giỏ hàng vượt quá số lượng tồn kho (' . $stock . ' sản phẩm).');
            $this->redirect('cart');
            return;
        }

        if ($existing) {
            $stmt = $db->prepare("UPDATE cart_items SET quantity = :qty WHERE cart_id = :cart_id AND product_id = :product_id");
            $stmt->execute([':qty' => $newQty, ':cart_id' => $cartId, ':product_id' => $productId]);
        } else {
            $stmt = $db->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (:cart_id, :product_id, :qty)");
            $stmt->execute([':cart_id' => $cartId, ':product_id' => $productId, ':qty' => $newQty]);
        }

        $this->syncCartSession((int)$user['id'], $db);

        if (isset($_GET['buynow']) && $_GET['buynow'] == '1') {
            $this->redirect('checkout');
        } else {
            $this->redirect('cart');
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

        // Kiểm tra tồn kho
        $productModel = $this->model('Product');
        $product = $productModel->getById($productId);
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
        if ($db && $productId > 0) {
            $cartId = $this->getOrCreateCartId((int)$user['id'], $db);
            $stmt = $db->prepare("DELETE FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id");
            $stmt->execute([':cart_id' => $cartId, ':product_id' => $productId]);
            
            $this->syncCartSession((int)$user['id'], $db);
        }

        $this->redirect('cart');
    }
}
