<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px; margin-bottom: 30px;">
    <!-- Stat card 1 -->
    <div class="card" style="margin-bottom: 0; display: flex; align-items: center; gap: 20px;">
        <div style="width: 54px; height: 54px; border-radius: 50%; background-color: rgba(10, 91, 255, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 24px;">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <span style="font-size: 13.5px; color: var(--text-secondary); font-weight: 500; display: block; margin-bottom: 4px;">Khách hàng</span>
            <strong style="font-size: 24px; font-weight: 800; color: var(--text-primary);"><?= number_format($stats['total_users']) ?></strong>
        </div>
    </div>

    <!-- Stat card 2 -->
    <div class="card" style="margin-bottom: 0; display: flex; align-items: center; gap: 20px;">
        <div style="width: 54px; height: 54px; border-radius: 50%; background-color: rgba(16, 185, 129, 0.1); color: #10B981; display: flex; align-items: center; justify-content: center; font-size: 24px;">
            <i class="fa-solid fa-box"></i>
        </div>
        <div>
            <span style="font-size: 13.5px; color: var(--text-secondary); font-weight: 500; display: block; margin-bottom: 4px;">Sản phẩm</span>
            <strong style="font-size: 24px; font-weight: 800; color: var(--text-primary);"><?= number_format($stats['total_products']) ?></strong>
        </div>
    </div>

    <!-- Stat card 3 -->
    <div class="card" style="margin-bottom: 0; display: flex; align-items: center; gap: 20px;">
        <div style="width: 54px; height: 54px; border-radius: 50%; background-color: rgba(245, 158, 11, 0.1); color: #F59E0B; display: flex; align-items: center; justify-content: center; font-size: 24px;">
            <i class="fa-solid fa-receipt"></i>
        </div>
        <div>
            <span style="font-size: 13.5px; color: var(--text-secondary); font-weight: 500; display: block; margin-bottom: 4px;">Đơn hàng</span>
            <strong style="font-size: 24px; font-weight: 800; color: var(--text-primary);"><?= number_format($stats['total_orders']) ?></strong>
        </div>
    </div>

    <!-- Stat card 4 -->
    <div class="card" style="margin-bottom: 0; display: flex; align-items: center; gap: 20px;">
        <div style="width: 54px; height: 54px; border-radius: 50%; background-color: rgba(139, 92, 246, 0.1); color: #8B5CF6; display: flex; align-items: center; justify-content: center; font-size: 24px;">
            <i class="fa-solid fa-hand-holding-dollar"></i>
        </div>
        <div>
            <span style="font-size: 13.5px; color: var(--text-secondary); font-weight: 500; display: block; margin-bottom: 4px;">Doanh thu</span>
            <strong style="font-size: 22px; font-weight: 800; color: var(--text-primary);"><?= formatPrice($stats['total_revenue']) ?></strong>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 30px;">
    <!-- Panel Left: Đơn hàng gần đây -->
    <div class="card" style="margin-bottom: 0;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
            <h3 class="card-title" style="margin-bottom: 0;">Đơn đặt hàng gần đây</h3>
            <a href="<?= url('admin/orders') ?>" class="btn btn--outline btn--sm" style="font-size: 12px; padding: 6px 12px;">Xem tất cả</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentOrders)): ?>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><strong><a href="<?= url('admin/orders/detail/' . $order['id']) ?>" style="color: var(--primary); text-decoration: none;"><?= e($order['order_code']) ?></a></strong></td>
                                <td><?= e($order['customer_name']) ?></td>
                                <td><strong><?= formatPrice($order['total_amount']) ?></strong></td>
                                <td>
                                    <?php
                                    $statusClass = 'badge--warning';
                                    if ($order['status'] === 'completed') $statusClass = 'badge--success';
                                    if ($order['status'] === 'cancelled') $statusClass = 'badge--danger';
                                    ?>
                                    <span class="badge <?= $statusClass ?>"><?= e($order['status']) ?></span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 30px;">Chưa có đơn hàng nào trong hệ thống.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Panel Right: Tồn kho thấp -->
    <div class="card" style="margin-bottom: 0;">
        <h3 class="card-title">Cảnh báo tồn kho thấp</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Tồn</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($lowStockProducts)): ?>
                        <?php foreach ($lowStockProducts as $prod): ?>
                            <tr>
                                <td>
                                    <span style="font-weight: 600; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; font-size: 13.5px;"><?= e($prod['name']) ?></span>
                                    <small style="color: var(--text-secondary);"><?= formatPrice($prod['price']) ?></small>
                                </td>
                                <td>
                                    <span class="badge badge--danger" style="font-size: 12px; font-weight: 700;"><?= (int)$prod['stock'] ?> chiếc</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" style="text-align: center; color: var(--text-secondary); padding: 30px;">Tất cả sản phẩm đều đủ tồn kho (> 10 chiếc).</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
