<?php
require_once ROOT_PATH . '/app/core/helpers.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/Order.php';
require_once ROOT_PATH . '/app/models/Notification.php';
require_once ROOT_PATH . '/app/models/ReturnRequest.php';
require_once ROOT_PATH . '/app/models/User.php';
require_once ROOT_PATH . '/app/models/Wishlist.php';

class ProfileController extends Controller
{
    private Order $orderModel;
    private Notification $notifModel;
    private ReturnRequest $returnModel;
    private User $userModel;
    private Wishlist $wishlistModel;

    public function __construct()
    {
        $this->orderModel = new Order();
        $this->notifModel = new Notification();
        $this->returnModel = new ReturnRequest();
        $this->userModel = new User();
        $this->wishlistModel = new Wishlist();
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
        ], false);
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
        ], false);
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
        ], false);
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
        ], false);
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

    /** Xem và cập nhật hồ sơ cá nhân: /profile hoặc /profile/index */
    public function index(): void
    {
        $user = $this->requireLogin();
        
        if ($this->isPost()) {
            if (!verifyCsrf($_POST['_csrf'] ?? null)) {
                flash('error', 'Phiên làm việc hết hạn. Thử lại.');
                $this->redirect('profile');
                return;
            }

            $fullName = trim((string)($_POST['full_name'] ?? ''));
            $phone = trim((string)($_POST['phone'] ?? ''));

            if ($fullName === '' || $phone === '') {
                flash('error', 'Vui lòng điền đầy đủ Họ tên và Số điện thoại.');
                $this->redirect('profile');
                return;
            }

            $ok = $this->userModel->updateProfile((int)$user['id'], $fullName, $phone);
            if ($ok) {
                // Cập nhật lại thông tin user trong Session
                $updatedUser = $this->userModel->getById((int)$user['id']);
                if ($updatedUser) {
                    unset($updatedUser['password']);
                    $_SESSION['user'] = $updatedUser;
                }
                flash('success', 'Cập nhật thông tin hồ sơ thành công!');
            } else {
                flash('error', 'Đã xảy ra lỗi khi cập nhật hồ sơ.');
            }
            $this->redirect('profile');
            return;
        }

        // Lấy thông tin user mới nhất từ DB
        $userData = $this->userModel->getById((int)$user['id']);

        $this->render('profile/index', [
            'pageTitle' => 'Hồ sơ cá nhân',
            'user' => $userData,
            'flashes' => pullFlashes()
        ], false);
    }

    /** Đổi mật khẩu tài khoản: /profile/change_password */
    public function change_password(): void
    {
        $user = $this->requireLogin();
        
        if (!$this->isPost()) {
            $this->redirect('profile');
            return;
        }

        if (!verifyCsrf($_POST['_csrf'] ?? null)) {
            flash('error', 'Phiên làm việc hết hạn. Thử lại.');
            $this->redirect('profile');
            return;
        }

        $oldPassword = (string)($_POST['old_password'] ?? '');
        $newPassword = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        if ($oldPassword === '' || $newPassword === '' || $confirmPassword === '') {
            flash('error', 'Vui lòng nhập đầy đủ thông tin mật khẩu.');
            $this->redirect('profile');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            flash('error', 'Mật khẩu mới và mật khẩu xác nhận không khớp.');
            $this->redirect('profile');
            return;
        }

        if (strlen($newPassword) < 6) {
            flash('error', 'Mật khẩu mới phải có tối thiểu 6 ký tự.');
            $this->redirect('profile');
            return;
        }

        // Kiểm tra mật khẩu cũ
        $dbUser = $this->userModel->getById((int)$user['id']);
        if (!$dbUser || !password_verify($oldPassword, $dbUser['password'])) {
            flash('error', 'Mật khẩu cũ không chính xác.');
            $this->redirect('profile');
            return;
        }

        $ok = $this->userModel->updatePassword((int)$user['id'], $newPassword);
        if ($ok) {
            flash('success', 'Đổi mật khẩu thành công!');
        } else {
            flash('error', 'Đã xảy ra lỗi khi đổi mật khẩu.');
        }
        $this->redirect('profile');
    }

    /** Danh sách sản phẩm yêu thích (Wishlist): /profile/wishlist */
    public function wishlist(): void
    {
        $user = $this->requireLogin();
        $items = $this->wishlistModel->getItems((int)$user['id']);

        $this->render('profile/wishlist', [
            'pageTitle' => 'Sản phẩm yêu thích',
            'items' => $items,
            'flashes' => pullFlashes()
        ], false);
    }

    /** Khách hàng tự hủy đơn hàng: POST /profile/cancel_order */
    public function cancel_order(): void
    {
        $user = $this->requireLogin();

        if (!$this->isPost()) {
            $this->redirect('profile/orders');
            return;
        }

        if (!verifyCsrf($_POST['_csrf'] ?? null)) {
            flash('error', 'Phiên làm việc hết hạn. Thử lại.');
            $this->redirect('profile/orders');
            return;
        }

        $orderId = (int)($_POST['order_id'] ?? 0);
        $order = $this->orderModel->getById($orderId, (int)$user['id']);

        if (!$order) {
            flash('error', 'Đơn hàng không tồn tại hoặc không thuộc quyền sở hữu của bạn.');
            $this->redirect('profile/orders');
            return;
        }

        if ($order['status'] !== 'pending') {
            flash('error', 'Chỉ có thể hủy đơn hàng khi trạng thái là Chờ xác nhận.');
            $this->redirect('profile/order_detail?id=' . $orderId);
            return;
        }

        // Thực hiện transaction hủy đơn và hoàn stock
        $db = Database::getConnection();
        if (!$db) {
            flash('error', 'Lỗi kết nối cơ sở dữ liệu.');
            $this->redirect('profile/order_detail?id=' . $orderId);
            return;
        }

        $db->beginTransaction();
        try {
            // Cập nhật trạng thái đơn hàng sang cancelled
            $stmt = $db->prepare("UPDATE orders SET status = 'cancelled' WHERE id = :id AND user_id = :user_id");
            $stmt->execute([':id' => $orderId, ':user_id' => (int)$user['id']]);

            // Lấy các sản phẩm trong đơn để cộng lại stock
            $stmt = $db->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = :order_id');
            $stmt->execute([':order_id' => $orderId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($items as $item) {
                if (!empty($item['product_id'])) {
                    $updateStockStmt = $db->prepare('UPDATE products SET stock = stock + :qty WHERE id = :pid');
                    $updateStockStmt->execute([
                        ':qty' => (int)$item['quantity'],
                        ':pid' => (int)$item['product_id']
                    ]);
                }
            }

            // Ghi nhận thông báo
            $stmt = $db->prepare('INSERT INTO notifications (user_id, title, content) VALUES (:user_id, :title, :content)');
            $stmt->execute([
                ':user_id' => $user['id'],
                ':title' => 'Hủy đơn hàng thành công',
                ':content' => 'Bạn đã hủy đơn hàng #' . $order['order_code'] . ' thành công. Tồn kho sản phẩm đã được hoàn lại.'
            ]);

            $db->commit();
            flash('success', 'Hủy đơn hàng thành công!');
        } catch (Throwable $e) {
            $db->rollBack();
            flash('error', 'Có lỗi xảy ra khi hủy đơn hàng: ' . $e->getMessage());
        }

        $this->redirect('profile/order_detail?id=' . $orderId);
    }
}
