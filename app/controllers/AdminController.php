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
        header('Cache-Control: no-cache, must-revalidate');
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();
        $items = [];
        $unreadCount = 0;

        if ($db) {
            $stmt = $db->prepare('SELECT id, title, content, is_read, created_at FROM notifications WHERE user_id = :uid OR user_id = 1 ORDER BY id DESC LIMIT 10');
            $stmt->execute([':uid' => $adminUserId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt2 = $db->prepare('SELECT COUNT(*) FROM notifications WHERE (user_id = :uid OR user_id = 1) AND is_read = 0');
            $stmt2->execute([':uid' => $adminUserId]);
            $unreadCount = (int)$stmt2->fetchColumn();
        }

        echo json_encode(['success' => true, 'unread' => $unreadCount, 'items' => $items], JSON_UNESCAPED_UNICODE);
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
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if ($db) {
            $stmt = $db->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = :uid OR user_id = 1');
            $stmt->execute([':uid' => $adminUserId]);
        }

        echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
