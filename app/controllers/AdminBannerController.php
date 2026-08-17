    <?php

class AdminBannerController extends Controller
{
    // =========================================================================
    // ===== Chức năng Admin Quản lý Banner Quảng cáo (UC33) =====
    // =========================================================================
    public function index(): void
    {
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $banners = [];
        if ($db) {
            $stmt = $db->query('SELECT * FROM banners ORDER BY position ASC, id DESC');
            $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->renderAdmin('admin/banners/index', [
            'pageTitle'  => 'Quản lý Banner quảng cáo',
            'activeMenu' => 'banners',
            'banners'    => $banners
        ]);
    }

    public function create(): void
    {
        $this->renderAdmin('admin/banners/create', [
            'pageTitle'  => 'Tạo banner mới',
            'activeMenu' => 'banners'
        ]);
    }

    public function store(): void
    {
        if (!$this->isPost()) {
            $this->redirect('admin/banners');
            return;
        }

        $this->requireAdmin();
        if (!verifyCsrf($_POST['_csrf'] ?? null)) {
            flash('error', 'Phiên làm việc hết hạn, vui lòng thử lại.');
            $this->redirect('admin/banners');
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $link = trim($_POST['link'] ?? '#');
        $type = trim($_POST['type'] ?? 'hero');
        $position = (int)($_POST['position'] ?? 1);
        $status = trim($_POST['status'] ?? 'active');

        if ($title === '') {
            flash('error', 'Vui lòng nhập tiêu đề cho banner.');
            $this->redirect('admin/banners/create');
            return;
        }

        // Xử lý upload ảnh banner
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            try {
                require_once ROOT_PATH . '/app/services/UploadService.php';
                $image = UploadService::uploadImage($_FILES['image'], 'banners');
            } catch (Exception $e) {
                flash('error', 'Lỗi upload ảnh banner: ' . $e->getMessage());
                $this->redirect('admin/banners/create');
                return;
            }
        } else {
            $image = trim($_POST['image_text'] ?? '');
        }

        if ($image === '') {
            flash('error', 'Vui lòng chọn hoặc nhập đường dẫn hình ảnh banner.');
            $this->redirect('admin/banners/create');
            return;
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if ($db) {
            $stmt = $db->prepare('INSERT INTO banners (title, image, link, type, position, status) VALUES (:title, :image, :link, :type, :position, :status)');
            $success = $stmt->execute([
                ':title'    => $title,
                ':image'    => $image,
                ':link'     => $link,
                ':type'     => $type,
                ':position' => $position,
                ':status'   => $status
            ]);

            if ($success) {
                flash('success', 'Đã thêm banner thành công!');
                $this->redirect('admin/banners');
                return;
            } else {
                flash('error', 'Không thể lưu banner.');
                $this->redirect('admin/banners/create');
                return;
            }
        }
    }

    public function edit(string $id = ''): void
    {
        $id = (int)$id;
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $banner = null;
        if ($db) {
            $stmt = $db->prepare('SELECT * FROM banners WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $banner = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$banner) {
            flash('error', 'Banner không tồn tại.');
            $this->redirect('admin/banners');
            return;
        }

        $this->renderAdmin('admin/banners/edit', [
            'pageTitle'  => 'Sửa Banner',
            'activeMenu' => 'banners',
            'banner'     => $banner
        ]);
    }

    public function update(string $id = ''): void
    {
        $id = (int)$id;
        if (!$this->isPost()) {
            $this->redirect('admin/banners');
            return;
        }

        $this->requireAdmin();
        if (!verifyCsrf($_POST['_csrf'] ?? null)) {
            flash('error', 'Phiên làm việc hết hạn, vui lòng thử lại.');
            $this->redirect('admin/banners');
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $link = trim($_POST['link'] ?? '#');
        $type = trim($_POST['type'] ?? 'hero');
        $position = (int)($_POST['position'] ?? 1);
        $status = trim($_POST['status'] ?? 'active');

        if ($title === '') {
            flash('error', 'Vui lòng nhập tiêu đề cho banner.');
            $this->redirect('admin/banners/edit/' . $id);
            return;
        }

        $image = $_POST['current_image'] ?? '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            try {
                require_once ROOT_PATH . '/app/services/UploadService.php';
                $image = UploadService::uploadImage($_FILES['image'], 'banners');
            } catch (Exception $e) {
                flash('error', 'Lỗi upload ảnh banner: ' . $e->getMessage());
                $this->redirect('admin/banners/edit/' . $id);
                return;
            }
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if ($db) {
            $stmt = $db->prepare('UPDATE banners SET title = :title, image = :image, link = :link, type = :type, position = :position, status = :status WHERE id = :id');
            $success = $stmt->execute([
                ':title'    => $title,
                ':image'    => $image,
                ':link'     => $link,
                ':type'     => $type,
                ':position' => $position,
                ':status'   => $status,
                ':id'       => $id
            ]);

            if ($success) {
                flash('success', 'Đã cập nhật banner thành công!');
                $this->redirect('admin/banners');
                return;
            } else {
                flash('error', 'Không thể cập nhật banner.');
                $this->redirect('admin/banners/edit/' . $id);
                return;
            }
        }
    }

    public function delete(string $id = ''): void
    {
        $id = (int)$id;
        if (!$this->isPost()) {
            $this->redirect('admin/banners');
            return;
        }

        $this->requireAdmin();
        if (!verifyCsrf($_POST['_csrf'] ?? null)) {
            flash('error', 'Phiên làm việc hết hạn, vui lòng thử lại.');
            $this->redirect('admin/banners');
            return;
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if ($db) {
            $stmt = $db->prepare("UPDATE banners SET status = 'inactive' WHERE id = :id");
            if ($stmt->execute([':id' => $id])) {
                flash('warning', 'Hệ thống đã khóa tính năng xóa cứng. Đã tự động chuyển trạng thái banner sang Tạm ẩn.');
            } else {
                flash('error', 'Không thể tạm ẩn banner.');
            }
        }

        $this->redirect('admin/banners');
        return;
    }

    /** Toggle trạng thái Bật/Tắt hiển thị banner (POST /admin/banners/toggle-status/{id}) */
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

        $stmt = $db->prepare("SELECT status, title FROM banners WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $bn = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$bn) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Banner không tồn tại.']);
            exit;
        }

        $newStatus = ($bn['status'] === 'active') ? 'inactive' : 'active';
        $upStmt = $db->prepare("UPDATE banners SET status = :status WHERE id = :id");
        $upStmt->execute([':status' => $newStatus, ':id' => $id]);

        echo json_encode([
            'success'      => true,
            'message'      => 'Đã ' . ($newStatus === 'active' ? 'bật hiển thị' : 'tạm ẩn') . ' banner ' . $bn['title'],
            'new_status'   => $newStatus,
            'status_label' => $newStatus === 'active' ? 'Hiển thị' : 'Ẩn'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    // =========================================================================
    // ===== Hoàn thành chức năng Admin Quản lý Banner (UC33) =====
    // =========================================================================
}
