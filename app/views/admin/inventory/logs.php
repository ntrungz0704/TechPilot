<?php
$filters = $filters ?? [];
$productId = (int)($filters['product_id'] ?? 0);
$typeFilter = $filters['type'] ?? '';
$search = $filters['search'] ?? '';
$dateFrom = $filters['date_from'] ?? '';
$dateTo = $filters['date_to'] ?? '';

$buildUrl = function (array $overrides = []) use ($filters): string {
    $q = array_merge([
        'product_id' => $filters['product_id'] ?? 0,
        'type'       => $filters['type'] ?? '',
        'search'     => $filters['search'] ?? '',
        'date_from'  => $filters['date_from'] ?? '',
        'date_to'    => $filters['date_to'] ?? '',
        'page'       => 1,
    ], $overrides);

    $q = array_filter($q, fn($v) => $v !== '' && $v !== 0 && $v !== '0');
    $query = http_build_query($q);
    return url('admin/inventory/logs' . ($query !== '' ? '?' . $query : ''));
};
?>

<div class="card">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h3 class="card-title" style="margin-bottom: 4px;">Lịch sử Nhập / Xuất kho & Biến động tồn kho (Audit Trail)</h3>
            <p style="font-size: 13px; color: var(--text-secondary); margin: 0;">Ghi vết nguyên tử 100% tất cả giao dịch nhập kho, xuất kho, giữ hàng đơn và hoàn kho</p>
        </div>
        <a href="<?= url('admin/products') ?>" class="btn btn--outline"><i class="fa-solid fa-arrow-left"></i> Quay lại Quản lý sản phẩm</a>
    </div>

    <!-- Bộ lọc nâng cao -->
    <form method="get" action="<?= url('admin/inventory/logs') ?>" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px; margin-bottom: 24px;">
        <?php if ($productId > 0): ?>
            <input type="hidden" name="product_id" value="<?= $productId ?>">
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; align-items: end;">
            <div>
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Từ khóa / Mã tham chiếu</label>
                <input type="text" name="search" class="form-control" placeholder="Tên sản phẩm, Mã đơn..." value="<?= e($search) ?>" style="font-size: 13px;">
            </div>

            <div>
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Loại giao dịch</label>
                <select name="type" class="form-control" style="font-size: 13px;">
                    <option value="">-- Tất cả loại --</option>
                    <option value="manual_import" <?= $typeFilter === 'manual_import' ? 'selected' : '' ?>>Nhập kho thủ công (+)</option>
                    <option value="manual_export" <?= $typeFilter === 'manual_export' ? 'selected' : '' ?>>Xuất kho thủ công (-)</option>
                    <option value="order_reserve" <?= $typeFilter === 'order_reserve' ? 'selected' : '' ?>>Khóa giữ hàng đơn (-)</option>
                    <option value="order_release" <?= $typeFilter === 'order_release' ? 'selected' : '' ?>>Hoàn tồn kho đơn (+)</option>
                    <option value="stock_correction_increase" <?= $typeFilter === 'stock_correction_increase' ? 'selected' : '' ?>>Điều chỉnh kiểm kê (+)</option>
                    <option value="stock_correction_decrease" <?= $typeFilter === 'stock_correction_decrease' ? 'selected' : '' ?>>Điều chỉnh kiểm kê (-)</option>
                    <option value="supplier_return" <?= $typeFilter === 'supplier_return' ? 'selected' : '' ?>>Trả hàng nhà cung cấp (-)</option>
                    <option value="return_restock" <?= $typeFilter === 'return_restock' ? 'selected' : '' ?>>Nhập lại từ đơn hoàn trả (+)</option>
                </select>
            </div>

            <div>
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Từ ngày</label>
                <input type="date" name="date_from" class="form-control" value="<?= e($dateFrom) ?>" style="font-size: 13px;">
            </div>

            <div>
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Đến ngày</label>
                <input type="date" name="date_to" class="form-control" value="<?= e($dateTo) ?>" style="font-size: 13px;">
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn" style="padding: 8px 16px; font-size: 13px; font-weight: 700;"><i class="fa-solid fa-filter"></i> Lọc</button>
                <a href="<?= url('admin/inventory/logs') ?>" class="btn btn--outline" style="padding: 8px 14px; font-size: 13px;">Đặt lại</a>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 70px;">ID</th>
                    <th style="width: 50px;">Ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th>Loại giao dịch</th>
                    <th>Thay đổi</th>
                    <th>Tồn trước</th>
                    <th>Tồn sau</th>
                    <th>Lý do / Tham chiếu</th>
                    <th>Người thực hiện</th>
                    <th>Thời gian</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($logs)): ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td>#<?= (int)$log['id'] ?></td>
                            <td>
                                <img src="<?= e(productImageUrl($log['product_image'] ?? '', $log['product_name'] ?? '')) ?>" alt="<?= e($log['product_name'] ?? '') ?>" style="width: 36px; height: 36px; object-fit: contain; border: 1px solid var(--border); border-radius: 4px; padding: 2px;">
                            </td>
                            <td>
                                <strong style="font-size: 13px; max-width: 240px; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= e($log['product_name'] ?? '') ?>">
                                    <?= e($log['product_name'] ?? 'Sản phẩm #' . $log['product_id']) ?>
                                </strong>
                            </td>
                            <td>
                                <?php
                                $t = $log['type'];
                                if ($t === 'manual_import' || $t === 'import'): ?>
                                    <span class="badge" style="background-color: #DCFCE7; color: #15803D; font-weight: 700;"><i class="fa-solid fa-square-plus"></i> Nhập kho</span>
                                <?php elseif ($t === 'manual_export' || $t === 'export'): ?>
                                    <span class="badge" style="background-color: #FEE2E2; color: #B91C1C; font-weight: 700;"><i class="fa-solid fa-square-minus"></i> Xuất kho</span>
                                <?php elseif ($t === 'order_reserve'): ?>
                                    <span class="badge" style="background-color: #FEF3C7; color: #B45309; font-weight: 700;"><i class="fa-solid fa-cart-shopping"></i> Khóa giữ đơn</span>
                                <?php elseif ($t === 'order_release'): ?>
                                    <span class="badge" style="background-color: #E0F2FE; color: #0369A1; font-weight: 700;"><i class="fa-solid fa-rotate-left"></i> Hoàn kho đơn</span>
                                <?php elseif ($t === 'stock_correction_increase'): ?>
                                    <span class="badge" style="background-color: #F0FDF4; color: #166534; font-weight: 700;"><i class="fa-solid fa-sliders"></i> Kiểm kê (+)</span>
                                <?php elseif ($t === 'stock_correction_decrease'): ?>
                                    <span class="badge" style="background-color: #FFF1F2; color: #9F1239; font-weight: 700;"><i class="fa-solid fa-sliders"></i> Kiểm kê (-)</span>
                                <?php else: ?>
                                    <span class="badge" style="background-color: #F3E8FF; color: #6B21A8; font-weight: 700;"><i class="fa-solid fa-clock-rotate-left"></i> <?= e($t) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php $delta = (int)($log['quantity_delta'] ?? $log['quantity'] ?? 0); ?>
                                <strong style="color: <?= $delta > 0 ? '#16A34A' : '#DC2626' ?>; font-size: 14px;">
                                    <?= $delta > 0 ? '+' . number_format($delta) : number_format($delta) ?>
                                </strong>
                            </td>
                            <td><?= number_format((int)$log['old_stock']) ?></td>
                            <td><strong style="color: var(--text-primary);"><?= number_format((int)$log['new_stock']) ?></strong></td>
                            <td>
                                <div style="font-size: 12.5px; color: var(--text-primary); font-weight: 600;">
                                    <?= e($log['note'] ?? '—') ?>
                                </div>
                                <?php if (!empty($log['order_id']) || !empty($log['reference_id'])): ?>
                                    <small style="color: #2563EB; font-weight: 700;">
                                        Mã Đơn: #<?= e($log['order_id'] ?? $log['reference_id']) ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="font-size: 12.5px; font-weight: 600;"><?= e($log['user_name'] ?? 'Tự động (Hệ thống)') ?></span>
                            </td>
                            <td>
                                <small style="color: var(--text-secondary); white-space: nowrap;"><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></small>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" style="text-align: center; color: var(--text-secondary); padding: 35px;">Chưa có lịch sử biến động kho nào phù hợp với bộ lọc.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div style="display: flex; justify-content: center; align-items: center; gap: 6px; margin-top: 25px; flex-wrap: wrap;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="<?= $buildUrl(['page' => $i]) ?>" class="btn <?= $page === $i ? '' : 'btn--outline' ?>" style="padding: 6px 12px; font-size: 13px; font-weight: 700;"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
