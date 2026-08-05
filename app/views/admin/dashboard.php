<!-- LƯỚI THẺ THỐNG KÊ (STATS GRID) -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <!-- Stat 1: Doanh thu COD -->
    <div class="stat-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%;">
            <div>
                <span class="stat-label">Doanh thu <i class="fa-solid fa-circle-info" title="Doanh thu từ các đơn hàng đã hoàn thành" style="font-size: 11px; cursor: help;"></i></span>
                <strong class="stat-value"><?= formatPrice($stats['total_revenue']) ?></strong>
                <div class="stat-trend">
                    <span class="trend-badge" style="color: #10B981; font-weight: 700; font-size: 12px;"><i class="fa-solid fa-check"></i> Đã hoàn thành</span>
                </div>
            </div>
            <div class="stat-icon-wrapper stat-icon--blue">
                <i class="fa-solid fa-chart-line"></i>
            </div>
        </div>
    </div>

    <!-- Stat 2: Tổng mẫu sản phẩm -->
    <div class="stat-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%;">
            <div>
                <span class="stat-label">Tổng mẫu sản phẩm <i class="fa-solid fa-circle-info" title="Số model/SKU trong hệ thống" style="font-size: 11px; cursor: help;"></i></span>
                <strong class="stat-value" id="val_total_product_models"><?= number_format($stats['total_product_models']) ?></strong>
                <div class="stat-trend">
                    <span class="trend-text">Đang bán: <strong id="val_active_product_models" style="color: var(--primary);"><?= number_format($stats['active_product_models']) ?></strong> mẫu</span>
                </div>
            </div>
            <div class="stat-icon-wrapper stat-icon--purple">
                <i class="fa-solid fa-cubes"></i>
            </div>
        </div>
    </div>

    <!-- Stat 3: Tổng đơn vị tồn kho -->
    <div class="stat-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%;">
            <div>
                <span class="stat-label">Tổng đơn vị tồn kho <i class="fa-solid fa-circle-info" title="Tổng số lượng sản phẩm vật lý còn lại trong kho" style="font-size: 11px; cursor: help;"></i></span>
                <strong class="stat-value" id="val_total_inventory_units" style="color: #2563EB;"><?= number_format($stats['total_inventory_units']) ?></strong>
                <div class="stat-trend">
                    <span class="trend-text">Đã bán: <strong id="val_total_sold_units" style="color: #10B981;"><?= number_format($stats['total_sold_units']) ?></strong> đơn vị</span>
                </div>
            </div>
            <div class="stat-icon-wrapper stat-icon--green">
                <i class="fa-solid fa-warehouse"></i>
            </div>
        </div>
    </div>

    <!-- Stat 4: Sắp hết hàng -->
    <div class="stat-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%;">
            <div>
                <span class="stat-label">Sắp hết hàng <i class="fa-solid fa-circle-info" title="Số mẫu có tồn kho từ 1 đến 9 sản phẩm" style="font-size: 11px; cursor: help;"></i></span>
                <strong class="stat-value" id="val_low_stock_models" style="color: #D97706;"><?= number_format($stats['low_stock_models']) ?></strong>
                <div class="stat-trend">
                    <span class="trend-text" style="color: #D97706;">Tồn từ 1 - 9 sản phẩm</span>
                </div>
            </div>
            <div class="stat-icon-wrapper stat-icon--orange">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>
    </div>

    <!-- Stat 5: Hết hàng -->
    <div class="stat-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%;">
            <div>
                <span class="stat-label">Hết hàng <i class="fa-solid fa-circle-info" title="Số mẫu có tồn kho bằng 0" style="font-size: 11px; cursor: help;"></i></span>
                <strong class="stat-value" id="val_out_of_stock_models" style="color: #DC2626;"><?= number_format($stats['out_of_stock_models']) ?></strong>
                <div class="stat-trend">
                    <span class="trend-text" style="color: #DC2626;">Cần nhập thêm hàng</span>
                </div>
            </div>
            <div class="stat-icon-wrapper stat-icon--red">
                <i class="fa-solid fa-ban"></i>
            </div>
        </div>
    </div>
