    <?php

class AdminBannerController extends Controller
{
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
            } else {
                flash('error', 'Không thể lưu banner.');
                $this->redirect('admin/banners/create');
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
            } else {
                flash('error', 'Không thể cập nhật banner.');
                $this->redirect('admin/banners/edit/' . $id);
            }
        }
    }

    public function delete(string $id = ''): void
    {
        $id = (int)$id;
        if (!$this->isPost()) {
            $this->redirect('admin/banners');
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if ($db) {
            $stmt = $db->prepare('DELETE FROM banners WHERE id = :id');
            if ($stmt->execute([':id' => $id])) {
                flash('success', 'Xoá banner thành công!');
            } else {
                flash('error', 'Không thể xoá banner.');
            }
        }

        $this->redirect('admin/banners');
    }
}
