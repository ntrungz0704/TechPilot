<?php

class AdminFlashSaleController extends Controller
{
    private const FLASH_SALE_STATUSES = ['draft', 'active', 'ended', 'cancelled'];

    private function parseCampaignDateTime(string $value): DateTimeImmutable
    {
        $value = trim($value);
        foreach (['!Y-m-d\TH:i', '!Y-m-d\TH:i:s', '!Y-m-d H:i:s'] as $format) {
            $parsed = DateTimeImmutable::createFromFormat($format, $value);
            $errors = DateTimeImmutable::getLastErrors();
            $isValid = $errors === false
                || ((int)($errors['warning_count'] ?? 0) === 0 && (int)($errors['error_count'] ?? 0) === 0);
            if ($parsed instanceof DateTimeImmutable && $isValid) {
                return $parsed;
            }
        }

        throw new RuntimeException('Thời gian Flash Sale không hợp lệ.');
    }

    private function normalizeCampaignSchedule(string $startTime, string $endTime, string $status): array
    {
        if (!in_array($status, self::FLASH_SALE_STATUSES, true)) {
            throw new RuntimeException('Trạng thái Flash Sale không hợp lệ.');
        }

        $start = $this->parseCampaignDateTime($startTime);
        $end = $this->parseCampaignDateTime($endTime);
        if ($start >= $end) {
            throw new RuntimeException('Thời gian kết thúc phải sau thời gian bắt đầu.');
        }

        return [
            'start_time' => $start->format('Y-m-d H:i:s'),
            'end_time' => $end->format('Y-m-d H:i:s'),
            'status' => $status,
        ];
    }

    private function assertValidItemQuantities(int $allocationQuantity, int $soldQuantity, int $limitPerUser): void
    {
        if ($allocationQuantity <= 0) {
            throw new RuntimeException('Số lượng mở bán phải lớn hơn 0.');
        }
        if ($soldQuantity < 0 || $soldQuantity > $allocationQuantity) {
            throw new RuntimeException('Số lượng đã bán phải nằm trong hạn mức mở bán.');
        }
        if ($limitPerUser <= 0 || $limitPerUser > $allocationQuantity) {
            throw new RuntimeException('Giới hạn mỗi khách phải từ 1 đến số lượng mở bán.');
        }
    }

