<?php

require_once ROOT_PATH . '/app/core/helpers.php';
require_once ROOT_PATH . '/app/models/Product.php';

class CartService
{
    private Product $products;

    public function __construct()
    {
        $this->products = new Product();
    }

    public function isAvailable(): bool
    {
        return $this->products->isAvailable();
    }

    /**
     * Session chỉ giữ product_id + quantity. Tên, ảnh, giá và tồn kho luôn được
     * hydrate lại từ database ở mỗi request.
     */
    public function getItems(): array
    {
        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            return [];
        }

        $ids = [];
        foreach ($cart as $key => $item) {
            $ids[] = (int)($item['product_id'] ?? $key);
        }

        $catalog = $this->products->getByIds($ids);
        $items = [];

        foreach ($cart as $key => $stored) {
            $productId = (int)($stored['product_id'] ?? $key);
            if (!isset($catalog[$productId])) {
                continue;
            }

            $product = $catalog[$productId];
            $stock = max(0, (int)($product['stock'] ?? 0));
            $quantity = max(1, (int)($stored['quantity'] ?? 1));
            if ($stock > 0) {
                $quantity = min($quantity, $stock);
            }

            $price = max(0, (float)($product['price'] ?? 0));
            $items[] = [
                'product_id' => $productId,
                'slug' => (string)($product['slug'] ?? ''),
                'name' => (string)($product['name'] ?? 'Sản phẩm'),
                'image' => (string)($product['image'] ?? ''),
                'brand_name' => (string)($product['brand_name'] ?? ''),
                'price' => $price,
                'old_price' => (float)($product['old_price'] ?? 0),
                'stock' => $stock,
                'quantity' => $quantity,
                'line_total' => $price * $quantity,
                'available' => $stock > 0,
            ];
        }

        return $items;
    }

    public function getSummary(): array
    {
        $items = $this->getItems();
        $subtotal = array_reduce(
            $items,
            fn (float $sum, array $item): float => $sum + (float)$item['line_total'],
            0.0
        );
        $shipping = shippingFee($subtotal);

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $subtotal + $shipping,
        ];
    }

    public function add(int $productId, int $quantity): array
    {
        if (!$this->isAvailable()) {
            return ['ok' => false, 'message' => 'Chưa kết nối được cơ sở dữ liệu.'];
        }

        $product = $this->products->getById($productId);
        if (!$product) {
            return ['ok' => false, 'message' => 'Sản phẩm không tồn tại hoặc đã ngừng bán.'];
        }

        $stock = max(0, (int)($product['stock'] ?? 0));
        if ($stock < 1) {
            return ['ok' => false, 'message' => 'Sản phẩm hiện đã hết hàng.'];
        }

        $quantity = max(1, $quantity);
        $cart = $_SESSION['cart'] ?? [];
        $currentQuantity = (int)($cart[$productId]['quantity'] ?? 0);
        $newQuantity = min($stock, $currentQuantity + $quantity);
        $cart[$productId] = [
            'product_id' => $productId,
            'quantity' => $newQuantity,
        ];
        $_SESSION['cart'] = $cart;

        $message = $newQuantity < ($currentQuantity + $quantity)
            ? 'Đã thêm số lượng tối đa còn trong kho.'
            : 'Đã thêm sản phẩm vào giỏ hàng.';

        return ['ok' => true, 'message' => $message];
    }

    public function update(int $productId, int $quantity): array
    {
        $cart = $_SESSION['cart'] ?? [];
        if (!isset($cart[$productId])) {
            return ['ok' => false, 'message' => 'Sản phẩm không còn trong giỏ hàng.'];
        }

        if ($quantity <= 0) {
            unset($cart[$productId]);
            $_SESSION['cart'] = $cart;
            return ['ok' => true, 'message' => 'Đã xóa sản phẩm khỏi giỏ hàng.'];
        }

        $product = $this->products->getById($productId);
        if (!$product) {
            return ['ok' => false, 'message' => 'Sản phẩm không còn khả dụng.'];
        }

        $stock = max(0, (int)($product['stock'] ?? 0));
        if ($stock < 1) {
            return ['ok' => false, 'message' => 'Sản phẩm đã hết hàng.'];
        }

        $cart[$productId] = [
            'product_id' => $productId,
            'quantity' => min($quantity, $stock),
        ];
        $_SESSION['cart'] = $cart;

        return ['ok' => true, 'message' => 'Đã cập nhật giỏ hàng.'];
    }

    public function remove(int $productId): void
    {
        $cart = $_SESSION['cart'] ?? [];
        unset($cart[$productId]);
        $_SESSION['cart'] = $cart;
    }
}

