<?php

class AdminController extends Controller
{
    public function index(): void
    {
        // 1. Thống kê số lượng chung
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();
        
        $stats = [
            'total_users'    => 0,
            'total_products' => 0,
            'total_orders'   => 0,
            'total_revenue'  => 0.0
        ];

        $lowStockProducts = [];
        $recentOrders = [];

        if ($db) {
            // Tổng số khách hàng
            $stats['total_users'] = (int)$db->query('SELECT COUNT(*) FROM users WHERE role_id = 2')->fetchColumn();
            
            // Tổng số sản phẩm
            $stats['total_products'] = (int)$db->query('SELECT COUNT(*) FROM products')->fetchColumn();
            
            // Tổng số đơn hàng
            $stats['total_orders'] = (int)$db->query('SELECT COUNT(*) FROM orders')->fetchColumn();
            
            // Doanh thu từ các đơn hàng đã hoàn thành (completed)
            $stats['total_revenue'] = (float)$db->query('SELECT SUM(total_amount) FROM orders WHERE status = \'completed\'')->fetchColumn();

            // Sản phẩm tồn kho thấp (< 10)
            $stmt = $db->prepare('SELECT id, name, price, stock FROM products WHERE stock < 10 ORDER BY stock ASC LIMIT 5');
            $stmt->execute();
            $lowStockProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Đơn hàng gần đây
            $stmt = $db->prepare('SELECT id, order_code, customer_name, total_amount, status, created_at FROM orders ORDER BY id DESC LIMIT 5');
            $stmt->execute();
            $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->renderAdmin('admin/dashboard', [
            'pageTitle'        => 'Dashboard Thống Kê',
            'activeMenu'       => 'dashboard',
            'stats'            => $stats,
            'lowStockProducts' => $lowStockProducts,
            'recentOrders'     => $recentOrders
        ]);
    }
}
