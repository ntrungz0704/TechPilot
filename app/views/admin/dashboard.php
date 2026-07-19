<div class="stats-grid">
    <!-- Stat card 1: Users -->
    <div class="stat-card">
        <div class="stat-icon stat-icon--blue">
            <i class="fa-solid fa-users"></i>
        </div>
        <div class="stat-details">
            <span class="stat-label">Khách hàng</span>
            <strong class="stat-value"><?= number_format($stats['total_users']) ?></strong>
        </div>
    </div>

    <!-- Stat card 2: Products -->
    <div class="stat-card">
        <div class="stat-icon stat-icon--green">
            <i class="fa-solid fa-box"></i>
        </div>
        <div class="stat-details">
            <span class="stat-label">Sản phẩm</span>
            <strong class="stat-value"><?= number_format($stats['total_products']) ?></strong>
        </div>
    </div>

    <!-- Stat card 3: Orders -->
    <div class="stat-card">
        <div class="stat-icon stat-icon--orange">
            <i class="fa-solid fa-receipt"></i>
        </div>
        <div class="stat-details">
            <span class="stat-label">Đơn hàng</span>
            <strong class="stat-value"><?= number_format($stats['total_orders']) ?></strong>
        </div>
    </div>

    <!-- Stat card 4: Revenue -->
    <div class="stat-card">
        <div class="stat-icon stat-icon--purple">
            <i class="fa-solid fa-hand-holding-dollar"></i>
        </div>
        <div class="stat-details">
            <span class="stat-label">Doanh thu</span>
            <strong class="stat-value" style="font-size: 20px;"><?= formatPrice($stats['total_revenue']) ?></strong>
        </div>
    </div>
</div>

<div class="dashboard-panels">
    <!-- Panel Left: Đơn hàng gần đây -->
    <div class="card" style="margin-bottom: 0;">
        <div class="panel-header">
            <h3 class="card-title"><i class="fa-solid fa-clock-rotate-left" style="color: var(--primary);"></i> Đơn đặt hàng gần đây</h3>
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
                                <td><strong><a href="<?= url('admin/orders/detail/' . $order['id']) ?>" class="order-code-link"><?= e($order['order_code']) ?></a></strong></td>
                                <td><span style="font-weight: 500;"><?= e($order['customer_name']) ?></span></td>
                                <td><strong style="color: var(--text-primary);"><?= formatPrice($order['total_amount']) ?></strong></td>
                                <td>
                                    <?php
                                    $statusClass = 'badge--warning';
                                    if ($order['status'] === 'completed') $statusClass = 'badge--success';
                                    if ($order['status'] === 'cancelled') $statusClass = 'badge--danger';
                                    ?>
                                    <span class="badge <?= $statusClass ?>"><?= e($order['status']) ?></span>
                                </td>
                                <td style="color: var(--text-secondary); font-size: 13px;"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="empty-state">Chưa có đơn hàng nào trong hệ thống.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Panel Right: Tồn kho thấp -->
    <div class="card" style="margin-bottom: 0;">
        <h3 class="card-title"><i class="fa-solid fa-triangle-exclamation" style="color: #EF4444;"></i> Cảnh báo tồn kho</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Tồn kho</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($lowStockProducts)): ?>
                        <?php foreach ($lowStockProducts as $prod): ?>
                            <tr>
                                <td>
                                    <span class="product-title-cell"><?= e($prod['name']) ?></span>
                                    <small style="color: var(--text-secondary); display: block; margin-top: 2px; font-weight: 500;"><?= formatPrice($prod['price']) ?></small>
                                </td>
                                <td>
                                    <span class="badge badge--danger" style="font-weight: 700; padding: 4px 8px;"><?= (int)$prod['stock'] ?> chiếc</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" class="empty-state">Tất cả sản phẩm đều đủ tồn kho (> 10 chiếc).</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 24px;
        margin-bottom: 30px;
    }

    .stat-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-card);
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 20px;
        box-shadow: var(--shadow-card);
        transition: var(--transition);
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -3px rgba(15, 23, 42, 0.06);
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        transition: var(--transition);
    }

    .stat-icon--blue { background-color: rgba(59, 130, 246, 0.1); color: #3B82F6; }
    .stat-icon--green { background-color: rgba(16, 185, 129, 0.1); color: #10B981; }
    .stat-icon--orange { background-color: rgba(245, 158, 11, 0.1); color: #F59E0B; }
    .stat-icon--purple { background-color: rgba(139, 92, 246, 0.1); color: #8B5CF6; }

    .stat-details {
        display: flex;
        flex-direction: column;
    }

    .stat-label {
        font-size: 13px;
        color: var(--text-secondary);
        font-weight: 600;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .stat-value {
        font-size: 24px;
        font-weight: 800;
        color: var(--text-primary);
        letter-spacing: -0.02em;
    }

    .dashboard-panels {
        display: grid;
        grid-template-columns: 1.25fr 0.75fr;
        gap: 24px;
    }

    @media (max-width: 992px) {
        .dashboard-panels {
            grid-template-columns: 1fr;
        }
    }

    .panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .order-code-link {
        color: var(--primary);
        text-decoration: none;
        font-weight: 700;
        transition: var(--transition);
    }

    .order-code-link:hover {
        color: var(--primary-dark);
        text-decoration: underline;
    }

    .empty-state {
        text-align: center;
        color: var(--text-secondary);
        padding: 40px !important;
        font-weight: 500;
    }

    .product-title-cell {
        font-weight: 600;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
        font-size: 13.5px;
        color: var(--text-primary);
    }
</style>
