<?php

require_once ROOT_PATH . '/app/core/helpers.php';
require_once ROOT_PATH . '/app/services/CartService.php';

class CheckoutController extends Controller
{
    private function requireAuthenticatedPage(string $redirect = '/checkout'): ?array
    {
        $user = currentUser();

        if (!$user || empty($user['id'])) {
            flash('error', 'Vui lòng đăng nhập để tiếp tục thanh toán.');
            $this->redirect(
                'auth/login?redirect=' . urlencode($redirect)
            );
            return null;
        }

        return $user;
    }

    private function requireAuthenticatedJson(): ?array
    {
        $user = currentUser();

        if (!$user || empty($user['id'])) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');

            echo json_encode([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để sử dụng chức năng này.',
            ], JSON_UNESCAPED_UNICODE);

            return null;
        }

        return $user;
    }

    private function getCartSummary(): array
    {
        return (new CartService())->getSummary();
    }

    private function findActiveCoupon(PDO $db, string $code): array|false
    {
        $stmt = $db->prepare(
            "SELECT * FROM coupons
             WHERE code = :code
               AND status = 'active'
               AND start_date <= NOW()
               AND end_date >= NOW()
             LIMIT 1"
        );
        $stmt->execute([':code' => $code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function index(): void
    {
        $user = $this->requireAuthenticatedPage('/checkout');

        if ($user === null) {
            return;
        }
        $summary = $this->getCartSummary();
        if (empty($summary['items'])) {
            $this->redirect('cart');
            return;
        }

        if (!($summary['can_checkout'] ?? false)) {
            unset($_SESSION['applied_coupon']);
            flash('error', 'Vui lòng xóa sản phẩm hết hàng trước khi thanh toán.');
            $this->redirect('cart');
            return;
        }

        $items = $summary['items'];
        $subtotal = (float)$summary['subtotal'];
        $shipping = (float)$summary['shipping'];

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        // Xử lý mã giảm giá đang được áp dụng từ session (nếu có)
        $appliedCoupon = $_SESSION['applied_coupon'] ?? null;
        $discountAmount = 0.0;
        if ($appliedCoupon && $db) {
            $coupon = $this->findActiveCoupon($db, (string)($appliedCoupon['code'] ?? ''));
            if ($coupon && $subtotal >= (float)$coupon['min_order_value']) {
                $discountAmount = calculateCouponDiscount($coupon, $subtotal);
                $_SESSION['applied_coupon'] = [
                    'code' => (string)$coupon['code'],
                    'discount' => $discountAmount,
                    'id' => (int)$coupon['id'],
                ];
                $appliedCoupon = $_SESSION['applied_coupon'];
            } else {
                unset($_SESSION['applied_coupon']);
                $appliedCoupon = null;
            }
        }

        $total = max(0.0, $subtotal - $discountAmount + $shipping);

        // Sinh submit token để chống double submit đơn hàng
        if (empty($_SESSION['submit_token'])) {
            $_SESSION['submit_token'] = bin2hex(random_bytes(16));
        }

        // Lấy danh sách Mã giảm giá khả dụng
        $availableCoupons = [];
        $savedAddresses = [];
        if ($db) {
            $cStmt = $db->prepare("SELECT * FROM coupons WHERE status = 'active' AND start_date <= NOW() AND end_date >= NOW() ORDER BY min_order_value ASC");
            $cStmt->execute();
            $availableCoupons = $cStmt->fetchAll(PDO::FETCH_ASSOC);

            // Chỉ truy vấn địa chỉ đã lưu khi người dùng đã đăng nhập.
            // Guest sẽ nhận $savedAddresses = [] và tự nhập địa chỉ tại form.
            if ($user !== null && isset($user['id'])) {
                $addrStmt = $db->prepare("SELECT * FROM user_addresses WHERE user_id = :uid ORDER BY is_default DESC, id DESC");
                $addrStmt->execute([':uid' => $user['id']]);
                $savedAddresses = $addrStmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        $this->render('checkout', [
            'pageTitle' => 'Thanh toán',
            'cartItems' => $items,
            'subtotal' => $subtotal,
            'discountAmount' => $discountAmount,
            'appliedCoupon' => $appliedCoupon,
            'shipping' => $shipping,
            'total' => $total,
            'availableCoupons' => $availableCoupons,
            'savedAddresses' => $savedAddresses
        ]);

        unset($_SESSION['checkout_error']);
    }

    public function apply_coupon(): void
    {
        header('Content-Type: application/json');

        if ($this->requireAuthenticatedJson() === null) {
            return;
        }

        if (!$this->isPost()) {
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
            exit;
        }

        $code = trim($_POST['coupon_code'] ?? '');
        $summary = $this->getCartSummary();
        $subtotal = (float)$summary['subtotal'];

        if (empty($summary['items'])) {
            echo json_encode(['success' => false, 'message' => 'Giỏ hàng không còn sản phẩm hợp lệ.']);
            exit;
        }

        if (!($summary['can_checkout'] ?? false)) {
            unset($_SESSION['applied_coupon']);
            echo json_encode([
                'success' => false,
                'message' => 'Vui lòng xóa sản phẩm hết hàng trước khi áp dụng mã giảm giá.',
            ]);
            exit;
        }

        if ($code === '') {
            unset($_SESSION['applied_coupon']);
            $shipping = (float)$summary['shipping'];
            $total = $subtotal + $shipping;
            echo json_encode([
                'success' => true,
                'message' => 'Đã gỡ mã giảm giá.',
                'discount' => 0,
                'discount_formatted' => '-0đ',
                'new_total' => $total,
                'new_total_formatted' => formatPrice($total),
                'removed' => true
            ]);
            exit;
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();
        if (!$db) {
            echo json_encode(['success' => false, 'message' => 'Không thể kết nối cơ sở dữ liệu.']);
            exit;
        }

        // Tìm coupon active
        $coupon = $this->findActiveCoupon($db, $code);

        if (!$coupon) {
            echo json_encode(['success' => false, 'message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn.']);
            exit;
        }

        $usedQty = (int)$coupon['used_count'];
        $maxQty = $coupon['usage_limit'] !== null ? (int)$coupon['usage_limit'] : null;
        if ($maxQty !== null && $usedQty >= $maxQty) {
            echo json_encode(['success' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng.']);
            exit;
        }

        // Kiểm tra xem mỗi tài khoản đã dùng mã này quá số lần cho phép chưa
        $user = currentUser();
        if ($user) {
            $userUsageStmt = $db->prepare("
                SELECT COUNT(*) FROM orders 
                WHERE user_id = :user_id AND coupon_id = :coupon_id AND status != 'cancelled'
            ");
            $userUsageStmt->execute([':user_id' => (int)$user['id'], ':coupon_id' => (int)$coupon['id']]);
            $usedByUser = (int)$userUsageStmt->fetchColumn();

            $maxPerUser = $coupon['usage_limit_per_user'] !== null ? (int)$coupon['usage_limit_per_user'] : 1;
            if ($usedByUser >= $maxPerUser) {
                echo json_encode(['success' => false, 'message' => 'Tài khoản của bạn đã sử dụng mã giảm giá này cho một đơn hàng trước đây.']);
                exit;
            }
        }

        $minOrder = (float)$coupon['min_order_value'];
        if ($subtotal < $minOrder) {
            echo json_encode(['success' => false, 'message' => 'Đơn hàng chưa đạt giá trị tối thiểu ' . formatPrice($minOrder) . ' để áp dụng mã này.']);
            exit;
        }

        $discount = calculateCouponDiscount($coupon, $subtotal);

        $_SESSION['applied_coupon'] = [
            'code' => $code,
            'discount' => $discount,
            'id' => $coupon['id'],
        ];

        $shipping = (float)$summary['shipping'];
        $newTotal = max(0.0, $subtotal - $discount + $shipping);

        echo json_encode([
            'success' => true,
            'message' => 'Áp dụng mã giảm giá thành công!',
            'code' => $code,
            'discount' => $discount,
            'discount_formatted' => '-' . formatPrice($discount),
            'new_total' => $newTotal,
            'new_total_formatted' => formatPrice($newTotal)
        ]);
        exit;
    }

    public function remove_coupon(): void
    {
        header('Content-Type: application/json');

        if ($this->requireAuthenticatedJson() === null) {
            return;
        }

        if (!$this->isPost()) {
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
            exit;
        }

        $summary = $this->getCartSummary();
        $subtotal = (float)$summary['subtotal'];
        unset($_SESSION['applied_coupon']);

        $shipping = (float)$summary['shipping'];
        $total = $subtotal + $shipping;

        echo json_encode([
            'success' => true,
            'message' => 'Đã gỡ mã giảm giá.',
            'new_total' => $total,
            'new_total_formatted' => formatPrice($total)
        ]);
        exit;
    }

    public function submit(): void
    {
        $user = $this->requireAuthenticatedPage('/checkout');

        if ($user === null) {
            return;
        }

        if (!$this->isPost()) {
            $this->redirect('checkout');
            return;
        }

        $summary = $this->getCartSummary();
        $cart = $summary['items'];
        if (empty($cart)) {
            $this->redirect('cart');
            return;
        }

        if (!($summary['can_checkout'] ?? false)) {
            unset($_SESSION['applied_coupon']);
            flash('error', 'Vui lòng xóa sản phẩm hết hàng trước khi thanh toán.');
            $this->redirect('cart');
            return;
        }

        // Chống double submit đơn hàng bằng cách kiểm tra submit_token
        $submitToken = trim($_POST['submit_token'] ?? '');
        $savedToken = $_SESSION['submit_token'] ?? '';

        if (!empty($savedToken) && ($submitToken === '' || $submitToken !== $savedToken)) {
            $_SESSION['submit_token'] = bin2hex(random_bytes(16));
            $_SESSION['checkout_error'] = 'Trang thanh toán đã được làm mới. Vui lòng thử lại.';
            $this->redirect('checkout');
            return;
        }

        // Huỷ bỏ submit_token ngay lập tức để chặn các request tiếp theo
        unset($_SESSION['submit_token']);

        $customerName = trim($_POST['customer_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $note = trim($_POST['note'] ?? '');
        $paymentMethod = trim($_POST['payment_method'] ?? 'COD');

        if ($customerName === '' || $phone === '' || $address === '') {
            $_SESSION['submit_token'] = bin2hex(random_bytes(16));
            $_SESSION['checkout_error'] = 'Vui lòng điền đầy đủ Họ và tên người nhận, Số điện thoại và Địa chỉ nhận hàng.';
            $this->redirect('checkout');
            return;
        }

        if (!in_array($paymentMethod, ['COD', 'VNPAY'], true)) {
            $paymentMethod = 'COD';
        }

        if ($paymentMethod === 'VNPAY') {
            require_once ROOT_PATH . '/app/services/VnpayService.php';
            $vnpayService = new VnpayService();
            if (!$vnpayService->isConfigured()) {
                $_SESSION['submit_token'] = bin2hex(random_bytes(16));
                $_SESSION['checkout_error'] = 'Thanh toán qua VNPay tạm thời chưa khả dụng trên môi trường thử nghiệm. Vui lòng chọn phương thức Thanh toán khi nhận hàng (COD).';
                $this->redirect('checkout');
                return;
            }
        }

        $subtotal = (float)$summary['subtotal'];

        $couponCode = '';
        $discountAmount = 0.0;
        $couponId = null;

        if (isset($_SESSION['applied_coupon'])) {
            $applied = $_SESSION['applied_coupon'];
            $couponCode = $applied['code'];
            $discountAmount = (float)$applied['discount'];
            $couponId = (int)$applied['id'];
        }

        $shipping = (float)$summary['shipping'];
        $total = max(0.0, $subtotal - $discountAmount + $shipping);

        $orderModel = $this->model('Order');
        $order = $orderModel->create([
            'customer_name' => $customerName,
            'phone' => $phone,
            'address' => $address,
            'note' => $note,
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentMethod === 'VNPAY' ? 'pending' : 'unpaid',
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

        // Order là lớp bảo vệ cuối: luôn dùng lại tổng tiền đã được tính và khóa trong transaction.
        $cart = $order['items'] ?? $cart;
        $subtotal = (float)($order['subtotal'] ?? 0);
        $discountAmount = (float)($order['discount_amount'] ?? 0);
        $shipping = (float)($order['shipping_fee'] ?? 0);
        $total = (float)($order['total_amount'] ?? 0);

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();
        if ($db && $user) {
            $addrStmt = $db->prepare("SELECT COUNT(*) FROM user_addresses WHERE user_id = :uid");
            $addrStmt->execute([':uid' => $user['id']]);
            $addrCount = (int)$addrStmt->fetchColumn();
            
            $saveAddress = $_POST['save_address'] ?? '0';
            if ($addrCount === 0 || $saveAddress === '1') {
                $isDefault = ($addrCount === 0) ? 1 : 0;
                $insStmt = $db->prepare("INSERT INTO user_addresses (user_id, recipient_name, phone, address_line, province, is_default) VALUES (:uid, :name, :phone, :addr, :province, :default)");
                $insStmt->execute([
                    ':uid' => $user['id'],
                    ':name' => $customerName,
                    ':phone' => $phone,
                    ':addr' => $address,
                    ':province' => '',
                    ':default' => $isDefault
                ]);
            }
        }

        $_SESSION['last_order'] = [
            'customer_name' => $customerName,
            'phone' => $phone,
            'address' => $address,
            'note' => $note,
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentMethod === 'VNPAY' ? 'pending' : 'unpaid',
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
        if ($paymentMethod === 'VNPAY') {
            require_once ROOT_PATH . '/app/services/VnpayService.php';
            try {
                $paymentUrl = (new VnpayService())->createPaymentUrl([
                    'order_code' => $order['order_code'],
                    'total' => $total,
                ]);
                header('Location: ' . $paymentUrl);
                exit;
            } catch (Throwable $e) {
                $_SESSION['last_order']['payment_error'] = 'VNPay chưa được cấu hình. Vui lòng liên hệ cửa hàng.';
            }
        }
        $this->redirect('checkout/success');
    }

    public function success(): void
    {
        if ($this->requireAuthenticatedPage('/checkout') === null) {
            return;
        }

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
