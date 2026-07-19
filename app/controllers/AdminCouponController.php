<?php

class AdminCouponController extends Controller
{
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
        }

        $code = strtoupper(trim($_POST['code'] ?? ''));
        $type = trim($_POST['type'] ?? 'fixed');
        $discountValue = (float)($_POST['discount_value'] ?? 0);
        $maxDiscount = trim($_POST['max_discount'] ?? '') !== '' ? (float)$_POST['max_discount'] : null;
        $minOrderValue = (float)($_POST['min_order_value'] ?? 0);
        $usageLimit = trim($_POST['usage_limit'] ?? '') !== '' ? (int)$_POST['usage_limit'] : null;
        $usageLimitPerUser = (int)($_POST['usage_limit_per_user'] ?? 1);
        $startDate = trim($_POST['start_date'] ?? '') !== '' ? $_POST['start_date'] : null;
        $endDate = trim($_POST['end_date'] ?? '') !== '' ? $_POST['end_date'] : null;
        $description = trim($_POST['description'] ?? '');
        $status = trim($_POST['status'] ?? 'active');

        if ($code === '' || $discountValue <= 0) {
            flash('error', 'Vui lòng nhập mã và giá trị giảm giá hợp lệ (> 0).');
            $this->redirect('admin/coupons/create');
            return;
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
            } else {
                flash('error', 'Không thể lưu mã giảm giá.');
                $this->redirect('admin/coupons/create');
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
        }

        $code = strtoupper(trim($_POST['code'] ?? ''));
        $type = trim($_POST['type'] ?? 'fixed');
        $discountValue = (float)($_POST['discount_value'] ?? 0);
        $maxDiscount = trim($_POST['max_discount'] ?? '') !== '' ? (float)$_POST['max_discount'] : null;
        $minOrderValue = (float)($_POST['min_order_value'] ?? 0);
        $usageLimit = trim($_POST['usage_limit'] ?? '') !== '' ? (int)$_POST['usage_limit'] : null;
        $usageLimitPerUser = (int)($_POST['usage_limit_per_user'] ?? 1);
        $startDate = trim($_POST['start_date'] ?? '') !== '' ? $_POST['start_date'] : null;
        $endDate = trim($_POST['end_date'] ?? '') !== '' ? $_POST['end_date'] : null;
        $description = trim($_POST['description'] ?? '');
        $status = trim($_POST['status'] ?? 'active');

        if ($code === '' || $discountValue <= 0) {
            flash('error', 'Vui lòng nhập mã và giá trị giảm giá hợp lệ (> 0).');
            $this->redirect('admin/coupons/edit/' . $id);
            return;
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
            } else {
                flash('error', 'Không thể cập nhật mã giảm giá.');
                $this->redirect('admin/coupons/edit/' . $id);
            }
        }
    }

    public function delete(string $id = ''): void
    {
        $id = (int)$id;
        if (!$this->isPost()) {
            $this->redirect('admin/coupons');
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if ($db) {
            $stmt = $db->prepare('DELETE FROM coupons WHERE id = :id');
            if ($stmt->execute([':id' => $id])) {
                flash('success', 'Xoá mã giảm giá thành công!');
            } else {
                flash('error', 'Không thể xoá mã giảm giá.');
            }
        }

        $this->redirect('admin/coupons');
    }
}
