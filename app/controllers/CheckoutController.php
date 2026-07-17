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

        // Sinh submit token để chống double submit đơn hàng
        if (empty($_SESSION['submit_token'])) {
            $_SESSION['submit_token'] = bin2hex(random_bytes(16));
        }

        $this->render('checkout', [
            'pageTitle' => 'Thanh toán',
            'cartItems' => $items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $total,
        ]);
    }

    public function apply_coupon(): void
    {
        header('Content-Type: application/json');

        if (!$this->isPost()) {
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
            exit;
        }

        $code = trim($_POST['coupon_code'] ?? '');
        $subtotal = (float)($_POST['subtotal'] ?? 0);

        if ($code === '') {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã giảm giá.']);
            exit;
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();
        if (!$db) {
            echo json_encode(['success' => false, 'message' => 'Không thể kết nối cơ sở dữ liệu.']);
            exit;
        }

        // Tìm coupon active
        $stmt = $db->prepare('SELECT * FROM coupons WHERE code = :code AND status = \'active\' AND start_date <= NOW() AND end_date >= NOW() LIMIT 1');
        $stmt->execute([':code' => $code]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$coupon) {
            echo json_encode(['success' => false, 'message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn.']);
            exit;
        }

        $usedQty = (int)$coupon['used_qty'];
        $maxQty = (int)$coupon['quantity'];
        if ($usedQty >= $maxQty) {
            echo json_encode(['success' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng.']);
            exit;
        }

        $minOrder = (float)$coupon['min_order_value'];
        if ($subtotal < $minOrder) {
            echo json_encode(['success' => false, 'message' => 'Đơn hàng chưa đạt giá trị tối thiểu ' . number_format($minOrder, 0, ',', '.') . 'đ để áp dụng mã này.']);
            exit;
        }

        $discount = 0.0;
        $discountType = $coupon['discount_type'];
        $discountValue = (float)$coupon['discount_value'];

        if ($discountType === 'percentage') {
            $discount = $subtotal * ($discountValue / 100);
            $maxDiscount = (float)($coupon['max_discount_value'] ?? 0);
            if ($maxDiscount > 0 && $discount > $maxDiscount) {
                $discount = $maxDiscount;
            }
        } else {
            $discount = $discountValue;
        }

        if ($discount > $subtotal) {
            $discount = $subtotal;
        }

        $_SESSION['applied_coupon'] = [
            'code' => $code,
            'discount' => $discount,
            'id' => $coupon['id'],
        ];

        echo json_encode([
            'success' => true,
            'message' => 'Áp dụng mã giảm giá thành công!',
            'discount' => $discount,
            'discount_formatted' => '-' . number_format($discount, 0, ',', '.') . 'đ',
            'new_total' => $subtotal - $discount + ($subtotal > 0 ? 30000 : 0),
            'new_total_formatted' => number_format($subtotal - $discount + ($subtotal > 0 ? 30000 : 0), 0, ',', '.') . 'đ'
        ]);
        exit;
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

        // Chống double submit đơn hàng bằng cách kiểm tra submit_token
        $submitToken = trim($_POST['submit_token'] ?? '');
        $savedToken = $_SESSION['submit_token'] ?? '';

        if ($submitToken === '' || $submitToken !== $savedToken) {
            $_SESSION['checkout_error'] = 'Đơn hàng này đã được gửi hoặc yêu cầu không hợp lệ. Vui lòng kiểm tra lại giỏ hàng.';
            $this->redirect('cart');
            return;
        }

        // Huỷ bỏ submit_token ngay lập tức để chặn các request tiếp theo
        unset($_SESSION['submit_token']);

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

        $couponCode = '';
        $discountAmount = 0.0;
        $couponId = null;

        if (isset($_SESSION['applied_coupon'])) {
            $applied = $_SESSION['applied_coupon'];
            $couponCode = $applied['code'];
            $discountAmount = (float)$applied['discount'];
            $couponId = (int)$applied['id'];
        }

        $shipping = $subtotal > 0 ? 30000 : 0;
        $total = max(0.0, $subtotal - $discountAmount + $shipping);

        $orderModel = $this->model('Order');
        $order = $orderModel->create([
            'customer_name' => $customerName,
            'phone' => $phone,
            'address' => $address,
            'note' => $note,
            'payment_method' => $paymentMethod,
            'subtotal' => $subtotal,
            'shipping_fee' => $shipping,
            'discount_amount' => $discountAmount,
            'coupon_id' => $couponId,
            'total_amount' => $total,
            'items' => $cart,
        ]);

        if (!$order) {
            // Khôi phục submit_token để khách hàng có thể thử lại
            $_SESSION['submit_token'] = bin2hex(random_bytes(16));
            $_SESSION['checkout_error'] = 'Không thể lưu đơn hàng hoặc sản phẩm đã hết hàng. Vui lòng thử lại.';
            $this->redirect('checkout');
            return;
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
            'discount' => $discountAmount,
            'shipping' => $shipping,
            'total' => $total,
            'created_at' => date('d/m/Y H:i'),
        ];

        unset($_SESSION['cart']);
        unset($_SESSION['applied_coupon']);
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
