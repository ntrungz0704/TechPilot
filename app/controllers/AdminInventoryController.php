<?php

require_once ROOT_PATH . '/app/services/InventoryService.php';

class AdminInventoryController extends Controller
{
    // =========================================================================
    // ===== Chức năng Admin Quản lý Tồn kho & Ghi Log Nhập / Xuất (UC27) =====
    // =========================================================================
    /** Lịch sử Nhập / Xuất kho: GET /admin/inventory/logs */
    public function logs(): void
    {
        $this->requireAdmin();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 30;

        $filters = [
            'product_id' => (int)($_GET['product_id'] ?? 0),
            'type'       => trim($_GET['type'] ?? ''),
            'created_by' => (int)($_GET['created_by'] ?? 0),
            'date_from'  => trim($_GET['date_from'] ?? ''),
            'date_to'    => trim($_GET['date_to'] ?? ''),
            'search'     => trim($_GET['search'] ?? ''),
        ];

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $logs = [];
        $totalLogs = 0;

        if ($db) {
            $totalLogs = InventoryService::countInventoryLogs($filters, $db);
            $totalPages = max(1, (int)ceil($totalLogs / $limit));

            if ($page > $totalPages && $totalLogs > 0) {
                $page = $totalPages;
            }
            $offset = ($page - 1) * $limit;

            $logs = InventoryService::getInventoryLogs($filters, $limit, $offset, $db);
        } else {
            $totalPages = 1;
        }

        $this->renderAdmin('admin/inventory/logs', [
            'pageTitle'  => 'Lịch sử Nhập / Xuất kho',
            'activeMenu' => 'products',
            'logs'       => $logs,
            'page'       => $page,
            'limit'      => $limit,
            'totalLogs'  => $totalLogs,
            'totalPages' => $totalPages,
            'filters'    => $filters,
        ]);
    }
    // =========================================================================
    // ===== Hoàn thành chức năng Admin Quản lý Tồn kho & Ghi Log (UC27) =====
    // =========================================================================
}
