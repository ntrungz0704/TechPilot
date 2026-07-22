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
            $stmt = $db->prepare('SELECT status, user_id, order_code FROM orders WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $orderData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$orderData) {
                flash('error', 'Đơn hàng không tồn tại.');
                $this->redirect('admin/orders');
                return;
            }

            $currentStatus = $orderData['status'];

            if ($currentStatus === $newStatus) {
                $this->redirect('admin/orders/detail/' . $id);
                return;
            }

            // Cho phép chuyển đổi linh hoạt 100% giữa tất cả các trạng thái để Admin dễ dàng kiểm thử và sửa lỗi vận chuyển
            $allStatuses = ['pending', 'confirmed', 'processing', 'shipping', 'completed', 'cancelled'];
            $validTransitions = [
                'pending'    => $allStatuses,
                'confirmed'  => $allStatuses,
                'processing' => $allStatuses,
                'shipping'   => $allStatuses,
                'completed'  => $allStatuses,
                'cancelled'  => $allStatuses
            ];

            if (!in_array($newStatus, $validTransitions[$currentStatus] ?? [])) {
                flash('error', 'Chuyển đổi trạng thái không hợp lệ (Không thể chuyển từ ' . $currentStatus . ' sang ' . $newStatus . ').');
                $this->redirect('admin/orders/detail/' . $id);
                return;
            }

            $db->beginTransaction();

            try {
                // Nếu đơn hàng chuyển sang Completed -> Tự động đánh dấu đã thanh toán (Paid)
                $paymentStatusSql = '';
                if ($newStatus === 'completed') {
                    $paymentStatusSql = ', payment_status = \'paid\'';
                }

                // Nếu đơn hàng bị Huỷ (Cancelled) -> Cộng lại số lượng tồn kho sản phẩm
                if ($newStatus === 'cancelled' && $currentStatus !== 'cancelled') {
                    // Lấy các sản phẩm trong đơn
                    $stmt = $db->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = :order_id');
                    $stmt->execute([':order_id' => $id]);
                    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($items as $item) {
                        $updateStockStmt = $db->prepare('UPDATE products SET stock = stock + :qty WHERE id = :pid');
                        $updateStockStmt->execute([
                            ':qty' => (int)$item['quantity'],
                            ':pid' => (int)$item['product_id']
                        ]);
                    }
                }

                // Nếu đơn hàng từ Trạng thái Cancelled phục hồi lại -> Phải trừ lại kho nếu đủ
                if ($currentStatus === 'cancelled' && $newStatus !== 'cancelled') {
                    $stmt = $db->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = :order_id');
                    $stmt->execute([':order_id' => $id]);
                    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($items as $item) {
                        // Kiểm tra kho có đủ không
                        $stockStmt = $db->prepare('SELECT stock FROM products WHERE id = :pid FOR UPDATE');
                        $stockStmt->execute([':pid' => (int)$item['product_id']]);
                        $currentStock = (int)$stockStmt->fetchColumn();

                        if ($currentStock < (int)$item['quantity']) {
                            throw new Exception("Sản phẩm ID {$item['product_id']} không đủ số lượng tồn kho để khôi phục đơn hàng.");
                        }

                        $updateStockStmt = $db->prepare('UPDATE products SET stock = stock - :qty WHERE id = :pid');
                        $updateStockStmt->execute([
                            ':qty' => (int)$item['quantity'],
                            ':pid' => (int)$item['product_id']
                        ]);
                    }
                }

                // Thực hiện cập nhật đơn hàng
                $stmt = $db->prepare("UPDATE orders SET status = :status {$paymentStatusSql} WHERE id = :id");
                $stmt->execute([
                    ':status' => $newStatus,
                    ':id'     => $id
                ]);

                // Tạo thông báo cho khách hàng
                if (!empty($orderData['user_id'])) {
                    $title = 'Cập nhật đơn hàng #' . $orderData['order_code'];
                    $statusLabel = [
                        'pending'    => 'Chờ xử lý (Pending)',
                        'confirmed'  => 'Đã xác nhận (Confirmed)',
                        'processing' => 'Đang xử lý (Processing)',
                        'shipping'   => 'Đang giao hàng (Shipping)',
                        'completed'  => 'Hoàn thành (Completed)',
                        'cancelled'  => 'Đã huỷ (Cancelled)',
                    ][$newStatus] ?? $newStatus;
                    
                    $content = "Đơn hàng #{$orderData['order_code']} của bạn đã được chuyển sang trạng thái: {$statusLabel}.";
                    
                    $notifStmt = $db->prepare('INSERT INTO notifications (user_id, title, content, is_read) VALUES (:user_id, :title, :content, 0)');
                    $notifStmt->execute([
                        ':user_id' => (int)$orderData['user_id'],
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
