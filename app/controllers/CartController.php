<?php

require_once ROOT_PATH . '/app/core/helpers.php';

class CartController extends Controller
{
    public function index(): void
    {
        $cartItems = [];
        $subtotal = 0.0;
        $cart = $_SESSION['cart'] ?? [];

        foreach ($cart as $item) {
            $quantity = max(1, (int)($item['quantity'] ?? 1));
            $lineTotal = (float)($item['price'] ?? 0) * $quantity;
            $subtotal += $lineTotal;

            $cartItems[] = [
                'product_id' => (int)($item['product_id'] ?? 0),
                'slug' => $item['slug'] ?? '',
                'name' => $item['name'] ?? 'Sản phẩm',
                'price' => (float)($item['price'] ?? 0),
                'quantity' => $quantity,
                'line_total' => $lineTotal,
            ];
        }

        $this->render('cart', [
            'pageTitle' => 'Giỏ hàng',
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'shipping' => 0.0,
            'total' => $subtotal,
        ]);
    }

    public function add(): void
    {
        if (!$this->isPost()) {
            $this->redirect('cart');
        }

        $productId = (int)($_POST['product_id'] ?? 0);
        $slug = trim($_POST['slug'] ?? '');
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));

        $productModel = $this->model('Product');
        $product = null;

        if ($productId > 0) {
            $product = $productModel->getById($productId);
        }

        if (!$product && $slug !== '') {
            $product = $productModel->getBySlug($slug);
        }

        if (!$product) {
            $this->redirect('cart');
        }

        $cart = $_SESSION['cart'] ?? [];
        $cartKey = (int)($product['id'] ?? 0);

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $quantity;
        } else {
            $cart[$cartKey] = [
                'product_id' => (int)($product['id'] ?? 0),
                'slug' => $product['slug'] ?? '',
                'name' => $product['name'] ?? 'Sản phẩm',
                'price' => (float)($product['price'] ?? 0),
                'quantity' => $quantity,
            ];
        }

        $_SESSION['cart'] = $cart;
        if (isset($_GET['buynow']) && $_GET['buynow'] == '1') {
            $this->redirect('checkout');
        } else {
            $this->redirect('cart');
        }
    }

    public function update(): void
    {
        if (!$this->isPost()) {
            $this->redirect('cart');
        }

        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));

        if ($productId > 0) {
            $cart = $_SESSION['cart'] ?? [];
            if (isset($cart[$productId])) {
                $cart[$productId]['quantity'] = $quantity;
                $_SESSION['cart'] = $cart;
            }
        }

        $this->redirect('cart');
    }

    public function remove(): void
    {
        if (!$this->isPost()) {
            $this->redirect('cart');
        }

        $productId = (int)($_POST['product_id'] ?? 0);

        if ($productId > 0) {
            $cart = $_SESSION['cart'] ?? [];
            unset($cart[$productId]);
            $_SESSION['cart'] = $cart;
        }

        $this->redirect('cart');
    }
}
