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
            'limit'         => $limit,
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
        $this->requireAdmin();
        if (!$this->isPost()) {
            $this->redirect('admin/products');
        }

        if (!verifyCsrf($_POST['_csrf'] ?? null)) {
            flash('error', 'Phiên làm việc đã hết hạn hoặc token CSRF không hợp lệ.');
            $this->redirect('admin/products/create');
            return;
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
        $this->requireAdmin();
        $id = (int)$id;
        if (!$this->isPost()) {
            $this->redirect('admin/products');
        }

        if (!verifyCsrf($_POST['_csrf'] ?? null)) {
            flash('error', 'Phiên làm việc đã hết hạn hoặc token CSRF không hợp lệ.');
            $this->redirect('admin/products/edit/' . $id);
            return;
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

    /** Soft Disable / Hide product logic - NO physical DELETE */
    public function delete(string $id = ''): void
    {
        $this->requireAdmin();
        $id = (int)$id;
        if (!$this->isPost()) {
            $this->redirect('admin/products');
        }

        if (!verifyCsrf($_POST['_csrf'] ?? null)) {
            flash('error', 'Phiên làm việc đã hết hạn hoặc token CSRF không hợp lệ.');
            $this->redirect('admin/products');
            return;
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if ($db) {
            // Strictly soft-disable (hide) product - NEVER physical DELETE
            $stmt = $db->prepare("UPDATE products SET status = 'inactive' WHERE id = :id");
            if ($stmt->execute([':id' => $id])) {
                flash('success', 'Đã ẩn sản phẩm (chuyển sang trạng thái Tạm ẩn/Ngừng kinh doanh) thành công!');
            } else {
                flash('error', 'Không thể ẩn sản phẩm.');
            }
        }

        $this->redirect('admin/products');
    }

    /** Toggle trạng thái Hiển thị / Tạm ẩn cho sản phẩm qua AJAX (POST /admin/products/toggle-status/{id}) */
    public function toggleStatus(string $id = ''): void
    {
        $adminUser = $this->requireApiAdmin();
        $id = (int)$id;

        if (!$this->isPost()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
            exit;
        }

        if (!verifyCsrf($_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf'] ?? null)) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'CSRF Token invalid']);
            exit;
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if (!$db) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Database error']);
            exit;
        }

        $stmt = $db->prepare('SELECT status FROM products WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $currentStatus = $stmt->fetchColumn();

        if ($currentStatus === false) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Sản phẩm không tồn tại']);
            exit;
        }

        $newStatus = ($currentStatus === 'active') ? 'inactive' : 'active';
        $updateStmt = $db->prepare('UPDATE products SET status = :status WHERE id = :id');
        $ok = $updateStmt->execute([':status' => $newStatus, ':id' => $id]);

        header('Content-Type: application/json; charset=utf-8');
        if ($ok) {
            echo json_encode([
                'success'     => true,
                'status'      => $newStatus,
                'status_text' => ($newStatus === 'active' ? 'Hiển thị' : 'Ẩn/Khoá'),
                'message'     => ($newStatus === 'active' ? 'Đã hiển thị sản phẩm!' : 'Đã ẩn sản phẩm!')
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Không thể cập nhật trạng thái']);
        }
        exit;
    }

    /** Xử lý Nhập kho / Xuất kho nhanh từ Admin: POST /admin/products/adjust-stock */
    public function adjustStock(): void
    {
        $this->requireAdmin();

        if (!$this->isPost()) {
            $this->redirect('admin/products');
            return;
        }

        if (!verifyCsrf($_POST['_csrf'] ?? null)) {
            flash('error', 'Phiên làm việc không hợp lệ. Vui lòng thử lại.');
            $this->redirect('admin/products');
            return;
        }

        $productId = (int)($_POST['product_id'] ?? 0);
        $action = trim($_POST['action_type'] ?? '');
        $qty = (int)($_POST['quantity'] ?? 0);
        $reasonCode = trim($_POST['reason_code'] ?? 'other');
        $note = trim($_POST['note'] ?? '');
        $idempotencyKey = trim($_POST['idempotency_key'] ?? '');

        if ($productId <= 0 || $qty <= 0) {
            flash('error', 'Số lượng nhập/xuất kho phải là số nguyên dương lớn hơn 0.');
            $this->redirect('admin/products');
            return;
        }

        if (!in_array($action, ['import', 'export'], true)) {
            flash('error', 'Thao tác kho chỉ chấp nhận Nhập kho (import) hoặc Xuất kho (export).');
            $this->redirect('admin/products');
            return;
        }

        if ($action === 'export' && empty($note)) {
            flash('error', 'Ghi chú là bắt buộc khi thực hiện Xuất kho hoặc điều chỉnh giảm.');
            $this->redirect('admin/products');
            return;
        }

        $quantityChange = ($action === 'export') ? -$qty : $qty;
        $type = ($action === 'export') ? 'manual_export' : 'manual_import';

        require_once ROOT_PATH . '/config/database.php';
        require_once ROOT_PATH . '/app/services/InventoryService.php';

        $db = Database::getConnection();
        if (!$db) {
            flash('error', 'Lỗi kết nối cơ sở dữ liệu.');
            $this->redirect('admin/products');
            return;
        }

        $user = currentUser();
        $userId = $user ? (int)$user['id'] : null;

        if (empty($idempotencyKey)) {
            $idempotencyKey = "adj_prod_{$productId}_usr_{$userId}_" . time();
        }

        $db->beginTransaction();
        try {
            $result = InventoryService::adjustStock(
                $db,
                $productId,
                $quantityChange,
                $type,
                $reasonCode,
                $note,
                $userId,
                $idempotencyKey
            );
            $db->commit();

            $actionText = ($action === 'export') ? 'Xuất kho' : 'Nhập kho';
            flash('success', "Đã {$actionText} thành công {$qty} đơn vị sản phẩm '{$result['name']}'. Tồn kho mới: {$result['new_stock']} đơn vị.");
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            flash('error', 'Lỗi điều chỉnh tồn kho: ' . $e->getMessage());
        }

        $this->redirect('admin/products');
    }

    /** API Hỗ trợ sinh dữ liệu sản phẩm bằng AI: POST /admin/products/ai-assistant */
    public function aiAssistant(): void
    {
        $adminUser = $this->requireApiAdmin();

        if (!$this->isPost()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
            exit;
        }

        if (!verifyCsrf($_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf'] ?? null)) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'CSRF Token invalid']);
            exit;
        }

        $inputQuery = trim($_POST['product_name'] ?? $_POST['query'] ?? '');
        if ($inputQuery === '') {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Vui lòng nhập tên sản phẩm hoặc model']);
            exit;
        }

        require_once ROOT_PATH . '/config/database.php';
        require_once ROOT_PATH . '/app/services/AiProductAssistantService.php';

        $db = Database::getConnection();
        $categories = [];
        $brands = [];
        if ($db) {
            $categories = $db->query('SELECT id, name FROM categories ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
            $brands = $db->query('SELECT id, name FROM brands ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
        }

        try {
            $data = AiProductAssistantService::generateProductData($inputQuery, $categories, $brands);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => true,
                'data'    => $data
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}