</div>

<!-- KHU VỰC BIỂU ĐỒ (CHARTS GRID) -->
<div class="charts-grid" style="display: grid; grid-template-columns: 1.3fr 0.7fr; gap: 24px; margin-bottom: 24px;">
    <?php
    // Prepare dynamic SVG line coordinates and bar heights
    $chartLabels = $chartLabels ?? ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'];
    $dailyRevenue = $dailyRevenue ?? [0, 0, 0, 0, 0, 0, 0];
    $dailyOrders = $dailyOrders ?? [0, 0, 0, 0, 0, 0, 0];
    $maxRev = max($dailyRevenue) ?: 1000000;
    $maxOrd = max($dailyOrders) ?: 1;

    $xCoords = [40, 115, 190, 265, 340, 415, 490];
    $svgPathPoints = [];
    $circlePoints = [];

    for ($i = 0; $i < 7; $i++) {
        $x = $xCoords[$i];
        $ord = $dailyOrders[$i] ?? 0;
        $y = 150 - (int)(($ord / $maxOrd) * 90);
        $svgPathPoints[] = "{$x},{$y}";
        $circlePoints[] = ['x' => $x, 'y' => $y, 'val' => $ord];
    }
    $pathD = implode(' L ', $svgPathPoints);

    // Conic gradient calculations for Donut Chart
    $statusPcts = $statusPcts ?? ['pending' => 0, 'shipping' => 0, 'completed' => 100, 'cancelled' => 0];
    $pPending = $statusPcts['pending'] ?? 0;
    $pShipping = $statusPcts['shipping'] ?? 0;
    $pCompleted = $statusPcts['completed'] ?? 0;
    $pCancelled = $statusPcts['cancelled'] ?? 0;

    $stop1 = $pPending;
    $stop2 = $stop1 + $pShipping;
    $stop3 = $stop2 + $pCompleted;
    $conicGrad = "#3B82F6 0% {$stop1}%, #06B6D4 {$stop1}% {$stop2}%, #10B981 {$stop2}% {$stop3}%, #EF4444 {$stop3}% 100%";
    ?>

    <!-- Biểu đồ doanh thu 7 ngày -->
    <div class="card" style="margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 class="card-title" style="margin-bottom: 0;"><i class="fa-solid fa-chart-column" style="color: #8B5CF6;"></i> Doanh thu 7 ngày gần nhất (Realtime)</h3>
            <div style="display: flex; gap: 16px; font-size: 12px; font-weight: 600; color: var(--text-secondary);">
                <span><i class="fa-solid fa-square" style="color: #8B5CF6; border-radius: 2px;"></i> Doanh thu (VND)</span>
                <span><i class="fa-solid fa-circle" style="color: #06B6D4;"></i> Số đơn hàng</span>
            </div>
        </div>

        <div style="position: relative; height: 260px; width: 100%; display: flex; flex-direction: column; justify-content: space-between; padding-top: 10px;">
            <!-- Grid background lines -->
            <div style="position: absolute; left: 0; right: 0; top: 0; bottom: 30px; display: flex; flex-direction: column; justify-content: space-between; pointer-events: none;">
                <div style="border-top: 1px dashed var(--border); width: 100%;"></div>
                <div style="border-top: 1px dashed var(--border); width: 100%;"></div>
                <div style="border-top: 1px dashed var(--border); width: 100%;"></div>
                <div style="border-top: 1px dashed var(--border); width: 100%;"></div>
                <div style="border-top: 1px dashed var(--border); width: 100%;"></div>
            </div>
            
            <!-- Graphic content: Bars and lines -->
            <div style="position: relative; flex: 1; margin-bottom: 20px; display: flex; justify-content: space-around; align-items: flex-end; padding: 0 10px; z-index: 2;">
                <?php for ($i = 0; $i < 7; $i++): ?>
                    <?php
                    $rev = $dailyRevenue[$i] ?? 0;
                    $barH = $maxRev > 0 ? max(12, (int)(($rev / $maxRev) * 85)) : 12;
                    ?>
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; width: 40px; position: relative;">
                        <div style="height: <?= $barH ?>%; width: 20px; background: linear-gradient(180deg, #8B5CF6 0%, #6366F1 100%); border-radius: 6px 6px 0 0; transition: all 0.3s;" title="Doanh thu: <?= formatPrice($rev) ?>"></div>
                    </div>
                <?php endfor; ?>
                
                <!-- Overlay line path for order counts -->
                <svg style="position: absolute; left: 0; top: 0; width: 100%; height: 100%; pointer-events: none;">
                    <path d="M <?= $pathD ?>" fill="none" stroke="#06B6D4" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                    <?php foreach ($circlePoints as $pt): ?>
                        <circle cx="<?= $pt['x'] ?>" cy="<?= $pt['y'] ?>" r="5" fill="#06B6D4" stroke="#FFFFFF" stroke-width="2" />
                    <?php endforeach; ?>
                </svg>
            </div>
            
            <!-- X Axis labels -->
            <div style="display: flex; justify-content: space-around; border-top: 1px solid var(--border); padding-top: 8px; font-size: 11.5px; font-weight: 700; color: var(--text-secondary);">
                <?php foreach ($chartLabels as $lbl): ?>
                    <span><?= e($lbl) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Biểu đồ donut trạng thái đơn -->
    <div class="card" style="margin-bottom: 0;">
        <h3 class="card-title" style="margin-bottom: 15px;"><i class="fa-solid fa-chart-pie" style="color: #3B82F6;"></i> Trạng thái đơn hàng</h3>
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%;">
            <!-- Donut chart generated via CSS conic-gradient -->
            <div class="donut-chart" style="width: 140px; height: 140px; border-radius: 50%; background: conic-gradient(<?= $conicGrad ?>); display: flex; align-items: center; justify-content: center; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <!-- Inner circle to create the donut hole -->
                <div class="donut-inner-circle" style="width: 90px; height: 90px; border-radius: 50%; background-color: var(--bg-card); display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <span style="font-size: 10px; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Tổng đơn</span>
                    <strong style="font-size: 18px; color: var(--text-primary); font-weight: 800;"><?= number_format($stats['total_orders']) ?></strong>
                </div>
            </div>
            
            <!-- Legends list layout with real DB data -->
            <div style="width: 100%; display: flex; flex-direction: column; gap: 8px; font-size: 12px; font-weight: 500;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="fa-solid fa-circle" style="color: #3B82F6; margin-right: 6px; font-size: 10px;"></i> Chờ xác nhận (<?= number_format($statusCounts['pending'] ?? 0) ?>)</span>
                    <span style="font-weight: 700; color: var(--text-primary);"><?= $pPending ?>%</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="fa-solid fa-circle" style="color: #06B6D4; margin-right: 6px; font-size: 10px;"></i> Đang giao (<?= number_format($statusCounts['shipping'] ?? 0) ?>)</span>
                    <span style="font-weight: 700; color: var(--text-primary);"><?= $pShipping ?>%</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="fa-solid fa-circle" style="color: #10B981; margin-right: 6px; font-size: 10px;"></i> Hoàn thành (<?= number_format($statusCounts['completed'] ?? 0) ?>)</span>
                    <span style="font-weight: 700; color: var(--text-primary);"><?= $pCompleted ?>%</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="fa-solid fa-circle" style="color: #EF4444; margin-right: 6px; font-size: 10px;"></i> Đã hủy (<?= number_format($statusCounts['cancelled'] ?? 0) ?>)</span>
                    <span style="font-weight: 700; color: var(--text-primary);"><?= $pCancelled ?>%</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- LƯỚI BẢNG DỮ LIỆU CHÍNH (TABLES GRID) -->
