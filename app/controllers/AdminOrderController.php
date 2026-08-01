<?php

class AdminOrderController extends Controller
{
    public function index(): void
    {
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $search = trim($_GET['search'] ?? '');
        $status = trim($_GET['status'] ?? '');

        $orders = [];
        $limit = 10;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $limit;
        $totalOrders = 0;

        if ($db) {
            $sql = 'SELECT * FROM orders WHERE 1=1';
            $countSql = 'SELECT COUNT(*) FROM orders WHERE 1=1';
            $params = [];

            if ($search !== '') {
                $sql .= ' AND (customer_name LIKE :search OR phone LIKE :search OR order_code LIKE :search)';
                $countSql .= ' AND (customer_name LIKE :search OR phone LIKE :search OR order_code LIKE :search)';
                $params[':search'] = '%' . $search . '%';
            }

            if ($status !== '') {
                $sql .= ' AND status = :status';
                $countSql .= ' AND status = :status';
                $params[':status'] = $status;
            }

            $countStmt = $db->prepare($countSql);
            $countStmt->execute($params);
            $totalOrders = (int)$countStmt->fetchColumn();

            $sql .= ' ORDER BY id DESC LIMIT :limit OFFSET :offset';
            $stmt = $db->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $totalPages = ceil($totalOrders / $limit);

        $this->renderAdmin('admin/orders/index', [
            'pageTitle'   => 'Quản lý đơn hàng',
            'activeMenu'  => 'orders',
            'orders'      => $orders,
            'search'      => $search,
            'status'      => $status,
            'page'        => $page,
            'totalPages'  => $totalPages,
            'totalOrders' => $totalOrders
        ]);
    }

    public function detail(string $id = ''): void
    {
        $id = (int)$id;
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $order = null;
        $items = [];
        if ($db) {
            // Lấy thông tin đơn hàng và mã coupon nếu có
            $stmt = $db->prepare(
                'SELECT o.*, c.code as coupon_code, c.discount_value, c.type as discount_type
                 FROM orders o
                 LEFT JOIN coupons c ON o.coupon_id = c.id
                 WHERE o.id = :id LIMIT 1'
            );
            $stmt->execute([':id' => $id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($order) {
                // Lấy các sản phẩm thuộc đơn hàng
                $stmt = $db->prepare(
                    'SELECT oi.*, p.name as product_name, p.image as product_image
                     FROM order_items oi
                     LEFT JOIN products p ON oi.product_id = p.id
                     WHERE oi.order_id = :order_id'
                );
                $stmt->execute([':order_id' => $id]);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        if (!$order) {
            flash('error', 'Đơn hàng không tồn tại.');
            $this->redirect('admin/orders');
            return;
        }

        $this->renderAdmin('admin/orders/detail', [
            'pageTitle'  => 'Chi tiết đơn hàng ' . $order['order_code'],
            'activeMenu' => 'orders',
            'order'      => $order,
            'items'      => $items
        ]);
    }

    public function updateStatus(string $id = ''): void
    {
        $this->requireAdmin();
        $id = (int)$id;
        if (!$this->isPost()) {
            $this->redirect('admin/orders');
        }

        $newStatus = trim($_POST['status'] ?? '');
        if ($newStatus === '') {
            $this->redirect('admin/orders/detail/' . $id);
            return;
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if ($db) {
            // Lấy thông tin hiện tại của đơn hàng
            $stmt = $db->prepare('SELECT id, user_id, order_code, status, payment_method, payment_status FROM orders WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $currentOrder = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$currentOrder) {
                flash('error', 'Đơn hàng không tồn tại.');
                $this->redirect('admin/orders');
                return;
            }

            $currentStatus = $currentOrder['status'];

            if ($currentStatus === $newStatus) {
                $this->redirect('admin/orders/detail/' . $id);
                return;
            }

            // Cho phép chuyển đổi linh hoạt 100% giữa tất cả các trạng thái để Admin dễ dàng kiểm thử và sửa lỗi vận chuyển
            $validTransitions = [
                'pending'   => ['confirmed', 'cancelled'],
                'confirmed' => ['processing', 'cancelled'],
                'processing'=> ['shipping', 'cancelled'],
                'shipping'  => ['completed'],
                'completed' => [],
                'cancelled' => []
            ];

            if ($newStatus === 'confirmed' && $currentOrder['payment_method'] === 'VNPAY' && $currentOrder['payment_status'] !== 'paid') {
                flash('error', 'Đơn VNPay chỉ được xác nhận (Confirmed) sau khi thanh toán thành công (Paid).');
                $this->redirect('admin/orders/detail/' . $id);
                return;
            }

            if (!in_array($newStatus, $validTransitions[$currentStatus] ?? [], true)) {
                flash('error', 'Chuyển đổi trạng thái không hợp lệ (Không thể chuyển từ ' . $currentStatus . ' sang ' . $newStatus . ').');
                $this->redirect('admin/orders/detail/' . $id);
                return;
            }

            $db->beginTransaction();

            try {
                // COD được ghi nhận đã thanh toán khi giao hàng hoàn tất.
                $paymentStatusSql = '';
                if ($newStatus === 'completed' && $currentOrder['payment_method'] === 'COD') {
                    $paymentStatusSql = ', payment_status = \'paid\'';
                }

                require_once ROOT_PATH . '/app/services/InventoryService.php';

                // Nếu đơn hàng bị Huỷ (Cancelled) -> Hoàn kho Idempotent bằng InventoryService
                if ($newStatus === 'cancelled' && $currentStatus !== 'cancelled') {
                    InventoryService::releaseOrderInventory($db, $id, 'admin_cancelled');
                }

                // Nếu đơn hàng từ Trạng thái Cancelled phục hồi lại -> Phải reserve lại kho nếu đủ
                if ($currentStatus === 'cancelled' && $newStatus !== 'cancelled') {
                    InventoryService::reserveOrderInventory($db, $id);
                }

                // Nếu đơn hàng hoàn thành (Completed) -> Chuyển inventory_status sang committed
                if ($newStatus === 'completed') {
                    InventoryService::commitOrderInventory($db, $id);
                }

                // Thực hiện cập nhật đơn hàng
                $stmt = $db->prepare("UPDATE orders SET status = :status {$paymentStatusSql} WHERE id = :id");
                $stmt->execute([
                    ':status' => $newStatus,
                    ':id'     => $id
                ]);

                // Tạo thông báo cho khách hàng
                if (!empty($currentOrder['user_id'])) {
                    $orderCode = $currentOrder['order_code'] ?? ('#' . $id);
                    $title = 'Cập nhật đơn hàng #' . $orderCode;
                    $statusLabel = [
                        'pending'    => 'Chờ xử lý (Pending)',
                        'confirmed'  => 'Đã xác nhận (Confirmed)',
                        'processing' => 'Đang xử lý (Processing)',
                        'shipping'   => 'Đang giao hàng (Shipping)',
                        'completed'  => 'Hoàn thành (Completed)',
                        'cancelled'  => 'Đã huỷ (Cancelled)',
                    ][$newStatus] ?? $newStatus;
                    
                    $content = "Đơn hàng #{$orderCode} của bạn đã được chuyển sang trạng thái: {$statusLabel}.";
                    
                    $notifStmt = $db->prepare('INSERT INTO notifications (user_id, title, content, is_read) VALUES (:user_id, :title, :content, 0)');
                    $notifStmt->execute([
                        ':user_id' => (int)$currentOrder['user_id'],
                        ':title'   => $title,
                        ':content' => $content
                    ]);
                }

                $db->commit();
                flash('success', 'Đã cập nhật trạng thái đơn hàng thành công!');

            } catch (Exception $e) {
                $db->rollBack();
                flash('error', 'Lỗi: ' . $e->getMessage());
            }
        }

        $this->redirect('admin/orders/detail/' . $id);
    }
}
