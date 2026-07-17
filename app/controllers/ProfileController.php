<?php
require_once ROOT_PATH . '/app/core/helpers.php';
require_once ROOT_PATH . '/app/models/Order.php';
require_once ROOT_PATH . '/app/models/Notification.php';
require_once ROOT_PATH . '/app/models/ReturnRequest.php';

class ProfileController extends Controller
{
    private Order $orderModel;
    private Notification $notifModel;
    private ReturnRequest $returnModel;

    public function __construct()
    {
        $this->orderModel = new Order();
        $this->notifModel = new Notification();
        $this->returnModel = new ReturnRequest();
    }

    private function requireLogin(): array
    {
        $user = currentUser();
        if (!$user) {
            flash('error', 'Vui lòng đăng nhập để tiếp tục.');
            $this->redirect('auth/login');
            exit;
        }
        return $user;
    }

    /** Lịch sử đơn hàng */
    public function orders(): void
    {
        $user = $this->requireLogin();
        $orders = $this->orderModel->getByUserId((int)$user['id']);

        $this->render('profile/orders', [
            'pageTitle' => 'Lịch sử đơn hàng',
            'orders' => $orders,
            'flashes' => pullFlashes()
        ]);
    }

    /** Chi tiết đơn hàng */
    public function order_detail(): void
    {
        $user = $this->requireLogin();
        $orderId = (int)($_GET['id'] ?? 0);
        $order = $this->orderModel->getById($orderId, (int)$user['id']);

        if (!$order) {
            flash('error', 'Đơn hàng không tồn tại.');
            $this->redirect('profile/orders');
        }

        $this->render('profile/order_detail', [
            'pageTitle' => 'Chi tiết đơn hàng',
            'order' => $order,
            'flashes' => pullFlashes()
        ]);
    }

    /** Hộp thư thông báo */
    public function notifications(): void
    {
        $user = $this->requireLogin();
        
        // Đánh dấu tất cả đã đọc trước
        $this->notifModel->markAllAsRead((int)$user['id']);

        $notifications = $this->notifModel->getByUserId((int)$user['id']);

        $this->render('profile/notifications', [
            'pageTitle' => 'Thông báo hệ thống',
            'notifications' => $notifications,
            'flashes' => pullFlashes()
        ]);
    }

    /** Form đăng ký đổi trả sản phẩm */
    public function return(): void
    {
        $user = $this->requireLogin();
        $orderId = (int)($_GET['order_id'] ?? 0);
        $order = $this->orderModel->getById($orderId, (int)$user['id']);

        if (!$order) {
            flash('error', 'Đơn hàng không hợp lệ.');
            $this->redirect('profile/orders');
        }
        
        $this->render('profile/return', [
            'pageTitle' => 'Yêu cầu đổi trả sản phẩm',
            'order' => $order,
            'flashes' => pullFlashes()
        ]);
    }

    /** Nhận yêu cầu đổi trả gửi lên */
    public function submit_return(): void
    {
        $user = $this->requireLogin();
        if (!$this->isPost()) {
            $this->redirect('profile/orders');
        }

        if (!verifyCsrf($_POST['_csrf'] ?? null)) {
            flash('error', 'Phiên làm việc hết hạn. Thử lại.');
            $this->redirect('profile/orders');
        }

        $orderId = (int)($_POST['order_id'] ?? 0);
        $reason = trim((string)($_POST['reason'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $quantities = $_POST['quantity'] ?? []; // mảng [order_item_id => qty]
        $resolutions = $_POST['resolution'] ?? []; // mảng [order_item_id => type]

        if ($orderId <= 0 || $reason === '') {
            flash('error', 'Lý do đổi trả không được để trống.');
            $this->redirect('profile/orders');
        }

        $itemsToReturn = [];
        foreach ($quantities as $orderItemId => $qty) {
            $qty = (int)$qty;
            if ($qty > 0) {
                $itemsToReturn[] = [
                    'order_item_id' => (int)$orderItemId,
                    'quantity' => $qty,
                    'resolution' => $resolutions[$orderItemId] ?? 'refund'
                ];
            }
        }

        if (empty($itemsToReturn)) {
            flash('error', 'Vui lòng chọn ít nhất một sản phẩm và số lượng để đổi trả.');
            $this->redirect('profile/return?order_id=' . $orderId);
        }

        $ok = $this->returnModel->create((int)$user['id'], $orderId, $reason, $description, $itemsToReturn);
        if ($ok) {
            flash('success', 'Gửi yêu cầu đổi trả thành công! Chúng tôi sẽ xem xét sớm nhất.');
            
            // Tự động tạo một thông báo hệ thống cho user
            $db = Database::getConnection();
            if ($db) {
                $stmt = $db->prepare('INSERT INTO notifications (user_id, title, content) VALUES (:user_id, :title, :content)');
                $stmt->execute([
                    ':user_id' => $user['id'],
                    ':title' => 'Yêu cầu đổi trả đã được tiếp nhận',
                    ':content' => 'Yêu cầu đổi trả cho đơn hàng #' . $orderId . ' đã được ghi nhận vào hệ thống.'
                ]);
            }

            $this->redirect('profile/orders');
        } else {
            flash('error', 'Đã xảy ra lỗi khi tạo yêu cầu đổi trả.');
            $this->redirect('profile/return?order_id=' . $orderId);
        }
    }
}