<div class="dashboard-panels">
    <!-- Cột bên trái: Đơn hàng mới -->
    <div class="card" style="margin-bottom: 0;">
        <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
            <h3 class="card-title" style="margin-bottom: 0;"><i class="fa-solid fa-clock-rotate-left" style="color: var(--primary);"></i> Đơn hàng mới</h3>
            <a href="<?= url('admin/orders') ?>" class="btn btn--outline btn--sm" style="font-size: 11.5px; padding: 6px 12px; font-weight: 600;">Xem tất cả</a>
        </div>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Thời gian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentOrders)): ?>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td>
                                    <strong><a href="<?= url('admin/orders/detail/' . $order['id']) ?>" class="order-code-link" style="color: #0B63E5; font-weight: 700; text-decoration: none;"><?= e($order['order_code']) ?></a></strong>
                                </td>
                                <td>
                                    <span style="font-weight: 600; color: var(--text-primary);"><?= e($order['customer_name']) ?></span>
                                </td>
                                <td>
                                    <strong style="color: var(--text-primary);"><?= formatPrice($order['total_amount']) ?></strong>
                                </td>
                                <td>
                                    <?php
                                    $badgeStyle = 'background-color: #FEF3C7; color: #D97706;'; // pending/chờ xác nhận
                                    $statusName = 'Chờ xác nhận';
                                    
                                    if ($order['status'] === 'completed') {
                                        $badgeStyle = 'background-color: #DCFCE7; color: #15803D;'; // completed
                                        $statusName = 'Hoàn thành';
                                    } elseif ($order['status'] === 'shipping') {
                                        $badgeStyle = 'background-color: #E0F2FE; color: #0369A1;'; // shipping
                                        $statusName = 'Đang giao';
                                    } elseif ($order['status'] === 'cancelled') {
                                        $badgeStyle = 'background-color: #FEE2E2; color: #B91C1C;'; // cancelled
                                        $statusName = 'Đã hủy';
                                    }
                                    ?>
                                    <span class="badge" style="<?= $badgeStyle ?> padding: 4px 10px; border-radius: 9999px; font-size: 11px; font-weight: 600; text-transform: none;"><?= $statusName ?></span>
                                </td>
                                <td style="color: var(--text-secondary); font-size: 12.5px; font-weight: 500;">
                                    <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                </td>
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

    <!-- Cột bên phải: Cảnh báo tồn kho thấp -->
    <div class="card" style="margin-bottom: 0;">
        <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
            <h3 class="card-title" style="margin-bottom: 0;"><i class="fa-solid fa-triangle-exclamation" style="color: #EF4444;"></i> Sản phẩm sắp hết hàng</h3>
            <a href="<?= url('admin/products') ?>" class="btn btn--outline btn--sm" style="font-size: 11.5px; padding: 6px 12px; font-weight: 600;">Xem tất cả</a>
        </div>
        
        <div class="low-stock-list" style="display: flex; flex-direction: column; gap: 12px;">
            <?php if (!empty($lowStockProducts)): ?>
                <?php foreach (array_slice($lowStockProducts, 0, 7) as $prod): ?>
                    <a href="<?= url('admin/products/edit/' . (int)$prod['id']) ?>" class="low-stock-item" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-elem); background-color: var(--bg-card); transition: var(--transition); text-decoration: none;">
                        <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                            <!-- Product Mini Image -->
                            <img src="<?= productImageUrl($prod['image'] ?? '', $prod['name'] ?? '') ?>" alt="<?= e($prod['name']) ?>" style="width: 42px; height: 42px; object-fit: contain; border: 1px solid var(--border); border-radius: 6px; padding: 2px; background: var(--bg-card); flex-shrink: 0;" onerror="this.src='https://placehold.co/100x100?text=SP'">
                            <div style="min-width: 0;">
                                <span class="product-title-cell" style="font-size: 13px; font-weight: 600; color: var(--text-primary); margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;" title="<?= e($prod['name']) ?>"><?= e($prod['name']) ?></span>
                                <small style="color: var(--text-secondary); font-size: 11px; font-weight: 500; display: block;">Giá: <?= formatPrice($prod['price']) ?></small>
                            </div>
                        </div>
                        <div style="text-align: right; flex-shrink: 0;">
                            <span class="badge badge--danger" style="background-color: #FEE2E2; color: #EF4444; border-radius: 6px; padding: 4px 8px; font-size: 11px; font-weight: 700; display: inline-block;">Còn <?= (int)$prod['stock'] ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state" style="border: 1px dashed var(--border); border-radius: var(--radius-elem); padding: 30px; text-align: center; color: var(--text-secondary);">
                    Tất cả sản phẩm đều đủ số lượng tồn kho.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    /* PHONG CÁCH TỐI ƯU HÓA CHO DASHBOARD THEO MOCKUP */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 24px;
        margin-bottom: 24px;
    }

    .stat-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-card);
        padding: 24px;
        box-shadow: var(--shadow-card);
        transition: var(--transition);
        display: flex;
        align-items: center;
        position: relative;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px -4px rgba(15, 23, 42, 0.08);
    }

    .stat-label {
        font-size: 13px;
        color: var(--text-secondary);
        font-weight: 600;
        display: block;
        margin-bottom: 6px;
    }

    .stat-value {
        font-size: 22px;
        font-weight: 800;
        color: var(--text-primary);
        letter-spacing: -0.03em;
        display: block;
        margin-bottom: 8px;
    }

    .stat-trend {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11.5px;
    }

    .trend-badge {
        color: #10B981;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }

    .trend-text {
        color: var(--text-secondary);
        font-weight: 500;
    }

    .stat-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    .stat-icon--blue { background-color: #EFF6FF; color: #0B63E5; }
    .stat-icon--green { background-color: #ECFDF5; color: #10B981; }
    .stat-icon--orange { background-color: #FFFBEB; color: #F59E0B; }
    .stat-icon--purple { background-color: #FAF5FF; color: #8B5CF6; }

    .dashboard-panels {
        display: grid;
        grid-template-columns: 1.25fr 0.75fr;
        gap: 24px;
    }

    .pc-builder-widget__btn:hover {
        transform: scale(1.08);
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .low-stock-item:hover {
        border-color: #CBD5E1;
        background-color: #F8FAFC !important;
        transform: translateX(2px);
    }

    @media (max-width: 992px) {
        .charts-grid {
            grid-template-columns: 1fr !important;
        }
        .dashboard-panels {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let inventoryPollTimer = null;

    function fetchInventorySummary() {
        fetch('<?= url("api/inventory/summary") ?>')
            .then(res => res.json())
            .then(res => {
                if (res.success && res.data) {
                    const d = res.data;
                    const elTotalModels = document.getElementById('val_total_product_models');
                    const elActiveModels = document.getElementById('val_active_product_models');
                    const elInventoryUnits = document.getElementById('val_total_inventory_units');
                    const elLowStock = document.getElementById('val_low_stock_models');
                    const elOutOfStock = document.getElementById('val_out_of_stock_models');
                    const elTotalSold = document.getElementById('val_total_sold_units');

                    if (elTotalModels) elTotalModels.innerText = Number(d.total_product_models).toLocaleString('vi-VN');
                    if (elActiveModels) elActiveModels.innerText = Number(d.active_product_models).toLocaleString('vi-VN');
                    if (elInventoryUnits) elInventoryUnits.innerText = Number(d.total_inventory_units).toLocaleString('vi-VN');
                    if (elLowStock) elLowStock.innerText = Number(d.low_stock_models).toLocaleString('vi-VN');
                    if (elOutOfStock) elOutOfStock.innerText = Number(d.out_of_stock_models).toLocaleString('vi-VN');
                    if (elTotalSold) elTotalSold.innerText = Number(d.total_sold_units).toLocaleString('vi-VN');
                }
            })
            .catch(err => console.debug('Inventory poll paused:', err));
    }

    // Polling mỗi 10 giây
    inventoryPollTimer = setInterval(fetchInventorySummary, 10000);

    // Tự động dừng polling khi tab bị ẩn để tiết kiệm tài nguyên
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            if (inventoryPollTimer) clearInterval(inventoryPollTimer);
        } else {
            fetchInventorySummary();
            inventoryPollTimer = setInterval(fetchInventorySummary, 10000);
        }
    });
});
</script>