    private function assertValidDiscountPrice(PDO $db, int $productId, float $discountPrice): void
    {
        $stmt = $db->prepare(
            "SELECT name, price, sale_price
             FROM products
             WHERE id = :id AND status = 'active'
             LIMIT 1 FOR UPDATE"
        );
        $stmt->execute([':id' => $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new RuntimeException("Sản phẩm ID {$productId} không tồn tại hoặc đã ngừng bán.");
        }

        $basePrice = (float)$product['price'];
        $salePrice = (float)($product['sale_price'] ?? 0);
        $regularPrice = ($salePrice > 0 && $salePrice < $basePrice) ? $salePrice : $basePrice;
        if ($discountPrice <= 0 || $discountPrice >= $regularPrice) {
            throw new RuntimeException(
                'Giá Flash Sale của ' . $product['name']
                . ' phải lớn hơn 0 và thấp hơn giá bán hiện tại (' . formatPrice($regularPrice) . ').'
            );
        }
    }

    public function index(): void
    {
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $flashSales = [];
        $quotaAuditAvailable = true;
        if ($db) {
            $stmt = $db->query('SELECT fs.*, (SELECT COUNT(*) FROM flash_sale_items WHERE flash_sale_id = fs.id) as item_count FROM flash_sales fs ORDER BY fs.id DESC');
            $flashSales = $stmt->fetchAll(PDO::FETCH_ASSOC);

            try {
                require_once ROOT_PATH . '/app/services/FlashSaleService.php';
                $quotaByCampaign = [];
                foreach (FlashSaleService::auditQuotaCounters($db) as $row) {
                    $campaignId = (int)$row['flash_sale_id'];
                    $quotaByCampaign[$campaignId] ??= [
                        'allocation_quantity' => 0,
                        'sold_quantity' => 0,
                        'ledger_quantity' => 0,
                        'drift_count' => 0,
                    ];
                    $quotaByCampaign[$campaignId]['allocation_quantity'] += (int)$row['allocation_quantity'];
                    $quotaByCampaign[$campaignId]['sold_quantity'] += (int)$row['sold_quantity'];
                    $quotaByCampaign[$campaignId]['ledger_quantity'] += (int)$row['ledger_quantity'];
                    if (!$row['is_consistent']) {
                        $quotaByCampaign[$campaignId]['drift_count']++;
                    }
                }

                foreach ($flashSales as &$flashSale) {
                    $flashSale['quota_audit'] = $quotaByCampaign[(int)$flashSale['id']] ?? [
                        'allocation_quantity' => 0,
                        'sold_quantity' => 0,
                        'ledger_quantity' => 0,
                        'drift_count' => 0,
                    ];
                }
                unset($flashSale);
            } catch (Throwable $e) {
                $quotaAuditAvailable = false;
                error_log('Flash Sale quota audit unavailable: ' . $e->getMessage());
            }
        }

        $this->renderAdmin('admin/flash_sales/index', [
            'pageTitle'  => 'Quản lý Flash Sale',
            'activeMenu' => 'flash-sales',
            'flashSales' => $flashSales,
            'quotaAuditAvailable' => $quotaAuditAvailable,
        ]);
    }

    public function create(): void
    {
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $products = [];
        if ($db) {
            $products = $db->query(
                "SELECT id, name, price, sale_price,
                        CASE WHEN sale_price > 0 AND sale_price < price THEN sale_price ELSE price END AS regular_price
                 FROM products
                 WHERE status = 'active'
                 ORDER BY name ASC"
            )->fetchAll(PDO::FETCH_ASSOC);
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

        $this->requireAdmin();
        if (!verifyCsrf($_POST['_csrf'] ?? null)) {
            flash('error', 'Phiên làm việc hết hạn, vui lòng thử lại.');
            $this->redirect('admin/flash-sales');
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $startTime = trim($_POST['start_time'] ?? '');
        $endTime = trim($_POST['end_time'] ?? '');
        $status = trim($_POST['status'] ?? 'active');
        $itemProducts = is_array($_POST['items'] ?? null) ? $_POST['items'] : []; // array of product_id
        ksort($itemProducts, SORT_NUMERIC);

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
                $schedule = $this->normalizeCampaignSchedule($startTime, $endTime, $status);

                // 1. Tạo đợt flash sale
                $stmt = $db->prepare('INSERT INTO flash_sales (title, slug, start_time, end_time, status) VALUES (:title, :slug, :start_time, :end_time, :status)');
                $stmt->execute([
                    ':title'      => $title,
                    ':slug'       => $slug,
                    ':start_time' => $schedule['start_time'],
                    ':end_time'   => $schedule['end_time'],
                    ':status'     => $schedule['status']
                ]);

                $flashSaleId = (int)$db->lastInsertId();

                // 2. Thêm các sản phẩm tham gia
                if (!empty($itemProducts)) {
                    foreach ($itemProducts as $prodId => $data) {
                        if (!isset($data['active'])) continue;

                        // Lấy giá và lọc bỏ dấu phẩy/chấm/chữ (chỉ giữ lại số)
                        $rawDiscountPrice = $data['discount_price'] ?? '';
                        $discountPrice = (float)preg_replace('/[^0-9]/', '', $rawDiscountPrice);
                        
                        $allocationQty = (int)($data['allocation_quantity'] ?? 10);
                        $limitUser = (int)($data['limit_per_user'] ?? 2);
                        $this->assertValidDiscountPrice($db, (int)$prodId, $discountPrice);
                        $this->assertValidItemQuantities($allocationQty, 0, $limitUser);

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

            } catch (Throwable $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
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
                $products = $db->query(
                    "SELECT id, name, price, sale_price,
                            CASE WHEN sale_price > 0 AND sale_price < price THEN sale_price ELSE price END AS regular_price
                     FROM products
                     WHERE status = 'active'
                     ORDER BY name ASC"
                )->fetchAll(PDO::FETCH_ASSOC);
                
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

        $this->requireAdmin();
        if (!verifyCsrf($_POST['_csrf'] ?? null)) {
            flash('error', 'Phiên làm việc hết hạn, vui lòng thử lại.');
            $this->redirect('admin/flash-sales');
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $startTime = trim($_POST['start_time'] ?? '');
        $endTime = trim($_POST['end_time'] ?? '');
        $status = trim($_POST['status'] ?? 'active');
        $itemProducts = is_array($_POST['items'] ?? null) ? $_POST['items'] : [];
        ksort($itemProducts, SORT_NUMERIC);

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
                $schedule = $this->normalizeCampaignSchedule($startTime, $endTime, $status);

                require_once ROOT_PATH . '/app/services/FlashSaleService.php';

                $campaignStmt = $db->prepare('SELECT id FROM flash_sales WHERE id = :id LIMIT 1 FOR UPDATE');
                $campaignStmt->execute([':id' => $id]);
                if (!$campaignStmt->fetchColumn()) {
                    throw new RuntimeException('Chương trình Flash Sale không tồn tại.');
                }

                // sold_quantity là dữ liệu hệ thống, không nhận lại từ hidden input của trình duyệt.
                $existingItemStmt = $db->prepare(
                    'SELECT id, product_id, sold_quantity
                     FROM flash_sale_items
                     WHERE flash_sale_id = :id
                     ORDER BY id ASC FOR UPDATE'
                );
                $existingItemStmt->execute([':id' => $id]);
                $existingItemsByProduct = [];
                foreach ($existingItemStmt->fetchAll(PDO::FETCH_ASSOC) as $existingItem) {
                    $existingItemsByProduct[(int)$existingItem['product_id']] = $existingItem;
                }

                // 1. Cập nhật thông tin chung
                $stmt = $db->prepare('UPDATE flash_sales SET title = :title, start_time = :start_time, end_time = :end_time, status = :status WHERE id = :id');
                $stmt->execute([
                    ':title'      => $title,
                    ':start_time' => $schedule['start_time'],
                    ':end_time'   => $schedule['end_time'],
                    ':status'     => $schedule['status'],
                    ':id'          => $id
                ]);

                // 2. Cập nhật tại chỗ để giữ nguyên item ID và lịch sử reservation.
                $selectedProductIds = [];
                if (!empty($itemProducts)) {
                    foreach ($itemProducts as $prodId => $data) {
                        if (!isset($data['active'])) continue;

                        $productId = (int)$prodId;
                        // Lấy giá và lọc bỏ dấu phẩy/chấm/chữ (chỉ giữ lại số)
                        $rawDiscountPrice = $data['discount_price'] ?? '';
                        $discountPrice = (float)preg_replace('/[^0-9]/', '', $rawDiscountPrice);
                        
                        $allocationQty = (int)($data['allocation_quantity'] ?? 10);
                        $existingItem = $existingItemsByProduct[$productId] ?? null;
                        $soldQty = $existingItem !== null ? (int)$existingItem['sold_quantity'] : 0;
                        $limitUser = (int)($data['limit_per_user'] ?? 2);
                        $this->assertValidDiscountPrice($db, $productId, $discountPrice);
                        $this->assertValidItemQuantities($allocationQty, $soldQty, $limitUser);

                        if ($existingItem !== null) {
                            $itemStmt = $db->prepare(
                                'UPDATE flash_sale_items
                                 SET discount_price = :discount_price,
                                     allocation_quantity = :allocation_quantity,
                                     limit_per_user = :limit_per_user
                                 WHERE id = :item_id AND flash_sale_id = :flash_sale_id'
                            );
                            $itemStmt->execute([
                                ':discount_price' => $discountPrice,
                                ':allocation_quantity' => $allocationQty,
                                ':limit_per_user' => $limitUser,
                                ':item_id' => (int)$existingItem['id'],
                                ':flash_sale_id' => $id,
                            ]);
                        } else {
                            $itemStmt = $db->prepare(
                                'INSERT INTO flash_sale_items
                                    (flash_sale_id, product_id, discount_price, allocation_quantity, sold_quantity, limit_per_user)
                                 VALUES
                                    (:flash_sale_id, :product_id, :discount_price, :allocation_quantity, 0, :limit_per_user)'
                            );
                            $itemStmt->execute([
                                ':flash_sale_id' => $id,
                                ':product_id' => $productId,
                                ':discount_price' => $discountPrice,
                                ':allocation_quantity' => $allocationQty,
                                ':limit_per_user' => $limitUser,
                            ]);
                        }

                        $selectedProductIds[$productId] = true;
                    }
                }

                // 3. Chỉ xóa item bị bỏ chọn khi chưa từng giữ/bán quota.
                $deleteItemStmt = $db->prepare(
                    'DELETE FROM flash_sale_items WHERE id = :item_id AND flash_sale_id = :flash_sale_id'
                );
                foreach ($existingItemsByProduct as $productId => $existingItem) {
                    if (isset($selectedProductIds[$productId])) {
                        continue;
                    }

                    FlashSaleService::assertItemRemovable($db, (int)$existingItem['id']);
                    $deleteItemStmt->execute([
                        ':item_id' => (int)$existingItem['id'],
                        ':flash_sale_id' => $id,
                    ]);
                }

                $db->commit();
                flash('success', 'Cập nhật Flash Sale thành công!');
                $this->redirect('admin/flash-sales');

            } catch (Throwable $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
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

        $this->requireAdmin();
        if (!verifyCsrf($_POST['_csrf'] ?? null)) {
            flash('error', 'Phiên làm việc hết hạn, vui lòng thử lại.');
            $this->redirect('admin/flash-sales');
            return;
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if ($db) {
            $db->beginTransaction();
            try {
                require_once ROOT_PATH . '/app/services/FlashSaleService.php';

                $campaignStmt = $db->prepare('SELECT id FROM flash_sales WHERE id = :id LIMIT 1 FOR UPDATE');
                $campaignStmt->execute([':id' => $id]);
                if (!$campaignStmt->fetchColumn()) {
                    throw new RuntimeException('Chương trình Flash Sale không tồn tại.');
                }

                $itemStmt = $db->prepare(
                    'SELECT id FROM flash_sale_items WHERE flash_sale_id = :id ORDER BY id ASC FOR UPDATE'
                );
                $itemStmt->execute([':id' => $id]);
                foreach ($itemStmt->fetchAll(PDO::FETCH_COLUMN) as $itemId) {
                    FlashSaleService::assertItemRemovable($db, (int)$itemId);
                }

                $stmt = $db->prepare('DELETE FROM flash_sales WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $db->commit();
                flash('success', 'Xoá chương trình Flash Sale thành công!');
            } catch (Throwable $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                flash('error', 'Không thể xoá Flash Sale: ' . $e->getMessage());
            }
        }

        $this->redirect('admin/flash-sales');
    }
}
