<?php
require_once ROOT_PATH . '/app/core/helpers.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/Order.php';
require_once ROOT_PATH . '/app/models/Notification.php';
require_once ROOT_PATH . '/app/models/ReturnRequest.php';
require_once ROOT_PATH . '/app/models/User.php';
require_once ROOT_PATH . '/app/models/Wishlist.php';
require_once ROOT_PATH . '/app/models/Address.php';

class ProfileController extends Controller
{
    private Order $orderModel;
    private Notification $notifModel;
    private ReturnRequest $returnModel;
    private User $userModel;
    private Wishlist $wishlistModel;
    private Address $addressModel;

    public function __construct()
    {
        $this->orderModel = new Order();
        $this->notifModel = new Notification();
        $this->returnModel = new ReturnRequest();
        $this->userModel = new User();
        $this->wishlistModel = new Wishlist();
        $this->addressModel = new Address();
    }

    public function addresses(): void
    {
        $user = $this->requireLogin();
        $this->render('profile/addresses', [
            'pageTitle' => 'Sổ địa chỉ',
            'addresses' => $this->addressModel->allForUser((int)$user['id']),
            'flashes' => pullFlashes(),
        ], false);
    }

    public function save_address(): void
    {
        $user = $this->requireLogin();
        $fields = ['recipient_name','phone','address_line','ward','district','province'];
        $data = [];
        foreach ($fields as $field) $data[$field] = trim((string)($_POST[$field] ?? ''));
        $data['is_default'] = isset($_POST['is_default']);

        if ($data['recipient_name'] === '' || !preg_match('/^[0-9+ .()-]{8,20}$/', $data['phone']) || $data['address_line'] === '' || $data['province'] === '') {
            flash('error', 'Vui lòng nhập đầy đủ tên người nhận, số điện thoại hợp lệ và địa chỉ.');
        } else {
            $id = (int)($_POST['id'] ?? 0);
            $ok = $id > 0
                ? $this->addressModel->update($id, (int)$user['id'], $data)
                : $this->addressModel->create((int)$user['id'], $data);
            flash($ok ? 'success' : 'error', $ok ? 'Đã lưu địa chỉ.' : 'Không thể lưu địa chỉ.');
        }
        $this->redirect('profile/addresses');
    }

    public function delete_address(): void
    {
        $user = $this->requireLogin();
        $ok = $this->addressModel->delete((int)($_POST['id'] ?? 0), (int)$user['id']);
        flash($ok ? 'success' : 'error', $ok ? 'Đã xóa địa chỉ.' : 'Không tìm thấy địa chỉ.');
        $this->redirect('profile/addresses');
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

    public function repay(): void
    {
        $user = $this->requireLogin();
        if (!$this->isPost()) $this->redirect('profile/orders');
        $orderId = (int)($_POST['order_id'] ?? 0);
        $order = $this->orderModel->getById($orderId, (int)$user['id']);
        if (!$order || ($order['payment_method'] ?? '') !== 'VNPAY' || ($order['status'] ?? '') === 'cancelled' || !in_array($order['payment_status'] ?? '', ['failed', 'pending'], true)) {
            flash('error', 'Đơn hàng này không thể thanh toán lại.');
            $this->redirect('profile/orders');
        }

        require_once ROOT_PATH . '/app/services/VnpayService.php';
        try {
            $this->orderModel->updatePayment((string)$order['order_code'], 'pending');
            $_SESSION['last_order'] = array_merge($order, [
                'payment_status' => 'pending',
                'total' => (float)$order['total_amount'],
            ]);
            header('Location: ' . (new VnpayService())->createPaymentUrl([
                'order_code' => $order['order_code'],
                'total' => $order['total_amount'],
            ]));
            exit;
        } catch (Throwable $e) {
            flash('error', 'Không thể kết nối VNPay. Vui lòng thử lại sau.');
            $this->redirect('profile/order_detail?id=' . $orderId);
        }
    }

    /** Hộp thư thông báo */
    public function notifications(): void
    {
        $user = $this->requireLogin();
        
        // Lấy danh sách thông báo trước (để giữ trạng thái chưa đọc khi hiển thị lần đầu)
        $notifications = $this->notifModel->getByUserId((int)$user['id']);

        // Đánh dấu tất cả đã đọc sau đó trong DB để lần load tiếp theo hoặc icon chuông reset về 0
        $this->notifModel->markAllAsRead((int)$user['id']);

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
            return;
        }

        if ($order['status'] !== 'completed') {
            flash('error', 'Chỉ những đơn hàng đã giao thành công (Completed) mới được phép yêu cầu đổi trả.');
            $this->redirect('profile/orders');
            return;
        }

        if (($order['payment_status'] ?? '') !== 'paid') {
            flash('error', 'Chỉ đơn hàng đã thanh toán thành công mới có thể yêu cầu đổi trả.');
            $this->redirect('profile/order_detail?id=' . $orderId);
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
        $order = $this->orderModel->getById($orderId, (int)$user['id']);
        if (!$order || ($order['payment_status'] ?? '') !== 'paid') {
            flash('error', 'Đơn hàng chưa thanh toán hoặc không hợp lệ.');
            $this->redirect('profile/orders');
        }
        $reason = trim((string)($_POST['reason'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $quantities = $_POST['quantity'] ?? []; // mảng [order_item_id => qty]
        $resolutions = $_POST['resolution'] ?? []; // mảng [order_item_id => type]

        if ($orderId <= 0 || $reason === '') {
            flash('error', 'Lý do đổi trả không được để trống.');
            $this->redirect('profile/orders');
            return;
        }

        $order = $this->orderModel->getById($orderId, (int)$user['id']);
        if (!$order || $order['status'] !== 'completed') {
            flash('error', 'Đơn hàng không hợp lệ hoặc chưa hoàn thành.');
            $this->redirect('profile/orders');
            return;
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

    /** API: Lấy thông báo chưa đọc phục vụ Realtime Polling */
    public function apiUnreadNotifications(): void
    {
        header('Content-Type: application/json');
        $user = currentUser();
        if (!$user) {
            echo json_encode(['success' => false, 'count' => 0, 'notifications' => []]);
            exit;
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();
        $notifications = [];
        $unreadCount = 0;
        
        if ($db) {
            $stmt = $db->prepare("SELECT id, title, content, created_at FROM notifications WHERE user_id = :user_id AND is_read = 0 ORDER BY id DESC");
            $stmt->execute([':user_id' => $user['id']]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $unreadCount = count($notifications);
        }

        echo json_encode([
            'success' => true,
            'count' => $unreadCount,
            'notifications' => $notifications
        ]);
        exit;
    }
}
