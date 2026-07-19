<?php

class AdminBrandController extends Controller
{
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
        } else {
            flash('error', 'Không thể lưu thương hiệu.');
            $this->redirect('admin/brands/create');
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
        } else {
            flash('error', 'Không thể cập nhật thương hiệu.');
            $this->redirect('admin/brands/edit/' . $id);
        }
    }

    public function delete(string $id = ''): void
    {
        $id = (int)$id;
        if (!$this->isPost()) {
            $this->redirect('admin/brands');
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if ($db) {
            // Chặn xóa nếu có sản phẩm
            $stmt = $db->prepare('SELECT COUNT(*) FROM products WHERE brand_id = :id');
            $stmt->execute([':id' => $id]);
            $count = (int)$stmt->fetchColumn();

            if ($count > 0) {
                flash('error', "Không thể xoá thương hiệu này vì đang có {$count} sản phẩm đang được liên kết. Vui lòng chuyển hoặc xoá các sản phẩm đó trước.");
                $this->redirect('admin/brands');
                return;
            }

            $stmt = $db->prepare('DELETE FROM brands WHERE id = :id');
            if ($stmt->execute([':id' => $id])) {
                flash('success', 'Xoá thương hiệu thành công!');
            } else {
                flash('error', 'Không thể xoá thương hiệu.');
            }
        }

        $this->redirect('admin/brands');
    }
}
