<?php

class AdminProductController extends Controller
{
    public function index(): void
    {
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $search = trim($_GET['search'] ?? '');
        $categoryId = (int)($_GET['category_id'] ?? 0);
        $brandId = (int)($_GET['brand_id'] ?? 0);
        $status = trim($_GET['status'] ?? '');
        $lowStock = (int)($_GET['low_stock'] ?? 0);

        $categories = [];
        $brands = [];
        $products = [];

        // Phân trang
        $limit = 10;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $limit;
        $totalProducts = 0;

        if ($db) {
            $categories = $db->query('SELECT id, name FROM categories ORDER BY sort_order ASC, name ASC')->fetchAll(PDO::FETCH_ASSOC);
            $brands = $db->query('SELECT id, name FROM brands ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);

            // Xây dựng câu truy vấn
            $sql = 'SELECT p.*, c.name as category_name, b.name as brand_name,
                    (SELECT COUNT(*) FROM order_items WHERE product_id = p.id) as order_count
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN brands b ON p.brand_id = b.id
                    WHERE 1=1';
            
            $countSql = 'SELECT COUNT(*) FROM products p WHERE 1=1';

            $params = [];

            if ($search !== '') {
                $sql .= ' AND p.name LIKE :search';
                $countSql .= ' AND p.name LIKE :search';
                $params[':search'] = '%' . $search . '%';
            }

            if ($categoryId > 0) {
                $sql .= ' AND p.category_id = :category_id';
                $countSql .= ' AND p.category_id = :category_id';
                $params[':category_id'] = $categoryId;
            }

            if ($brandId > 0) {
                $sql .= ' AND p.brand_id = :brand_id';
                $countSql .= ' AND p.brand_id = :brand_id';
                $params[':brand_id'] = $brandId;
            }

            if ($status !== '') {
                $sql .= ' AND p.status = :status';
                $countSql .= ' AND p.status = :status';
                $params[':status'] = $status;
            }

            if ($lowStock > 0) {
                $sql .= ' AND p.stock < 10';
                $countSql .= ' AND p.stock < 10';
            }

            // Đếm tổng số
            $countStmt = $db->prepare($countSql);
            $countStmt->execute($params);
            $totalProducts = (int)$countStmt->fetchColumn();

            // Lấy danh sách phân trang
            $sql .= ' ORDER BY p.id DESC LIMIT :limit OFFSET :offset';
            $stmt = $db->prepare($sql);
            
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $totalPages = ceil($totalProducts / $limit);

        $this->renderAdmin('admin/products/index', [
            'pageTitle'     => 'Quản lý sản phẩm',
            'activeMenu'    => 'products',
            'products'      => $products,
            'categories'    => $categories,
            'brands'        => $brands,
            'search'        => $search,
            'categoryId'    => $categoryId,
            'brandId'       => $brandId,
            'status'        => $status,
            'lowStock'      => $lowStock,
            'page'          => $page,
            'totalPages'    => $totalPages,
            'totalProducts' => $totalProducts
        ]);
    }

    public function create(): void
    {
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $categories = [];
        $brands = [];
        if ($db) {
            $categories = $db->query('SELECT id, name FROM categories WHERE status = \'active\' ORDER BY sort_order ASC, name ASC')->fetchAll(PDO::FETCH_ASSOC);
            $brands = $db->query('SELECT id, name FROM brands ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->renderAdmin('admin/products/create', [
            'pageTitle'  => 'Thêm sản phẩm mới',
            'activeMenu' => 'products',
            'categories' => $categories,
            'brands'     => $brands
        ]);
    }

    public function store(): void
    {
        if (!$this->isPost()) {
            $this->redirect('admin/products');
        }

        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $brandId = (int)($_POST['brand_id'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);
        $salePrice = trim($_POST['sale_price'] ?? '') !== '' ? (float)$_POST['sale_price'] : null;
        $stock = (int)($_POST['stock'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $specs = trim($_POST['specs'] ?? '');
        $status = trim($_POST['status'] ?? 'active');

        // Validation
        if ($name === '' || $categoryId === 0 || $brandId === 0) {
            flash('error', 'Vui lòng điền đầy đủ tên sản phẩm, danh mục và thương hiệu.');
            $this->redirect('admin/products/create');
            return;
        }

        if ($price < 0 || $stock < 0) {
            flash('error', 'Giá bán và số lượng tồn kho không được âm.');
            $this->redirect('admin/products/create');
            return;
        }

        if ($salePrice !== null && $salePrice > $price) {
            flash('error', 'Giá khuyến mãi không được lớn hơn giá gốc.');
            $this->redirect('admin/products/create');
            return;
        }

        if ($slug === '') {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        }

        // Xử lý upload ảnh chính
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            try {
                require_once ROOT_PATH . '/app/services/UploadService.php';
                $image = UploadService::uploadImage($_FILES['image'], 'products');
            } catch (Exception $e) {
                flash('error', 'Lỗi upload ảnh chính: ' . $e->getMessage());
                $this->redirect('admin/products/create');
                return;
            }
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        // Check slug trùng lặp
        $stmt = $db->prepare('SELECT id FROM products WHERE slug = :slug LIMIT 1');
        $stmt->execute([':slug' => $slug]);
        if ($stmt->fetch()) {
            flash('error', 'Slug sản phẩm này đã được sử dụng.');
            $this->redirect('admin/products/create');
            return;
        }

        $stmt = $db->prepare(
            'INSERT INTO products (category_id, brand_id, name, slug, image, price, sale_price, stock, description, specs, status)
             VALUES (:category_id, :brand_id, :name, :slug, :image, :price, :sale_price, :stock, :description, :specs, :status)'
        );

        $success = $stmt->execute([
            ':category_id' => $categoryId,
            ':brand_id'    => $brandId,
            ':name'        => $name,
            ':slug'        => $slug,
            ':image'       => $image,
            ':price'       => $price,
            ':sale_price'  => $salePrice,
            ':stock'       => $stock,
            ':description' => $description,
            ':specs'       => $specs,
            ':status'      => $status
        ]);

        if ($success) {
            flash('success', 'Đã thêm sản phẩm thành công!');
            $this->redirect('admin/products');
        } else {
            flash('error', 'Không thể lưu sản phẩm vào database.');
            $this->redirect('admin/products/create');
        }
    }

    public function edit(string $id = ''): void
    {
        $id = (int)$id;
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $product = null;
        $categories = [];
        $brands = [];

        if ($db) {
            $stmt = $db->prepare('SELECT * FROM products WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            $categories = $db->query('SELECT id, name FROM categories WHERE status = \'active\' ORDER BY sort_order ASC, name ASC')->fetchAll(PDO::FETCH_ASSOC);
            $brands = $db->query('SELECT id, name FROM brands ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
        }

        if (!$product) {
            flash('error', 'Sản phẩm không tồn tại.');
            $this->redirect('admin/products');
            return;
        }

        $this->renderAdmin('admin/products/edit', [
            'pageTitle'  => 'Sửa sản phẩm',
            'activeMenu' => 'products',
            'product'    => $product,
            'categories' => $categories,
            'brands'     => $brands
        ]);
    }

    public function update(string $id = ''): void
    {
        $id = (int)$id;
        if (!$this->isPost()) {
            $this->redirect('admin/products');
        }

        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $brandId = (int)($_POST['brand_id'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);
        $salePrice = trim($_POST['sale_price'] ?? '') !== '' ? (float)$_POST['sale_price'] : null;
        $stock = (int)($_POST['stock'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $specs = trim($_POST['specs'] ?? '');
        $status = trim($_POST['status'] ?? 'active');

        // Validation
        if ($name === '' || $categoryId === 0 || $brandId === 0) {
            flash('error', 'Vui lòng điền đầy đủ tên, danh mục và thương hiệu.');
            $this->redirect('admin/products/edit/' . $id);
            return;
        }

        if ($price < 0 || $stock < 0) {
            flash('error', 'Giá bán và số lượng tồn kho không được âm.');
            $this->redirect('admin/products/edit/' . $id);
            return;
        }

        if ($salePrice !== null && $salePrice > $price) {
            flash('error', 'Giá khuyến mãi không được lớn hơn giá gốc.');
            $this->redirect('admin/products/edit/' . $id);
            return;
        }

        if ($slug === '') {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        // Check slug trùng lặp trừ chính nó
        $stmt = $db->prepare('SELECT id FROM products WHERE slug = :slug AND id != :id LIMIT 1');
        $stmt->execute([':slug' => $slug, ':id' => $id]);
        if ($stmt->fetch()) {
            flash('error', 'Slug sản phẩm này đã được sử dụng.');
            $this->redirect('admin/products/edit/' . $id);
            return;
        }

        // Xử lý upload ảnh mới
        $image = $_POST['current_image'] ?? '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            try {
                require_once ROOT_PATH . '/app/services/UploadService.php';
                $image = UploadService::uploadImage($_FILES['image'], 'products');
            } catch (Exception $e) {
                flash('error', 'Lỗi upload ảnh: ' . $e->getMessage());
                $this->redirect('admin/products/edit/' . $id);
                return;
            }
        }

        $stmt = $db->prepare(
            'UPDATE products SET category_id = :category_id, brand_id = :brand_id, name = :name, slug = :slug,
                                 image = :image, price = :price, sale_price = :sale_price, stock = :stock,
                                 description = :description, specs = :specs, status = :status
             WHERE id = :id'
        );

        $success = $stmt->execute([
            ':category_id' => $categoryId,
            ':brand_id'    => $brandId,
            ':name'        => $name,
            ':slug'        => $slug,
            ':image'       => $image,
            ':price'       => $price,
            ':sale_price'  => $salePrice,
            ':stock'       => $stock,
            ':description' => $description,
            ':specs'       => $specs,
            ':status'      => $status,
            ':id'          => $id
        ]);

        if ($success) {
            flash('success', 'Đã cập nhật sản phẩm thành công!');
            $this->redirect('admin/products');
        } else {
            flash('error', 'Không thể cập nhật sản phẩm.');
            $this->redirect('admin/products/edit/' . $id);
        }
    }

    public function delete(string $id = ''): void
    {
        $id = (int)$id;
        if (!$this->isPost()) {
            $this->redirect('admin/products');
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if ($db) {
            // Kiểm tra xem sản phẩm đã có đơn hàng nào chưa
            $stmt = $db->prepare('SELECT COUNT(*) FROM order_items WHERE product_id = :id');
            $stmt->execute([':id' => $id]);
            $orderCount = (int)$stmt->fetchColumn();

            if ($orderCount > 0) {
                // Soft Delete: Chuyển sang inactive
                $stmt = $db->prepare('UPDATE products SET status = \'inactive\' WHERE id = :id');
                if ($stmt->execute([':id' => $id])) {
                    flash('success', 'Sản phẩm này đã có lịch sử đơn hàng. Hệ thống đã tự động chuyển trạng thái sản phẩm sang ẩn (Tạm khoá) để bảo toàn dữ liệu.');
                } else {
                    flash('error', 'Không thể khoá sản phẩm.');
                }
            } else {
                // Hard Delete: Xoá hẳn khỏi MySQL
                $stmt = $db->prepare('DELETE FROM products WHERE id = :id');
                if ($stmt->execute([':id' => $id])) {
                    flash('success', 'Đã xoá hoàn toàn sản phẩm khỏi hệ thống thành công!');
                } else {
                    flash('error', 'Không thể xoá sản phẩm.');
                }
            }
        }

        $this->redirect('admin/products');
    }
}
