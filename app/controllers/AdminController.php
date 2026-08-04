<?php

class AdminController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();

        require_once ROOT_PATH . '/app/services/InventoryService.php';
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $inventorySummary = InventoryService::getGlobalSummary($db);

        $stats = [
            'total_users'           => 0,
            'total_orders'          => 0,
            'total_revenue'         => 0.0,
            'total_product_models'  => $inventorySummary['total_product_models'],
            'active_product_models' => $inventorySummary['active_product_models'],
            'total_inventory_units' => $inventorySummary['total_inventory_units'],
            'low_stock_models'      => $inventorySummary['low_stock_models'],
            'out_of_stock_models'   => $inventorySummary['out_of_stock_models'],
            'total_sold_units'      => $inventorySummary['total_sold_units'],
        ];

        $lowStockProducts = [];
        $recentOrders = [];
        $chartLabels = [];
        $dailyRevenue = [];
        $dailyOrders = [];
        $maxRevenue = 100000.0;
        $statusCounts = [
            'pending' => 0,
            'shipping' => 0,
            'completed' => 0,
            'cancelled' => 0,
        ];

        if ($db) {
            // Tổng số khách hàng
            $stats['total_users'] = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();

            // Tổng số đơn hàng
            $stats['total_orders'] = (int)$db->query("SELECT COUNT(*) FROM orders")->fetchColumn();

            // Doanh thu từ các đơn hàng đã hoàn thành (completed)
            $stats['total_revenue'] = (float)$db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'completed'")->fetchColumn();

            // Sản phẩm tồn kho thấp (1 - 9)
            $stmt = $db->prepare("SELECT id, name, price, stock, image FROM products WHERE status = 'active' AND stock < 10 ORDER BY stock ASC LIMIT 7");
            $stmt->execute();
            $lowStockProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Đơn hàng gần đây
            $stmt = $db->prepare("SELECT id, order_code, customer_name, total_amount, status, created_at FROM orders ORDER BY id DESC LIMIT 5");
            $stmt->execute();
            $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Realtime 7-day revenue & order counts
            for ($i = 6; $i >= 0; $i--) {
                $timestamp = strtotime("-$i days");
                $dateStr = date('Y-m-d', $timestamp);
                $labelStr = date('d/m', $timestamp);
                $chartLabels[] = $labelStr;

                $stmtRev = $db->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'completed' AND DATE(created_at) = :d");
                $stmtRev->execute([':d' => $dateStr]);
                $rev = (float)$stmtRev->fetchColumn();
                $dailyRevenue[] = $rev;
                if ($rev > $maxRevenue) {
                    $maxRevenue = $rev;
                }

                $stmtOrd = $db->prepare("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = :d");
                $stmtOrd->execute([':d' => $dateStr]);
                $cnt = (int)$stmtOrd->fetchColumn();
                $dailyOrders[] = $cnt;
            }

            // Realtime Order status distribution
            $stmtSt = $db->query("SELECT status, COUNT(*) as cnt FROM orders GROUP BY status");
            if ($stmtSt) {
                while ($row = $stmtSt->fetch(PDO::FETCH_ASSOC)) {
                    $st = $row['status'];
                    if ($st === 'pending' || $st === 'confirmed' || $st === 'processing') {
                        $statusCounts['pending'] += (int)$row['cnt'];
                    } elseif ($st === 'shipping') {
                        $statusCounts['shipping'] += (int)$row['cnt'];
                    } elseif ($st === 'completed') {
                        $statusCounts['completed'] += (int)$row['cnt'];
                    } elseif ($st === 'cancelled') {
                        $statusCounts['cancelled'] += (int)$row['cnt'];
                    }
                }
            }
        }

        $totalOrdersCount = array_sum($statusCounts);
        $statusPcts = [
            'pending'   => $totalOrdersCount > 0 ? round(($statusCounts['pending'] / $totalOrdersCount) * 100, 1) : 0,
            'shipping'  => $totalOrdersCount > 0 ? round(($statusCounts['shipping'] / $totalOrdersCount) * 100, 1) : 0,
            'completed' => $totalOrdersCount > 0 ? round(($statusCounts['completed'] / $totalOrdersCount) * 100, 1) : 0,
            'cancelled' => $totalOrdersCount > 0 ? round(($statusCounts['cancelled'] / $totalOrdersCount) * 100, 1) : 0,
        ];

        $this->renderAdmin('admin/dashboard', [
            'pageTitle'        => 'Dashboard Thống Kê',
            'activeMenu'       => 'dashboard',
            'stats'            => $stats,
            'lowStockProducts' => $lowStockProducts,
            'recentOrders'     => $recentOrders,
            'chartLabels'      => $chartLabels,
            'dailyRevenue'     => $dailyRevenue,
            'dailyOrders'      => $dailyOrders,
            'maxRevenue'       => $maxRevenue,
            'statusCounts'     => $statusCounts,
            'statusPcts'       => $statusPcts
        ]);
    }

    public function notifications(): void
    {
        $adminUser = $this->requireApiAdmin();
        $adminUserId = (int)($adminUser['id'] ?? 1);

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if (!$db) {
            http_response_code(503);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'DATABASE_UNAVAILABLE',
                    'message' => 'Hệ thống đang bảo trì, vui lòng thử lại sau.'
                ]
            ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            exit;
        }

        $items = [];
        $unreadCount = 0;

        try {
            $stmt = $db->prepare('SELECT id, title, content, is_read, created_at FROM notifications WHERE user_id = :uid OR user_id = 1 ORDER BY id DESC LIMIT 10');
            $stmt->execute([':uid' => $adminUserId]);
            $rawItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($rawItems as $row) {
                $item = [
                    'id'         => (int)$row['id'],
                    'title'      => (string)$row['title'],
                    'content'    => (string)$row['content'],
                    'is_read'    => (int)$row['is_read'],
                    'created_at' => (string)$row['created_at'],
                    'link'       => null
                ];
                
                if (preg_match('/#TP-([A-Z0-9\-]+)/i', $item['title'] . ' ' . $item['content'], $matches)) {
                    $orderCode = 'TP-' . $matches[1];
                    $stmtOrder = $db->prepare('SELECT id FROM orders WHERE order_code = ? LIMIT 1');
                    $stmtOrder->execute([$orderCode]);
                    $orderId = $stmtOrder->fetchColumn();
                    if ($orderId) {
                        $item['link'] = url('admin/orders/detail/' . $orderId);
                    }
                }
                
                $items[] = $item;
            }

            $stmt2 = $db->prepare('SELECT COUNT(*) FROM notifications WHERE (user_id = :uid OR user_id = 1) AND is_read = 0');
            $stmt2->execute([':uid' => $adminUserId]);
            $unreadCount = (int)$stmt2->fetchColumn();
        } catch (PDOException $e) {
            http_response_code(503);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'DATABASE_ERROR',
                    'message' => 'Lỗi truy xuất cơ sở dữ liệu.'
                ]
            ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            exit;
        }

        echo json_encode([
            'success' => true, 
            'unread' => $unreadCount, 
            'items' => $items
        ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        exit;
    }

    public function markReadNotifications(): void
    {
        $adminUser = $this->requireApiAdmin();
        $adminUserId = (int)($adminUser['id'] ?? 1);

        if (!$this->isPost()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
            exit;
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if (!$db) {
            http_response_code(503);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'DATABASE_UNAVAILABLE',
                    'message' => 'Hệ thống đang bảo trì, vui lòng thử lại sau.'
                ]
            ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            exit;
        }

        try {
            if (isset($_POST['id'])) {
                $id = (int)$_POST['id'];
                if ($id <= 0) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'error' => [
                            'code' => 'INVALID_NOTIFICATION_ID',
                            'message' => 'ID thông báo không hợp lệ.'
                        ]
                    ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
                    exit;
                }
                
                $stmt = $db->prepare('UPDATE notifications SET is_read = 1 WHERE id = :id AND (user_id = :uid OR user_id = 1)');
                $stmt->execute([':id' => $id, ':uid' => $adminUserId]);
            } else {
                $stmt = $db->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = :uid OR user_id = 1');
                $stmt->execute([':uid' => $adminUserId]);
            }
        } catch (PDOException $e) {
            http_response_code(503);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'DATABASE_ERROR',
                    'message' => 'Lỗi cập nhật cơ sở dữ liệu.'
                ]
            ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            exit;
        }

        echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        exit;
    }
}
