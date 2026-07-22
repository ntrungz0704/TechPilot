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
            if ($search !== '') {
                $stmt = $db->prepare('SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count FROM categories c WHERE c.name LIKE :search ORDER BY c.sort_order ASC, c.id DESC');
                $stmt->execute([':search' => '%' . $search . '%']);
            } else {
                $stmt = $db->prepare('SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count FROM categories c ORDER BY c.sort_order ASC, c.id DESC');
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

            $stmt2 = $db->query('SELECT id, name FROM categories WHERE parent_id IS NULL AND id != ' . $id . ' ORDER BY name ASC');
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

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if ($db) {
            // Kiểm tra xem danh mục có sản phẩm nào không (Chặn xóa cứng)
            $stmt = $db->prepare('SELECT COUNT(*) FROM products WHERE category_id = :id');
            $stmt->execute([':id' => $id]);
            $count = (int)$stmt->fetchColumn();

            if ($count > 0) {
                flash('error', "Không thể xoá danh mục này vì đang có {$count} sản phẩm thuộc danh mục này. Vui lòng chuyển các sản phẩm sang danh mục khác trước.");
                $this->redirect('admin/categories');
                return;
            }

            $stmt = $db->prepare('DELETE FROM categories WHERE id = :id');
            if ($stmt->execute([':id' => $id])) {
                flash('success', 'Xoá danh mục thành công!');
            } else {
                flash('error', 'Không thể xoá danh mục.');
            }
        }

        $this->redirect('admin/categories');
    }
}
