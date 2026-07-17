<?php

class CheckoutController extends Controller
{
    public function index(): void
    {
        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            $this->redirect('cart');
        }

        $items = [];
        $subtotal = 0.0;

        foreach ($cart as $item) {
            $quantity = max(1, (int)($item['quantity'] ?? 1));
            $lineTotal = (float)($item['price'] ?? 0) * $quantity;
            $subtotal += $lineTotal;

            $items[] = [
                'product_id' => (int)($item['product_id'] ?? 0),
                'name' => $item['name'] ?? 'Sản phẩm',
                'price' => (float)($item['price'] ?? 0),
                'quantity' => $quantity,
                'line_total' => $lineTotal,
            ];
        }

        $shipping = $subtotal > 0 ? 30000 : 0;
        $total = $subtotal + $shipping;

        $this->render('checkout', [
            'pageTitle' => 'Thanh toán',
            'cartItems' => $items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $total,
        ]);
    }

    public function submit(): void
    {
        if (!$this->isPost()) {
            $this->redirect('checkout');
        }

        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            $this->redirect('cart');
        }

        $customerName = trim($_POST['customer_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $note = trim($_POST['note'] ?? '');
        $paymentMethod = trim($_POST['payment_method'] ?? 'COD');

        $subtotal = 0.0;
        foreach ($cart as $item) {
            $quantity = max(1, (int)($item['quantity'] ?? 1));
            $subtotal += (float)($item['price'] ?? 0) * $quantity;
        }

        $shipping = $subtotal > 0 ? 30000 : 0;
        $total = $subtotal + $shipping;

        $orderModel = $this->model('Order');
        $order = $orderModel->create([
            'customer_name' => $customerName,
            'phone' => $phone,
            'address' => $address,
            'note' => $note,
            'payment_method' => $paymentMethod,
            'subtotal' => $subtotal,
            'shipping_fee' => $shipping,
            'total_amount' => $total,
            'items' => $cart,
        ]);

        if (!$order) {
            $_SESSION['checkout_error'] = 'Không thể lưu đơn hàng. Vui lòng thử lại.';
            $this->redirect('checkout');
        }

        $_SESSION['last_order'] = [
            'customer_name' => $customerName,
            'phone' => $phone,
            'address' => $address,
            'note' => $note,
            'payment_method' => $paymentMethod,
            'order_code' => $order['order_code'] ?? '',
            'status' => $order['status'] ?? 'pending',
            'items' => $cart,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $total,
            'created_at' => date('d/m/Y H:i'),
        ];

        unset($_SESSION['cart']);
        unset($_SESSION['checkout_error']);
        $this->redirect('checkout/success');
    }

    public function success(): void
    {
        $order = $_SESSION['last_order'] ?? null;
        if (!$order) {
            $this->redirect('cart');
        }

        $this->render('checkout-success', [
            'pageTitle' => 'Đặt hàng thành công',
            'order' => $order,
        ]);
    }
}
