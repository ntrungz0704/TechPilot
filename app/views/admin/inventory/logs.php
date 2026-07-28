<div class="card">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <div>
            <h3 class="card-title" style="margin-bottom: 4px;">Lịch sử Nhập / Xuất kho & Biến động tồn kho</h3>
            <p style="font-size: 13px; color: var(--text-secondary); margin: 0;">Theo dõi chi tiết tất cả giao dịch nhập kho, xuất kho, đặt hàng và hoàn kho</p>
        </div>
        <a href="<?= url('admin/products') ?>" class="btn btn--outline"><i class="fa-solid fa-arrow-left"></i> Quay lại Danh sách sản phẩm</a>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 70px;">ID</th>
                    <th style="width: 60px;">Ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th>Loại giao dịch</th>
                    <th>Số lượng</th>
                    <th>Tồn trước</th>
                    <th>Tồn sau</th>
                    <th>Ghi chú / Lý do</th>
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
                                <strong style="font-size: 13px; max-width: 260px; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= e($log['product_name'] ?? 'Sản phẩm #' . $log['product_id']) ?></strong>
                            </td>
                            <td>
                                <?php
                                $type = $log['type'];
                                if ($type === 'import'): ?>
                                    <span class="badge" style="background-color: #DCFCE7; color: #15803D; font-weight: 700;"><i class="fa-solid fa-square-plus"></i> Nhập kho</span>
                                <?php elseif ($type === 'export'): ?>
                                    <span class="badge" style="background-color: #FEE2E2; color: #B91C1C; font-weight: 700;"><i class="fa-solid fa-square-minus"></i> Xuất kho</span>
                                <?php elseif ($type === 'order_reserve'): ?>
                                    <span class="badge" style="background-color: #FEF3C7; color: #B45309; font-weight: 700;"><i class="fa-solid fa-cart-shopping"></i> Giữ hàng đơn</span>
                                <?php elseif ($type === 'order_release'): ?>
                                    <span class="badge" style="background-color: #E0F2FE; color: #0369A1; font-weight: 700;"><i class="fa-solid fa-rotate-left"></i> Hoàn kho đơn</span>
                                <?php else: ?>
                                    <span class="badge" style="background-color: #F3E8FF; color: #6B21A8; font-weight: 700;"><i class="fa-solid fa-sliders"></i> Điều chỉnh</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php $qty = (int)$log['quantity']; ?>
                                <strong style="color: <?= $qty > 0 ? '#16A34A' : '#DC2626' ?>; font-size: 14px;">
                                    <?= $qty > 0 ? '+' . $qty : $qty ?>
                                </strong>
                            </td>
                            <td><?= number_format((int)$log['old_stock']) ?></td>
                            <td><strong style="color: var(--text-primary);"><?= number_format((int)$log['new_stock']) ?></strong></td>
                            <td>
                                <span style="font-size: 12.5px; color: var(--text-secondary);"><?= e($log['note'] ?? '—') ?></span>
                            </td>
                            <td>
                                <span style="font-size: 12.5px; font-weight: 600;"><?= e($log['user_name'] ?? 'Hệ thống Admin') ?></span>
                            </td>
                            <td>
                                <small style="color: var(--text-secondary); white-space: nowrap;"><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></small>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" style="text-align: center; color: var(--text-secondary); padding: 35px;">Chưa có lịch sử biến động kho nào được ghi nhận.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div style="display: flex; justify-content: center; align-items: center; gap: 6px; margin-top: 25px; flex-wrap: wrap;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="<?= url("admin/inventory/logs?page={$i}" . ($productId > 0 ? "&product_id={$productId}" : "")) ?>" class="btn <?= $page === $i ? '' : 'btn--outline' ?>" style="padding: 6px 12px; font-size: 13px; font-weight: 700;"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
