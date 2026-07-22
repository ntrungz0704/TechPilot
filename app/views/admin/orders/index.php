<div class="card">
    <h3 class="card-title">Quản lý đơn đặt hàng</h3>

    <!-- Filters & Search Form -->
    <form method="get" action="<?= url('admin/orders') ?>" style="margin-bottom: 25px; display: flex; gap: 15px; flex-wrap: wrap;">
        <input type="text" name="search" class="form-control" placeholder="Tìm theo mã đơn, tên, điện thoại..." value="<?= e($search) ?>" style="max-width: 320px;">
        
        <select name="status" class="form-control" style="max-width: 200px;">
            <option value="">Tất cả trạng thái</option>
            <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Chờ xử lý (Pending)</option>
            <option value="confirmed" <?= $status === 'confirmed' ? 'selected' : '' ?>>Đã xác nhận (Confirmed)</option>
            <option value="processing" <?= $status === 'processing' ? 'selected' : '' ?>>Đang xử lý (Processing)</option>
            <option value="shipping" <?= $status === 'shipping' ? 'selected' : '' ?>>Đang giao hàng (Shipping)</option>
            <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Hoàn thành (Completed)</option>
            <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Đã huỷ (Cancelled)</option>
            <option value="returned" <?= $status === 'returned' ? 'selected' : '' ?>>Trả hàng (Returned)</option>
        </select>

        <button type="submit" class="btn btn--outline"><i class="fa-solid fa-filter"></i> Lọc đơn</button>
        
        <?php if ($search !== '' || $status !== ''): ?>
            <a href="<?= url('admin/orders') ?>" class="btn btn--secondary">Xoá lọc</a>
        <?php endif; ?>
    </form>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Điện thoại</th>
                    <th>Tổng tiền</th>
                    <th>P.Thức thanh toán</th>
                    <th>Trạng thái đơn</th>
                    <th>Trạng thái T.Toán</th>
                    <th>Ngày đặt hàng</th>
                    <th style="width: 120px; text-align: center;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><strong><a href="<?= url('admin/orders/detail/' . $order['id']) ?>" style="color: var(--primary); text-decoration: none;"><?= e($order['order_code']) ?></a></strong></td>
                            <td><?= e($order['customer_name']) ?></td>
                            <td><?= e(formatPhone($order['phone'])) ?></td>
                            <td><strong><?= formatPrice($order['total_amount']) ?></strong></td>
                            <td><span style="font-size: 13px; font-weight: 600;"><?= e($order['payment_method']) ?></span></td>
                            <td>
                                <?php
                                $statusClass = 'badge--warning';
                                if ($order['status'] === 'completed') $statusClass = 'badge--success';
                                if ($order['status'] === 'cancelled') $statusClass = 'badge--danger';
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= e($order['status']) ?></span>
                            </td>
                            <td>
                                <?php
                                $payClass = 'badge--warning';
                                if ($order['payment_status'] === 'paid') $payClass = 'badge--success';
                                if ($order['payment_status'] === 'failed') $payClass = 'badge--danger';
                                ?>
                                <span class="badge <?= $payClass ?>"><?= e($order['payment_status']) ?></span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                            <td style="text-align: center;">
                                <a href="<?= url('admin/orders/detail/' . $order['id']) ?>" class="btn btn--outline btn--sm" style="padding: 6px 12px; font-size: 12px;"><i class="fa-solid fa-eye"></i> Chi tiết</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; color: var(--text-secondary); padding: 30px;">Không tìm thấy đơn hàng nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div style="display: flex; justify-content: center; gap: 8px; margin-top: 25px;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php
                $query = $_GET;
                $query['page'] = $i;
                $pageUrl = url('admin/orders?' . http_build_query($query));
                ?>
                <a href="<?= $pageUrl ?>" class="btn <?= $page === $i ? '' : 'btn--outline' ?>" style="padding: 6px 12px; font-size: 13px;"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
