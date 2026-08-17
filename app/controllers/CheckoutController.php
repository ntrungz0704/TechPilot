<?php

require_once ROOT_PATH . '/app/core/helpers.php';
require_once ROOT_PATH . '/app/services/CartService.php';

class CheckoutController extends Controller
{
    protected function getVnpayService()
    {
        require_once ROOT_PATH . '/app/services/VnpayService.php';
        return new VnpayService();
    }

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

        if (($user['role'] ?? '') === 'admin') {
            flash('error', 'Tài khoản Quản trị viên (Admin) không được phép truy cập trang thanh toán mua hàng. Vui lòng sử dụng tài khoản Khách hàng.');
            $this->redirect('admin');
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

        if (($user['role'] ?? '') === 'admin') {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');

            echo json_encode([
                'success' => false,
                'message' => 'Tài khoản Quản trị viên (Admin) không được phép thực hiện thao tác mua hàng.',
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
            $availableCouponsRaw = $cStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($availableCouponsRaw as $ac) {
                $isDisabled = false;
                $disableReason = '';
                
                if ($ac['usage_limit'] !== null && (int)$ac['used_count'] >= (int)$ac['usage_limit']) {
                    $isDisabled = true;
                    $disableReason = 'Hết lượt dùng';
                }
                
                if (!$isDisabled && $user !== null && isset($user['id'])) {
                    $userUsageStmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = :user_id AND coupon_id = :coupon_id AND status != 'cancelled'");
                    $userUsageStmt->execute([':user_id' => (int)$user['id'], ':coupon_id' => (int)$ac['id']]);
                    $usedByUser = (int)$userUsageStmt->fetchColumn();
                    $maxPerUser = $ac['usage_limit_per_user'] !== null ? (int)$ac['usage_limit_per_user'] : 1;
                    if ($usedByUser >= $maxPerUser) {
                        $isDisabled = true;
                        $disableReason = 'Bạn đã dùng';
                    }
                }
                
                $ac['is_disabled'] = $isDisabled;
                $ac['disable_reason'] = $disableReason;
                $availableCoupons[] = $ac;
            }

            // Chỉ truy vấn địa chỉ đã lưu khi người dùng đã đăng nhập.
            // Guest sẽ nhận $savedAddresses = [] và tự nhập địa chỉ tại form.
            $defaultAddress = null;
            if ($user !== null && isset($user['id'])) {
                $uStmt = $db->prepare("SELECT id, full_name, email, phone, role FROM users WHERE id = :id LIMIT 1");
                $uStmt->execute([':id' => (int)$user['id']]);
                $dbUser = $uStmt->fetch(PDO::FETCH_ASSOC);
                if ($dbUser) {
                    $user = array_merge($user, $dbUser);
                    $_SESSION['user'] = $user;
                }

                $addrStmt = $db->prepare("SELECT * FROM user_addresses WHERE user_id = :uid ORDER BY is_default DESC, id DESC");
                $addrStmt->execute([':uid' => $user['id']]);
                $savedAddresses = $addrStmt->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($savedAddresses)) {
                    $defaultAddress = $savedAddresses[0];
                } else {
                    // Trích xuất địa chỉ từ đơn hàng gần nhất nếu tài khoản chưa có trong sổ địa chỉ
                    $lastOrderStmt = $db->prepare("SELECT customer_name, phone, address FROM orders WHERE user_id = :uid AND address != '' ORDER BY id DESC LIMIT 1");
                    $lastOrderStmt->execute([':uid' => $user['id']]);
                    $lastOrder = $lastOrderStmt->fetch(PDO::FETCH_ASSOC);
                    if ($lastOrder) {
                        $defaultAddress = [
                            'recipient_name' => $lastOrder['customer_name'],
                            'phone' => $lastOrder['phone'],
                            'address_line' => $lastOrder['address'],
                        ];
                    }
                }
            }
        }

        $checkoutInput = $_SESSION['checkout_input'] ?? [];
        unset($_SESSION['checkout_input']);

        $this->render('checkout', [
            'pageTitle' => 'Thanh toán',
            'cartItems' => $items,
            'subtotal' => $subtotal,
            'discountAmount' => $discountAmount,
            'appliedCoupon' => $appliedCoupon,
            'shipping' => $shipping,
            'total' => $total,
            'availableCoupons' => $availableCoupons,
            'savedAddresses' => $savedAddresses,
            'defaultAddress' => $defaultAddress,
            'checkoutInput' => $checkoutInput
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

        if (!verifyCsrf($_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf'] ?? null)) {
            echo json_encode(['success' => false, 'message' => 'Phiên làm việc hết hạn. Vui lòng tải lại trang.']);
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

        if (!verifyCsrf($_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf'] ?? null)) {
            echo json_encode(['success' => false, 'message' => 'Phiên làm việc hết hạn. Vui lòng tải lại trang.']);
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
    // =========================================================================
    // ===== Hoàn thành chức năng Áp dụng & Hủy mã giảm giá Coupon (UC17) =====
    // =========================================================================

    // =========================================================================
    // ===== Chức năng Xử lý Đặt hàng Checkout & Gửi Đơn (UC16) =====
    // =========================================================================
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

        $customerName  = trim($_POST['customer_name'] ?? '');
        $phone         = trim($_POST['phone'] ?? '');
        $province      = trim($_POST['province'] ?? '');
        $district      = trim($_POST['district'] ?? '');
        $ward          = trim($_POST['ward'] ?? '');
        $addressDetail = trim($_POST['address_detail'] ?? '');
        $address       = trim($_POST['address'] ?? '');
        $note          = trim($_POST['note'] ?? '');
        $paymentMethod = trim($_POST['payment_method'] ?? 'COD');
        $saveAddress   = $_POST['save_address'] ?? '0';
        $savedAddressId= $_POST['saved_address_id'] ?? '';

        // Tự động ghép địa chỉ đầy đủ từ 4 trường phân cấp hành chính nếu có
        if ($province !== '' || $district !== '' || $ward !== '' || $addressDetail !== '') {
            $addrParts = array_filter([$addressDetail, $ward, $district, $province], fn($p) => trim((string)$p) !== '');
            if (!empty($addrParts)) {
                $address = implode(', ', $addrParts);
            }
        }

        $inputData = [
            'customer_name'    => $customerName,
            'phone'            => $phone,
            'province'         => $province,
            'district'         => $district,
            'ward'             => $ward,
            'address_detail'   => $addressDetail,
            'address'          => $address,
            'note'             => $note,
            'payment_method'   => $paymentMethod,
            'save_address'     => $saveAddress,
            'saved_address_id' => $savedAddressId,
        ];

        if (!verifyCsrf($_POST['_csrf'] ?? null)) {
            $_SESSION['checkout_error'] = 'Phiên làm việc hết hạn, vui lòng thử lại.';
            $_SESSION['checkout_input'] = $inputData;
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
            $_SESSION['checkout_input'] = $inputData;
            $this->redirect('checkout');
            return;
        }

        // Huỷ bỏ submit_token ngay lập tức để chặn các request tiếp theo
        unset($_SESSION['submit_token']);

        if ($customerName === '' || $phone === '' || $address === '') {
            $_SESSION['submit_token'] = bin2hex(random_bytes(16));
            $_SESSION['checkout_error'] = 'Vui lòng điền đầy đủ Họ và tên người nhận, Số điện thoại và Địa chỉ nhận hàng (Tỉnh/Thành phố, Quận/Huyện, Phường/Xã, Số nhà/Đường).';
            $_SESSION['checkout_input'] = $inputData;
            $this->redirect('checkout');
            return;
        }

        // Kiểm tra định dạng số điện thoại Việt Nam (+84 với 10 hoặc 11 số)
        if (!isValidVietnamesePhone($phone)) {
            $_SESSION['submit_token'] = bin2hex(random_bytes(16));
            $_SESSION['checkout_error'] = 'Số điện thoại không hợp lệ! Vui lòng nhập số điện thoại định dạng +84 (10 hoặc 11 số, ví dụ: +84901234567 hoặc 0901234567).';
            $_SESSION['checkout_input'] = $inputData;
            $this->redirect('checkout');
            return;
        }

        // Chuẩn hóa số điện thoại về định dạng +84
        $phone = formatPhone($phone);

        if (!in_array($paymentMethod, ['COD', 'VNPAY'], true)) {
            $paymentMethod = 'COD';
        }

        if ($paymentMethod === 'VNPAY') {
            $vnpayService = $this->getVnpayService();
            if (!$vnpayService->isConfigured()) {
                $_SESSION['submit_token'] = bin2hex(random_bytes(16));
                $_SESSION['checkout_error'] = 'Thanh toán qua VNPay tạm thời chưa khả dụng trên môi trường thử nghiệm. Vui lòng chọn phương thức Thanh toán khi nhận hàng (COD).';
                $_SESSION['checkout_input'] = $inputData;
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
            'expected_total_amount' => $total,
            'items' => $cart,
        ]);

        if (!$order) {
            $_SESSION['submit_token'] = bin2hex(random_bytes(16));
            $err = $orderModel->getLastError();
            $_SESSION['checkout_error'] = !empty($err) ? $err : 'Không thể lưu đơn hàng. Vui lòng thử lại.';
            $_SESSION['checkout_input'] = $inputData;
            $this->redirect('checkout');
            return;
        }

        $cart = $order['items'] ?? $cart;
        $subtotal = (float)($order['subtotal'] ?? 0);
        $discountAmount = (float)($order['discount_amount'] ?? 0);
        $shipping = (float)($order['shipping_fee'] ?? 0);
        $total = (float)($order['total_amount'] ?? 0);

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();
        if ($db && $user && !empty($user['id'])) {
            $userId = (int)$user['id'];

            // Tự động cập nhật SĐT cho user nếu tài khoản chưa có SĐT
            if (!empty($phone)) {
                $upPhoneStmt = $db->prepare("UPDATE users SET phone = :phone WHERE id = :uid AND (phone IS NULL OR phone = '')");
                $upPhoneStmt->execute([':phone' => $phone, ':uid' => $userId]);
            }

            $addrStmt = $db->prepare("SELECT COUNT(*) FROM user_addresses WHERE user_id = :uid");
            $addrStmt->execute([':uid' => $userId]);
            $addrCount = (int)$addrStmt->fetchColumn();

            $saveAddress = $_POST['save_address'] ?? '0';
            // Lưu và làm địa chỉ mặc định khi mua lần đầu hoặc có tích chọn lưu
            if ($addrCount === 0 || $saveAddress === '1') {
                // Đặt các địa chỉ cũ về is_default = 0
                $resetStmt = $db->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = :uid");
                $resetStmt->execute([':uid' => $userId]);

                // Kiểm tra xem địa chỉ này đã tồn tại trong sổ địa chỉ chưa
                $checkExistingStmt = $db->prepare("SELECT id FROM user_addresses WHERE user_id = :uid AND address_line = :addr AND recipient_name = :name LIMIT 1");
                $checkExistingStmt->execute([':uid' => $userId, ':addr' => $address, ':name' => $customerName]);
                $existingId = $checkExistingStmt->fetchColumn();

                if ($existingId) {
                    // Cập nhật làm mặc định
                    $upStmt = $db->prepare("UPDATE user_addresses SET phone = :phone, is_default = 1 WHERE id = :id");
                    $upStmt->execute([':phone' => $phone, ':id' => $existingId]);
                } else {
                    // Thêm mới làm mặc định
                    $insStmt = $db->prepare("INSERT INTO user_addresses (user_id, recipient_name, phone, address_line, province, is_default) VALUES (:uid, :name, :phone, :addr, '', 1)");
                    $insStmt->execute([
                        ':uid' => $userId,
                        ':name' => $customerName,
                        ':phone' => $phone,
                        ':addr' => $address
                    ]);
                }
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
            try {
                $paymentUrl = $this->getVnpayService()->createPaymentUrl([
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
        return;
    }
    // =========================================================================
    // ===== Hoàn thành chức năng Đặt hàng Checkout (UC16) =====
    // =========================================================================

    public function success(): void
    {
        if ($this->requireAuthenticatedPage('/checkout') === null) {
            return;
        }

        $order = $_SESSION['last_order'] ?? null;
        if (!$order) {
            $this->redirect('cart');
            return;
        }

        $this->render('checkout-success', [
            'pageTitle' => 'Đặt hàng thành công',
            'order' => $order,
        ]);
    }
}
