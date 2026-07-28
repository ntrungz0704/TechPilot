<?php

require_once ROOT_PATH . '/app/services/InventoryService.php';

class AdminInventoryController extends Controller
{
    /** Lịch sử Nhập / Xuất kho: GET /admin/inventory/logs */
    public function logs(): void
    {
        $this->requireAdmin();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 30;
        $offset = ($page - 1) * $limit;
        $productId = (int)($_GET['product_id'] ?? 0);

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $logs = [];
        $totalLogs = 0;

        if ($db) {
            $logs = InventoryService::getInventoryLogs($productId > 0 ? $productId : null, $limit, $offset, $db);

            $countSql = "SELECT COUNT(*) FROM inventory_logs";
            if ($productId > 0) {
                $countSql .= " WHERE product_id = " . $productId;
            }
            $totalLogs = (int)$db->query($countSql)->fetchColumn();
        }

        $totalPages = max(1, (int)ceil($totalLogs / $limit));

        $this->renderAdmin('admin/inventory/logs', [
            'pageTitle'  => 'Lịch sử Nhập / Xuất kho',
            'activeMenu' => 'products',
            'logs'       => $logs,
            'page'       => $page,
            'totalPages' => $totalPages,
            'productId'  => $productId
        ]);
    }
}
