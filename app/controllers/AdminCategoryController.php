<?php

class AdminCategoryController extends Controller
{
    /** List categories */
    public function index(): void
    {
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $search = trim($_GET['search'] ?? '');
        $categories = [];

        if ($db) {
            $sql = "
                SELECT 
                    c.*,
                    COUNT(DISTINCT p.id) AS product_models,
                    COALESCE(SUM(CASE WHEN p.status = 'active' THEN p.stock ELSE 0 END), 0) AS inventory_units
                FROM categories c
                LEFT JOIN categories child ON child.parent_id = c.id
                LEFT JOIN products p ON (p.category_id = c.id OR p.category_id = child.id)
            ";

            if ($search !== '') {
                $sql .= " WHERE c.name LIKE :search GROUP BY c.id ORDER BY c.sort_order ASC, c.id DESC";
                $stmt = $db->prepare($sql);
                $stmt->execute([':search' => '%' . $search . '%']);
            } else {
                $sql .= " GROUP BY c.id ORDER BY c.sort_order ASC, c.id DESC";
                $stmt = $db->prepare($sql);
                $stmt->execute();
            }
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->renderAdmin('admin/categories/index', [
            'pageTitle'  => 'Quản lý danh mục',
            'activeMenu' => 'categories',
            'categories' => $categories,
            'search'     => $search
        ]);
    }

    public function create(): void
    {
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();
        $categories = [];
        if ($db) {
            $stmt = $db->query('SELECT id, name FROM categories WHERE parent_id IS NULL ORDER BY name ASC');
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->renderAdmin('admin/categories/create', [
            'pageTitle'  => 'Thêm danh mục mới',
            'activeMenu' => 'categories',
            'categories' => $categories
        ]);
    }

    /** Store new category */
    public function store(): void
    {
        if (!$this->isPost()) {
            $this->redirect('admin/categories');
        }

        $this->requireAdmin();
        if (!verifyCsrf($_POST['_csrf'] ?? null)) {
            flash('error', 'Phiên làm việc hết hạn, vui lòng thử lại.');
            $this->redirect('admin/categories');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $status = trim($_POST['status'] ?? 'active');
        $parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $icon = trim($_POST['icon'] ?? '');
        $image = trim($_POST['image'] ?? '');

        if ($name === '') {
            flash('error', 'Vui lòng nhập tên danh mục.');
            $this->redirect('admin/categories/create');
            return;
        }

        if ($slug === '') {
            // Generate slug automatically
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        // Kiểm tra slug trùng lặp
        $stmt = $db->prepare('SELECT id FROM categories WHERE slug = :slug LIMIT 1');
        $stmt->execute([':slug' => $slug]);
        if ($stmt->fetch()) {
            flash('error', 'Slug hoặc tên này đã được sử dụng.');
            $this->redirect('admin/categories/create');
            return;
        }

        $stmt = $db->prepare('INSERT INTO categories (name, slug, description, sort_order, status, parent_id, icon, image) VALUES (:name, :slug, :description, :sort_order, :status, :parent_id, :icon, :image)');
        $success = $stmt->execute([
            ':name'        => $name,
            ':slug'        => $slug,
            ':description' => $description,
            ':sort_order'  => $sortOrder,
            ':status'      => $status,
            ':parent_id'   => $parentId,
            ':icon'        => $icon,
            ':image'       => $image
        ]);

        if ($success) {
            flash('success', 'Đã thêm danh mục thành công!');
            $this->redirect('admin/categories');
        } else {
            flash('error', 'Không thể lưu danh mục vào database.');
            $this->redirect('admin/categories/create');
        }
    }

    /** Edit form */
    public function edit(string $id = ''): void
    {
        $id = (int)$id;
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $category = null;
        $categories = [];
        if ($db) {
            $stmt = $db->prepare('SELECT * FROM categories WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $category = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt2 = $db->prepare('SELECT id, name FROM categories WHERE parent_id IS NULL AND id != :exclude_id ORDER BY name ASC');
            $stmt2->execute([':exclude_id' => $id]);
            $categories = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        }

        if (!$category) {
            flash('error', 'Danh mục không tồn tại.');
            $this->redirect('admin/categories');
            return;
        }

        $this->renderAdmin('admin/categories/edit', [
            'pageTitle'  => 'Sửa danh mục',
            'activeMenu' => 'categories',
            'category'   => $category,
            'categories' => $categories
        ]);
    }

    /** Update category */
    public function update(string $id = ''): void
    {
        $id = (int)$id;
        if (!$this->isPost()) {
            $this->redirect('admin/categories');
        }

        $this->requireAdmin();
        if (!verifyCsrf($_POST['_csrf'] ?? null)) {
            flash('error', 'Phiên làm việc hết hạn, vui lòng thử lại.');
            $this->redirect('admin/categories');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $status = trim($_POST['status'] ?? 'active');
        $parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $icon = trim($_POST['icon'] ?? '');
        $image = trim($_POST['image'] ?? '');

        if ($name === '') {
            flash('error', 'Vui lòng nhập tên danh mục.');
            $this->redirect('admin/categories/edit/' . $id);
            return;
        }

        if ($slug === '') {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        // Kiểm tra slug trùng lặp trừ chính nó
        $stmt = $db->prepare('SELECT id FROM categories WHERE slug = :slug AND id != :id LIMIT 1');
        $stmt->execute([':slug' => $slug, ':id' => $id]);
        if ($stmt->fetch()) {
            flash('error', 'Slug danh mục này đã tồn tại.');
            $this->redirect('admin/categories/edit/' . $id);
            return;
        }

        $stmt = $db->prepare('UPDATE categories SET name = :name, slug = :slug, description = :description, sort_order = :sort_order, status = :status, parent_id = :parent_id, icon = :icon, image = :image WHERE id = :id');
        $success = $stmt->execute([
            ':name'        => $name,
            ':slug'        => $slug,
            ':description' => $description,
            ':sort_order'  => $sortOrder,
            ':status'      => $status,
            ':parent_id'   => $parentId,
            ':icon'        => $icon,
            ':image'       => $image,
            ':id'          => $id
        ]);

        if ($success) {
            flash('success', 'Đã cập nhật danh mục thành công!');
            $this->redirect('admin/categories');
        } else {
            flash('error', 'Không thể cập nhật danh mục.');
            $this->redirect('admin/categories/edit/' . $id);
        }
    }

    /** Delete category */
    public function delete(string $id = ''): void
    {
        $id = (int)$id;
        if (!$this->isPost()) {
            $this->redirect('admin/categories');
        }

        $this->requireAdmin();
        if (!verifyCsrf($_POST['_csrf'] ?? null)) {
            flash('error', 'Phiên làm việc hết hạn, vui lòng thử lại.');
            $this->redirect('admin/categories');
            return;
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if ($db) {
            // Khóa xóa cứng danh mục để bảo toàn dữ liệu
            $stmt = $db->prepare("UPDATE categories SET status = 'inactive' WHERE id = :id");
            $stmt->execute([':id' => $id]);
            flash('warning', 'Hệ thống đã khóa tính năng xóa cứng danh mục. Đã tự động chuyển trạng thái danh mục sang Tạm ẩn (Inactive). Các sản phẩm thuộc danh mục này cũng sẽ tự động ẩn khỏi cửa hàng.');
        }

        $this->redirect('admin/categories');
    }

    /** Toggle trạng thái Bật/Tắt hiển thị danh mục (POST /admin/categories/toggle-status/{id}) */
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
        $stmt->execute([':id' => $id]);
        $cat = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cat) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Danh mục không tồn tại.']);
            exit;
        }

        $newStatus = ($cat['status'] === 'active') ? 'inactive' : 'active';
        $upStmt = $db->prepare("UPDATE categories SET status = :status WHERE id = :id");
        $upStmt->execute([':status' => $newStatus, ':id' => $id]);

        echo json_encode([
            'success'      => true,
            'message'      => 'Đã ' . ($newStatus === 'active' ? 'bật hiển thị' : 'tạm ẩn') . ' danh mục ' . $cat['name'],
            'new_status'   => $newStatus,
            'status_label' => $newStatus === 'active' ? 'Đang hoạt động' : 'Tạm ẩn'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
