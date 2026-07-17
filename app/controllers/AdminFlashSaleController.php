<?php

class AdminFlashSaleController extends Controller
{
    public function index(): void
    {
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $flashSales = [];
        if ($db) {
            $stmt = $db->query('SELECT fs.*, (SELECT COUNT(*) FROM flash_sale_items WHERE flash_sale_id = fs.id) as item_count FROM flash_sales fs ORDER BY fs.id DESC');
            $flashSales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->renderAdmin('admin/flash_sales/index', [
            'pageTitle'  => 'Quản lý Flash Sale',
            'activeMenu' => 'flash-sales',
            'flashSales' => $flashSales
        ]);
    }

    public function create(): void
    {
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $products = [];
        if ($db) {
            $products = $db->query('SELECT id, name, price FROM products WHERE status = \'active\' ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->renderAdmin('admin/flash_sales/create', [
            'pageTitle'  => 'Tạo chương trình Flash Sale',
            'activeMenu' => 'flash-sales',
            'products'   => $products
        ]);
    }

    public function store(): void
    {
        if (!$this->isPost()) {
            $this->redirect('admin/flash-sales');
        }

        $title = trim($_POST['title'] ?? '');
        $startTime = trim($_POST['start_time'] ?? '');
        $endTime = trim($_POST['end_time'] ?? '');
        $status = trim($_POST['status'] ?? 'active');
        $itemProducts = $_POST['items'] ?? []; // array of product_id

        if ($title === '' || $startTime === '' || $endTime === '') {
            flash('error', 'Vui lòng nhập đầy đủ tiêu đề, thời gian bắt đầu và kết thúc.');
            $this->redirect('admin/flash-sales/create');
            return;
        }

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-')) . '-' . time();

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if ($db) {
            $db->beginTransaction();
            try {
                // 1. Tạo đợt flash sale
                $stmt = $db->prepare('INSERT INTO flash_sales (title, slug, start_time, end_time, status) VALUES (:title, :slug, :start_time, :end_time, :status)');
                $stmt->execute([
                    ':title'      => $title,
                    ':slug'       => $slug,
                    ':start_time' => $startTime,
                    ':end_time'   => $endTime,
                    ':status'     => $status
                ]);

                $flashSaleId = (int)$db->lastInsertId();

                // 2. Thêm các sản phẩm tham gia
                if (!empty($itemProducts)) {
                    foreach ($itemProducts as $prodId => $data) {
                        if (!isset($data['active'])) continue;

                        $discountPrice = (float)($data['discount_price'] ?? 0);
                        $allocationQty = (int)($data['allocation_quantity'] ?? 10);
                        $limitUser = (int)($data['limit_per_user'] ?? 2);

                        $itemStmt = $db->prepare(
                            'INSERT INTO flash_sale_items (flash_sale_id, product_id, discount_price, allocation_quantity, sold_quantity, limit_per_user)
                             VALUES (:flash_sale_id, :product_id, :discount_price, :allocation_quantity, 0, :limit_per_user)'
                        );
                        $itemStmt->execute([
                            ':flash_sale_id'       => $flashSaleId,
                            ':product_id'          => (int)$prodId,
                            ':discount_price'      => $discountPrice,
                            ':allocation_quantity' => $allocationQty,
                            ':limit_per_user'      => $limitUser
                        ]);
                    }
                }

                $db->commit();
                flash('success', 'Tạo chương trình Flash Sale thành công!');
                $this->redirect('admin/flash-sales');

            } catch (Exception $e) {
                $db->rollBack();
                flash('error', 'Lỗi: ' . $e->getMessage());
                $this->redirect('admin/flash-sales/create');
            }
        }
    }

    public function edit(string $id = ''): void
    {
        $id = (int)$id;
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $flashSale = null;
        $products = [];
        $selectedItems = [];

        if ($db) {
            $stmt = $db->prepare('SELECT * FROM flash_sales WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $flashSale = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($flashSale) {
                $products = $db->query('SELECT id, name, price FROM products WHERE status = \'active\' ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
                
                $stmt = $db->prepare('SELECT * FROM flash_sale_items WHERE flash_sale_id = :fsid');
                $stmt->execute([':fsid' => $id]);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($items as $item) {
                    $selectedItems[(int)$item['product_id']] = $item;
                }
            }
        }

        if (!$flashSale) {
            flash('error', 'Chương trình Flash Sale không tồn tại.');
            $this->redirect('admin/flash-sales');
            return;
        }

        $this->renderAdmin('admin/flash_sales/edit', [
            'pageTitle'     => 'Sửa chương trình Flash Sale',
            'activeMenu'    => 'flash-sales',
            'flashSale'     => $flashSale,
            'products'      => $products,
            'selectedItems' => $selectedItems
        ]);
    }

    public function update(string $id = ''): void
    {
        $id = (int)$id;
        if (!$this->isPost()) {
            $this->redirect('admin/flash-sales');
        }

        $title = trim($_POST['title'] ?? '');
        $startTime = trim($_POST['start_time'] ?? '');
        $endTime = trim($_POST['end_time'] ?? '');
        $status = trim($_POST['status'] ?? 'active');
        $itemProducts = $_POST['items'] ?? [];

        if ($title === '' || $startTime === '' || $endTime === '') {
            flash('error', 'Vui lòng nhập đầy đủ tiêu đề, thời gian bắt đầu và kết thúc.');
            $this->redirect('admin/flash-sales/edit/' . $id);
            return;
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if ($db) {
            $db->beginTransaction();
            try {
                // 1. Cập nhật thông tin chung
                $stmt = $db->prepare('UPDATE flash_sales SET title = :title, start_time = :start_time, end_time = :end_time, status = :status WHERE id = :id');
                $stmt->execute([
                    ':title'      => $title,
                    ':start_time' => $startTime,
                    ':end_time'   => $endTime,
                    ':status'     => $status,
                    ':id'          => $id
                ]);

                // 2. Xoá toàn bộ sản phẩm tham gia cũ
                $stmt = $db->prepare('DELETE FROM flash_sale_items WHERE flash_sale_id = :id');
                $stmt->execute([':id' => $id]);

                // 3. Thêm mới danh sách sản phẩm tham gia được chọn
                if (!empty($itemProducts)) {
                    foreach ($itemProducts as $prodId => $data) {
                        if (!isset($data['active'])) continue;

                        $discountPrice = (float)($data['discount_price'] ?? 0);
                        $allocationQty = (int)($data['allocation_quantity'] ?? 10);
                        $soldQty = (int)($data['sold_quantity'] ?? 0);
                        $limitUser = (int)($data['limit_per_user'] ?? 2);

                        $itemStmt = $db->prepare(
                            'INSERT INTO flash_sale_items (flash_sale_id, product_id, discount_price, allocation_quantity, sold_quantity, limit_per_user)
                             VALUES (:flash_sale_id, :product_id, :discount_price, :allocation_quantity, :sold_quantity, :limit_per_user)'
                        );
                        $itemStmt->execute([
                            ':flash_sale_id'       => $id,
                            ':product_id'          => (int)$prodId,
                            ':discount_price'      => $discountPrice,
                            ':allocation_quantity' => $allocationQty,
                            ':sold_quantity'       => $soldQty,
                            ':limit_per_user'      => $limitUser
                        ]);
                    }
                }

                $db->commit();
                flash('success', 'Cập nhật Flash Sale thành công!');
                $this->redirect('admin/flash-sales');

            } catch (Exception $e) {
                $db->rollBack();
                flash('error', 'Lỗi: ' . $e->getMessage());
                $this->redirect('admin/flash-sales/edit/' . $id);
            }
        }
    }

    public function delete(string $id = ''): void
    {
        $id = (int)$id;
        if (!$this->isPost()) {
            $this->redirect('admin/flash-sales');
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if ($db) {
            $stmt = $db->prepare('DELETE FROM flash_sales WHERE id = :id');
            if ($stmt->execute([':id' => $id])) {
                flash('success', 'Xoá chương trình Flash Sale thành công!');
            } else {
                flash('error', 'Không thể xoá chương trình Flash Sale.');
            }
        }

        $this->redirect('admin/flash-sales');
    }
}
