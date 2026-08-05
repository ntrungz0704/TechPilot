<?php

require_once ROOT_PATH . '/app/services/InventoryService.php';

class InventoryApiController extends Controller
{
    /** Endpoint Admin / API kiểm tra tồn kho sản phẩm: GET /api/inventory/product/{id} */
    public function product(string $id = '0'): void
    {
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');

        $user = currentUser();
        if (!$user || ($user['role'] ?? '') !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền truy cập dữ liệu này.']);
            exit;
        }

        $productId = (int)$id;
        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID sản phẩm không hợp lệ.']);
            exit;
        }

        $stockData = InventoryService::getProductStock($productId);
        echo json_encode([
            'success'     => true,
            'data'        => $stockData,
            'updated_at'  => date('c')
        ]);
        exit;
    }

    /** Endpoint Admin / API thống kê tồn kho toàn hệ thống: GET /api/inventory/summary */
    public function summary(): void
    {
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');

        $user = currentUser();
        if (!$user || ($user['role'] ?? '') !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền truy cập dữ liệu này.']);
            exit;
        }

        $summary = InventoryService::getGlobalSummary();
        echo json_encode([
            'success'    => true,
            'data'       => $summary,
            'updated_at' => date('c')
        ]);
        exit;
    }

    /** Endpoint Admin / API thống kê tồn kho theo danh mục: GET /api/inventory/category/{id} */
    public function category(string $id = '0'): void
    {
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');

        $user = currentUser();
        if (!$user || ($user['role'] ?? '') !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền truy cập dữ liệu này.']);
            exit;
        }

        $catId = (int)$id;
        $summary = InventoryService::getCategorySummary($catId > 0 ? $catId : null);
        echo json_encode([
            'success'    => true,
            'data'       => $summary,
            'updated_at' => date('c')
        ]);
        exit;
    }
}
