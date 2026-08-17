<?php

class AdminCouponController extends Controller
{
    // =========================================================================
    // ===== Chức năng Admin Quản lý Mã giảm giá Coupon (UC32) =====
    // =========================================================================
    public function index(): void
    {
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $coupons = [];
        if ($db) {
            $stmt = $db->query('SELECT * FROM coupons ORDER BY id DESC');
            $coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->renderAdmin('admin/coupons/index', [
            'pageTitle'  => 'Quản lý Mã giảm giá (Coupon)',
            'activeMenu' => 'coupons',
            'coupons'    => $coupons
        ]);
    }

    public function create(): void
    {
        $this->renderAdmin('admin/coupons/create', [
            'pageTitle'  => 'Thêm mã giảm giá mới',
            'activeMenu' => 'coupons'
        ]);
    }

    public function store(): void
    {
        if (!$this->isPost()) {
            $this->redirect('admin/coupons');
            return;
        }

        $this->requireAdmin();
        if (!verifyCsrf($_POST['_csrf'] ?? null)) {
            flash('error', 'Phiên làm việc hết hạn, vui lòng thử lại.');
            $this->redirect('admin/coupons');
            return;
        }

        $code = strtoupper(trim($_POST['code'] ?? ''));
        $type = trim($_POST['type'] ?? 'fixed');
        $discountValue = (float)($_POST['discount_value'] ?? 0);
        $maxDiscount = trim($_POST['max_discount'] ?? '') !== '' ? (float)$_POST['max_discount'] : null;
        $minOrderValue = (float)($_POST['min_order_value'] ?? 0);
        $usageLimit = trim($_POST['usage_limit'] ?? '') !== '' ? (int)$_POST['usage_limit'] : null;
        $usageLimitPerUser = (int)($_POST['usage_limit_per_user'] ?? 1);
        $startDate = trim($_POST['start_date'] ?? '') !== '' ? date('Y-m-d 00:00:00', strtotime($_POST['start_date'])) : null;
        $endDate = trim($_POST['end_date'] ?? '') !== '' ? date('Y-m-d 00:00:00', strtotime($_POST['end_date'])) : null;
        $description = trim($_POST['description'] ?? '');
        $status = trim($_POST['status'] ?? 'active');

        if ($code === '' || $discountValue <= 0) {
            flash('error', 'Vui lòng nhập mã và giá trị giảm giá hợp lệ (> 0).');
            $this->redirect('admin/coupons/create');
            return;
        }

        // Quy tắc kiểm tra mức giảm giá tối đa 20%
        if ($type === 'percent' || $type === 'percentage') {
            if ($discountValue > 20) {
                flash('error', 'Mức giảm giá theo phần trăm tối đa chỉ được 20%. Bạn đã nhập ' . $discountValue . '%.');
                $this->redirect('admin/coupons/create');
                return;
            }
        } elseif ($type === 'fixed' || $type === 'fixed_amount') {
            if ($minOrderValue <= 0) {
                flash('error', 'Vui lòng nhập Giá trị đơn hàng tối thiểu (> 0) đối với mã giảm giá số tiền cố định để xác định hạn mức giảm tối đa 20%.');
                $this->redirect('admin/coupons/create');
                return;
            }
            $maxAllowedFixed = $minOrderValue * 0.20;
            if ($discountValue > $maxAllowedFixed) {
                flash('error', 'Số tiền giảm cố định (' . formatPrice($discountValue) . ') vượt quá 20% giá trị đơn hàng tối thiểu. Mức giảm tối đa cho phép là ' . formatPrice($maxAllowedFixed) . ' (20% của ' . formatPrice($minOrderValue) . ').');
                $this->redirect('admin/coupons/create');
                return;
            }
        }

        if ($startDate !== null && $endDate !== null && strtotime($endDate) < strtotime($startDate)) {
            flash('error', 'Thời gian kết thúc không thể trước thời gian bắt đầu.');
            $this->redirect('admin/coupons/create');
            return;
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if ($db) {
            // Check code duplicate
            $stmt = $db->prepare('SELECT id FROM coupons WHERE code = :code LIMIT 1');
            $stmt->execute([':code' => $code]);
            if ($stmt->fetch()) {
                flash('error', 'Mã giảm giá này đã tồn tại trong hệ thống.');
                $this->redirect('admin/coupons/create');
                return;
            }

            $stmt = $db->prepare(
                'INSERT INTO coupons (code, type, discount_value, max_discount, min_order_value, usage_limit, usage_limit_per_user, used_count, start_date, end_date, description, status)
                 VALUES (:code, :type, :discount_value, :max_discount, :min_order_value, :usage_limit, :usage_limit_per_user, 0, :start_date, :end_date, :description, :status)'
            );

            $success = $stmt->execute([
                ':code'                 => $code,
                ':type'                 => $type,
                ':discount_value'       => $discountValue,
                ':max_discount'         => $maxDiscount,
                ':min_order_value'      => $minOrderValue,
                ':usage_limit'          => $usageLimit,
                ':usage_limit_per_user' => $usageLimitPerUser,
                ':start_date'           => $startDate,
                ':end_date'             => $endDate,
                ':description'          => $description,
                ':status'               => $status
            ]);

            if ($success) {
                flash('success', 'Đã thêm mã giảm giá thành công!');
                $this->redirect('admin/coupons');
                return;
            } else {
                flash('error', 'Không thể lưu mã giảm giá.');
                $this->redirect('admin/coupons/create');
                return;
            }
        }
    }

    public function edit(string $id = ''): void
    {
        $id = (int)$id;
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $coupon = null;
        if ($db) {
            $stmt = $db->prepare('SELECT * FROM coupons WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$coupon) {
            flash('error', 'Mã giảm giá không tồn tại.');
            $this->redirect('admin/coupons');
            return;
        }

        $this->renderAdmin('admin/coupons/edit', [
            'pageTitle'  => 'Sửa mã giảm giá',
            'activeMenu' => 'coupons',
            'coupon'     => $coupon
        ]);
    }

    public function update(string $id = ''): void
    {
        $id = (int)$id;
        if (!$this->isPost()) {
            $this->redirect('admin/coupons');
            return;
        }

        $this->requireAdmin();
        if (!verifyCsrf($_POST['_csrf'] ?? null)) {
            flash('error', 'Phiên làm việc hết hạn, vui lòng thử lại.');
            $this->redirect('admin/coupons');
            return;
        }

        $code = strtoupper(trim($_POST['code'] ?? ''));
        $type = trim($_POST['type'] ?? 'fixed');
        $discountValue = (float)($_POST['discount_value'] ?? 0);
        $maxDiscount = trim($_POST['max_discount'] ?? '') !== '' ? (float)$_POST['max_discount'] : null;
        $minOrderValue = (float)($_POST['min_order_value'] ?? 0);
        $usageLimit = trim($_POST['usage_limit'] ?? '') !== '' ? (int)$_POST['usage_limit'] : null;
        $usageLimitPerUser = (int)($_POST['usage_limit_per_user'] ?? 1);
        $startDate = trim($_POST['start_date'] ?? '') !== '' ? date('Y-m-d 00:00:00', strtotime($_POST['start_date'])) : null;
        $endDate = trim($_POST['end_date'] ?? '') !== '' ? date('Y-m-d 00:00:00', strtotime($_POST['end_date'])) : null;
        $description = trim($_POST['description'] ?? '');
        $status = trim($_POST['status'] ?? 'active');

        if ($code === '' || $discountValue <= 0) {
            flash('error', 'Vui lòng nhập mã và giá trị giảm giá hợp lệ (> 0).');
            $this->redirect('admin/coupons/edit/' . $id);
            return;
        }

        // Quy tắc kiểm tra mức giảm giá tối đa 20%
        if ($type === 'percent' || $type === 'percentage') {
            if ($discountValue > 20) {
                flash('error', 'Mức giảm giá theo phần trăm tối đa chỉ được 20%. Bạn đã nhập ' . $discountValue . '%.');
                $this->redirect('admin/coupons/edit/' . $id);
                return;
            }
        } elseif ($type === 'fixed' || $type === 'fixed_amount') {
            if ($minOrderValue <= 0) {
                flash('error', 'Vui lòng nhập Giá trị đơn hàng tối thiểu (> 0) đối với mã giảm giá số tiền cố định để xác định hạn mức giảm tối đa 20%.');
                $this->redirect('admin/coupons/edit/' . $id);
                return;
            }
            $maxAllowedFixed = $minOrderValue * 0.20;
            if ($discountValue > $maxAllowedFixed) {
                flash('error', 'Số tiền giảm cố định (' . formatPrice($discountValue) . ') vượt quá 20% giá trị đơn hàng tối thiểu. Mức giảm tối đa cho phép là ' . formatPrice($maxAllowedFixed) . ' (20% của ' . formatPrice($minOrderValue) . ').');
                $this->redirect('admin/coupons/edit/' . $id);
                return;
            }
        }

        if ($startDate !== null && $endDate !== null && strtotime($endDate) < strtotime($startDate)) {
            flash('error', 'Thời gian kết thúc không thể trước thời gian bắt đầu.');
            $this->redirect('admin/coupons/edit/' . $id);
            return;
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if ($db) {
            // Check code duplicate trừ chính nó
            $stmt = $db->prepare('SELECT id FROM coupons WHERE code = :code AND id != :id LIMIT 1');
            $stmt->execute([':code' => $code, ':id' => $id]);
            if ($stmt->fetch()) {
                flash('error', 'Mã giảm giá này đã tồn tại trong hệ thống.');
                $this->redirect('admin/coupons/edit/' . $id);
                return;
            }

            $stmt = $db->prepare(
                'UPDATE coupons SET code = :code, type = :type, discount_value = :discount_value,
                                    max_discount = :max_discount, min_order_value = :min_order_value,
                                    usage_limit = :usage_limit, usage_limit_per_user = :usage_limit_per_user,
                                    start_date = :start_date, end_date = :end_date, description = :description,
                                    status = :status
                 WHERE id = :id'
            );

            $success = $stmt->execute([
                ':code'                 => $code,
                ':type'                 => $type,
                ':discount_value'       => $discountValue,
                ':max_discount'         => $maxDiscount,
                ':min_order_value'      => $minOrderValue,
                ':usage_limit'          => $usageLimit,
                ':usage_limit_per_user' => $usageLimitPerUser,
                ':start_date'           => $startDate,
                ':end_date'             => $endDate,
                ':description'          => $description,
                ':status'               => $status,
                ':id'                   => $id
            ]);

            if ($success) {
                flash('success', 'Đã cập nhật mã giảm giá thành công!');
                $this->redirect('admin/coupons');
                return;
            } else {
                flash('error', 'Không thể cập nhật mã giảm giá.');
                $this->redirect('admin/coupons/edit/' . $id);
                return;
            }
        }
    }

    public function delete(string $id = ''): void
    {
        $id = (int)$id;
        if (!$this->isPost()) {
            $this->redirect('admin/coupons');
            return;
        }

        $this->requireAdmin();
        if (!verifyCsrf($_POST['_csrf'] ?? null)) {
            flash('error', 'Phiên làm việc hết hạn, vui lòng thử lại.');
            $this->redirect('admin/coupons');
            return;
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if ($db) {
            $stmt = $db->prepare("UPDATE coupons SET status = 'inactive' WHERE id = :id");
            if ($stmt->execute([':id' => $id])) {
                flash('warning', 'Hệ thống đã khóa tính năng xóa cứng. Đã tự động chuyển trạng thái mã giảm giá sang Tạm khoá.');
            } else {
                flash('error', 'Không thể tạm khoá mã giảm giá.');
            }
        }

        $this->redirect('admin/coupons');
        return;
    }

    /** Toggle trạng thái Bật/Tắt mã giảm giá (POST /admin/coupons/toggle-status/{id}) */
    public function toggleStatus(string $id = ''): void
    {
        $adminUser = $this->requireApiAdmin();
        $id = (int)$id;

        if (!$this->isPost()) {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ.']);
            exit;
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();
        if (!$db) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi kết nối CSDL.']);
            exit;
        }

        $stmt = $db->prepare("SELECT status, code FROM coupons WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $cp = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cp) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Mã giảm giá không tồn tại.']);
            exit;
        }

        $newStatus = ($cp['status'] === 'active') ? 'inactive' : 'active';
        $upStmt = $db->prepare("UPDATE coupons SET status = :status WHERE id = :id");
        $upStmt->execute([':status' => $newStatus, ':id' => $id]);

        echo json_encode([
            'success'      => true,
            'message'      => 'Đã ' . ($newStatus === 'active' ? 'kích hoạt' : 'tạm khoá') . ' mã giảm giá ' . $cp['code'],
            'new_status'   => $newStatus,
            'status_label' => $newStatus === 'active' ? 'Hoạt động' : 'Tạm khoá'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    // =========================================================================
    // ===== Hoàn thành chức năng Admin Quản lý Mã giảm giá Coupon (UC32) =====
    // =========================================================================
}
