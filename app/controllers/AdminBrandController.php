<?php

class AdminBrandController extends Controller
{
    // =========================================================================
    // ===== Chức năng Admin Quản lý Thương hiệu (UC28) =====
    // =========================================================================
    public function index(): void
    {
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $search = trim($_GET['search'] ?? '');
        $brands = [];

        if ($db) {
            if ($search !== '') {
                $stmt = $db->prepare('SELECT b.*, (SELECT COUNT(*) FROM products WHERE brand_id = b.id) as product_count FROM brands b WHERE b.name LIKE :search ORDER BY b.id DESC');
                $stmt->execute([':search' => '%' . $search . '%']);
            } else {
                $stmt = $db->prepare('SELECT b.*, (SELECT COUNT(*) FROM products WHERE brand_id = b.id) as product_count FROM brands b ORDER BY b.id DESC');
                $stmt->execute();
            }
            $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->renderAdmin('admin/brands/index', [
            'pageTitle'  => 'Quản lý thương hiệu',
            'activeMenu' => 'brands',
            'brands'     => $brands,
            'search'     => $search
        ]);
    }

    public function create(): void
    {
        $this->renderAdmin('admin/brands/create', [
            'pageTitle'  => 'Thêm thương hiệu mới',
            'activeMenu' => 'brands'
        ]);
    }

    public function store(): void
    {
        if (!$this->isPost()) {
            $this->redirect('admin/brands');
            return;
        }

        $this->requireAdmin();
        if (!verifyCsrf($_POST['_csrf'] ?? null)) {
            flash('error', 'Phiên làm việc hết hạn, vui lòng thử lại.');
            $this->redirect('admin/brands');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if ($name === '') {
            flash('error', 'Vui lòng nhập tên thương hiệu.');
            $this->redirect('admin/brands/create');
            return;
        }

        if ($slug === '') {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        }

        // Xử lý upload logo thương hiệu (nếu có)
        $logo = '';
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            try {
                require_once ROOT_PATH . '/app/services/UploadService.php';
                $logo = UploadService::uploadImage($_FILES['logo'], 'brands');
            } catch (Exception $e) {
                flash('error', 'Lỗi upload logo: ' . $e->getMessage());
                $this->redirect('admin/brands/create');
                return;
            }
        } else {
            // Nhập thủ công tên file logo hoặc để trống
            $logo = trim($_POST['logo_text'] ?? '');
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        // Check slug trùng lặp
        $stmt = $db->prepare('SELECT id FROM brands WHERE slug = :slug LIMIT 1');
        $stmt->execute([':slug' => $slug]);
        if ($stmt->fetch()) {
            flash('error', 'Slug hoặc tên thương hiệu này đã được sử dụng.');
            $this->redirect('admin/brands/create');
            return;
        }

        $stmt = $db->prepare('INSERT INTO brands (name, slug, logo, description) VALUES (:name, :slug, :logo, :description)');
        $success = $stmt->execute([
            ':name'        => $name,
            ':slug'        => $slug,
            ':logo'        => $logo,
            ':description' => $description
        ]);

        if ($success) {
            flash('success', 'Đã thêm thương hiệu thành công!');
            $this->redirect('admin/brands');
            return;
        } else {
            flash('error', 'Không thể lưu thương hiệu.');
            $this->redirect('admin/brands/create');
            return;
        }
    }

    public function edit(string $id = ''): void
    {
        $id = (int)$id;
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $brand = null;
        if ($db) {
            $stmt = $db->prepare('SELECT * FROM brands WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $brand = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$brand) {
            flash('error', 'Thương hiệu không tồn tại.');
            $this->redirect('admin/brands');
            return;
        }

        $this->renderAdmin('admin/brands/edit', [
            'pageTitle'  => 'Sửa thương hiệu',
            'activeMenu' => 'brands',
            'brand'      => $brand
        ]);
    }

    public function update(string $id = ''): void
    {
        $id = (int)$id;
        if (!$this->isPost()) {
            $this->redirect('admin/brands');
            return;
        }

        $this->requireAdmin();
        if (!verifyCsrf($_POST['_csrf'] ?? null)) {
            flash('error', 'Phiên làm việc hết hạn, vui lòng thử lại.');
            $this->redirect('admin/brands');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name === '') {
            flash('error', 'Vui lòng nhập tên thương hiệu.');
            $this->redirect('admin/brands/edit/' . $id);
            return;
        }

        if ($slug === '') {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        // Kiểm tra slug trùng lặp trừ chính nó
        $stmt = $db->prepare('SELECT id FROM brands WHERE slug = :slug AND id != :id LIMIT 1');
        $stmt->execute([':slug' => $slug, ':id' => $id]);
        if ($stmt->fetch()) {
            flash('error', 'Slug thương hiệu này đã tồn tại.');
            $this->redirect('admin/brands/edit/' . $id);
            return;
        }

        $logo = $_POST['current_logo'] ?? '';
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            try {
                require_once ROOT_PATH . '/app/services/UploadService.php';
                $logo = UploadService::uploadImage($_FILES['logo'], 'brands');
            } catch (Exception $e) {
                flash('error', 'Lỗi upload logo: ' . $e->getMessage());
                $this->redirect('admin/brands/edit/' . $id);
                return;
            }
        }

        $stmt = $db->prepare('UPDATE brands SET name = :name, slug = :slug, logo = :logo, description = :description WHERE id = :id');
        $success = $stmt->execute([
            ':name'        => $name,
            ':slug'        => $slug,
            ':logo'        => $logo,
            ':description' => $description,
            ':id'          => $id
        ]);

        if ($success) {
            flash('success', 'Đã cập nhật thương hiệu thành công!');
            $this->redirect('admin/brands');
            return;
        } else {
            flash('error', 'Không thể cập nhật thương hiệu.');
            $this->redirect('admin/brands/edit/' . $id);
            return;
        }
    }

    public function delete(string $id = ''): void
    {
        $id = (int)$id;
        if (!$this->isPost()) {
            $this->redirect('admin/brands');
            return;
        }

        $this->requireAdmin();
        if (!verifyCsrf($_POST['_csrf'] ?? null)) {
            flash('error', 'Phiên làm việc hết hạn, vui lòng thử lại.');
            $this->redirect('admin/brands');
            return;
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if ($db) {
            $stmt = $db->prepare("UPDATE brands SET status = 'inactive' WHERE id = :id");
            if ($stmt->execute([':id' => $id])) {
                flash('warning', 'Hệ thống đã khóa tính năng xóa cứng thương hiệu. Đã tự động chuyển trạng thái thương hiệu sang Tạm ẩn.');
            } else {
                flash('error', 'Không thể tạm ẩn thương hiệu.');
            }
        }

        $this->redirect('admin/brands');
        return;
    }

    /** Toggle trạng thái Bật/Tắt hiển thị thương hiệu (POST /admin/brands/toggle-status/{id}) */
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

        $stmt = $db->prepare("SELECT status, name FROM categories WHERE id = :id LIMIT 1");
        $stmt = $db->prepare("SELECT status, name FROM brands WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $brand = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$brand) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Thương hiệu không tồn tại.']);
            exit;
        }

        $newStatus = ($brand['status'] === 'active') ? 'inactive' : 'active';
        $upStmt = $db->prepare("UPDATE brands SET status = :status WHERE id = :id");
        $upStmt->execute([':status' => $newStatus, ':id' => $id]);

        echo json_encode([
            'success'      => true,
            'message'      => 'Đã ' . ($newStatus === 'active' ? 'bật hiển thị' : 'tạm ẩn') . ' thương hiệu ' . $brand['name'],
            'new_status'   => $newStatus,
            'status_label' => $newStatus === 'active' ? 'Đang hoạt động' : 'Tạm ẩn'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    // =========================================================================
    // ===== Hoàn thành chức năng Admin Quản lý Thương hiệu (UC28) =====
    // =========================================================================
}
