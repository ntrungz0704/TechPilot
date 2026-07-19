<!-- WIDGET XANH DƯƠNG TRÊN CÙNG -->
<div class="pc-builder-widget" style="background: linear-gradient(135deg, #0B63E5, #0051C4); border-radius: var(--radius-card); padding: 20px 24px; display: flex; justify-content: space-between; align-items: center; color: #FFFFFF; margin-bottom: 24px; box-shadow: 0 10px 20px -5px rgba(11, 99, 229, 0.25);">
    <div style="display: flex; align-items: center; gap: 16px;">
        <div style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div>
            <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 4px;">Quản lý cấu hình PC</h3>
            <p style="font-size: 13px; color: rgba(255, 255, 255, 0.85); font-weight: 500;">Kiểm tra tương thích linh kiện và build PC chuyên nghiệp</p>
        </div>
    </div>
    <a href="#" class="pc-builder-widget__btn" style="width: 42px; height: 42px; background: #FFFFFF; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #0B63E5; text-decoration: none; font-size: 16px; transition: var(--transition);">
        <i class="fa-solid fa-arrow-right"></i>
    </a>
</div>

<!-- LƯỚI THẺ THỐNG KÊ (STATS GRID) -->
<div class="stats-grid">
    <!-- Stat 1: Doanh thu COD -->
    <div class="stat-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%;">
            <div>
                <span class="stat-label">Doanh thu COD <i class="fa-solid fa-circle-info" title="Tổng doanh thu từ đơn hàng hoàn thành" style="font-size: 11px; cursor: help;"></i></span>
                <strong class="stat-value"><?= formatPrice($stats['total_revenue']) ?></strong>
                <div class="stat-trend">
                    <span class="trend-badge" style="color: #10B981; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 4px;"><i class="fa-solid fa-arrow-up" style="font-size: 11px;"></i> 18,6%</span>
                    <span class="trend-text">so với 7 ngày trước</span>
                </div>
            </div>
            <div class="stat-icon-wrapper stat-icon--blue">
                <i class="fa-solid fa-chart-line"></i>
            </div>
        </div>
    </div>

    <!-- Stat 2: Đơn hàng -->
    <div class="stat-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%;">
            <div>
                <span class="stat-label">Đơn hàng <i class="fa-solid fa-circle-info" title="Tổng số lượng đơn hàng" style="font-size: 11px; cursor: help;"></i></span>
                <strong class="stat-value"><?= number_format($stats['total_orders']) ?></strong>
                <div class="stat-trend">
                    <span class="trend-badge" style="color: #10B981; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 4px;"><i class="fa-solid fa-arrow-up" style="font-size: 11px;"></i> 12,3%</span>
                    <span class="trend-text">so với 7 ngày trước</span>
                </div>
            </div>
            <div class="stat-icon-wrapper stat-icon--green">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
        </div>
    </div>

    <!-- Stat 3: Khách hàng -->
    <div class="stat-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%;">
            <div>
                <span class="stat-label">Khách hàng <i class="fa-solid fa-circle-info" title="Tổng số tài khoản đã đăng ký" style="font-size: 11px; cursor: help;"></i></span>
                <strong class="stat-value"><?= number_format($stats['total_users']) ?></strong>
                <div class="stat-trend">
                    <span class="trend-badge" style="color: #10B981; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 4px;"><i class="fa-solid fa-arrow-up" style="font-size: 11px;"></i> 9,7%</span>
                    <span class="trend-text">so với 7 ngày trước</span>
                </div>
            </div>
            <div class="stat-icon-wrapper stat-icon--orange">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>
    </div>

    <!-- Stat 4: Sản phẩm -->
    <div class="stat-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%;">
            <div>
                <span class="stat-label">Sản phẩm <i class="fa-solid fa-circle-info" title="Tổng số mặt hàng trong danh mục" style="font-size: 11px; cursor: help;"></i></span>
                <strong class="stat-value"><?= number_format($stats['total_products']) ?></strong>
                <div class="stat-trend">
                    <span class="trend-badge" style="color: #10B981; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 4px;"><i class="fa-solid fa-arrow-up" style="font-size: 11px;"></i> 5,4%</span>
                    <span class="trend-text">so với 7 ngày trước</span>
                </div>
            </div>
            <div class="stat-icon-wrapper stat-icon--purple">
                <i class="fa-solid fa-box"></i>
            </div>
        </div>
    </div>
</div>

<!-- KHU VỰC BIỂU ĐỒ (CHARTS GRID) -->
<div class="charts-grid" style="display: grid; grid-template-columns: 1.3fr 0.7fr; gap: 24px; margin-bottom: 24px;">
    <!-- Biểu đồ doanh thu 7 ngày -->
    <div class="card" style="margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 class="card-title" style="margin-bottom: 0;">Doanh thu 7 ngày gần nhất</h3>
            <div style="display: flex; gap: 16px; font-size: 12px; font-weight: 600; color: var(--text-secondary);">
                <span><i class="fa-solid fa-circle" style="color: #0B63E5;"></i> Doanh thu (đ)</span>
                <span><i class="fa-solid fa-circle" style="color: #3B82F6;"></i> Đơn hàng</span>
            </div>
        </div>
        <!-- SVG Chart representation matching mockup styling -->
        <div style="position: relative; height: 260px; width: 100%; display: flex; flex-direction: column; justify-content: space-between; padding-top: 10px;">
            <!-- Grid background lines -->
            <div style="position: absolute; left: 0; right: 0; top: 0; bottom: 30px; display: flex; flex-direction: column; justify-content: space-between; pointer-events: none;">
                <div style="border-top: 1px dashed #E2E8F0; width: 100%;"></div>
                <div style="border-top: 1px dashed #E2E8F0; width: 100%;"></div>
                <div style="border-top: 1px dashed #E2E8F0; width: 100%;"></div>
                <div style="border-top: 1px dashed #E2E8F0; width: 100%;"></div>
                <div style="border-top: 1px dashed #E2E8F0; width: 100%;"></div>
            </div>
            
            <!-- Graphic content: Bars and lines -->
            <div style="position: relative; flex: 1; margin-bottom: 20px; display: flex; justify-content: space-around; align-items: flex-end; padding: 0 10px; z-index: 2;">
                <!-- Day 1 -->
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; width: 40px; position: relative;">
                    <div style="height: 60%; width: 18px; background: #0B63E5; border-radius: 4px 4px 0 0;" title="Doanh thu: 15.000.000đ"></div>
                </div>
                <!-- Day 2 -->
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; width: 40px; position: relative;">
                    <div style="height: 75%; width: 18px; background: #0B63E5; border-radius: 4px 4px 0 0;" title="Doanh thu: 20.000.000đ"></div>
                </div>
                <!-- Day 3 -->
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; width: 40px; position: relative;">
                    <div style="height: 50%; width: 18px; background: #0B63E5; border-radius: 4px 4px 0 0;" title="Doanh thu: 12.000.000đ"></div>
                </div>
                <!-- Day 4 -->
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; width: 40px; position: relative;">
                    <div style="height: 65%; width: 18px; background: #0B63E5; border-radius: 4px 4px 0 0;" title="Doanh thu: 16.000.000đ"></div>
                </div>
                <!-- Day 5 -->
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; width: 40px; position: relative;">
                    <div style="height: 45%; width: 18px; background: #0B63E5; border-radius: 4px 4px 0 0;" title="Doanh thu: 11.000.000đ"></div>
                </div>
                <!-- Day 6 -->
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; width: 40px; position: relative;">
                    <div style="height: 60%; width: 18px; background: #0B63E5; border-radius: 4px 4px 0 0;" title="Doanh thu: 15.000.000đ"></div>
                </div>
                <!-- Day 7 -->
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; width: 40px; position: relative;">
                    <div style="height: 80%; width: 18px; background: #0B63E5; border-radius: 4px 4px 0 0;" title="Doanh thu: 24.000.000đ"></div>
                </div>
                
                <!-- Overlay line path for order counts -->
                <svg style="position: absolute; left: 0; top: 0; width: 100%; height: 100%; pointer-events: none;">
                    <path d="M 40,110 L 115,70 L 190,130 L 265,95 L 340,140 L 415,100 L 490,50" fill="none" stroke="#60A5FA" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                    <!-- Points -->
                    <circle cx="40" cy="110" r="5" fill="#3B82F6" stroke="#FFFFFF" stroke-width="2" />
                    <circle cx="115" cy="70" r="5" fill="#3B82F6" stroke="#FFFFFF" stroke-width="2" />
                    <circle cx="190" cy="130" r="5" fill="#3B82F6" stroke="#FFFFFF" stroke-width="2" />
                    <circle cx="265" cy="95" r="5" fill="#3B82F6" stroke="#FFFFFF" stroke-width="2" />
                    <circle cx="340" cy="140" r="5" fill="#3B82F6" stroke="#FFFFFF" stroke-width="2" />
                    <circle cx="415" cy="100" r="5" fill="#3B82F6" stroke="#FFFFFF" stroke-width="2" />
                    <circle cx="490" cy="50" r="5" fill="#3B82F6" stroke="#FFFFFF" stroke-width="2" />
                </svg>
            </div>
            
            <!-- X Axis labels -->
            <div style="display: flex; justify-content: space-around; border-top: 1px solid var(--border); padding-top: 8px; font-size: 11px; font-weight: 600; color: var(--text-secondary);">
                <span>14/05</span>
                <span>15/05</span>
                <span>16/05</span>
                <span>17/05</span>
                <span>18/05</span>
                <span>19/05</span>
                <span>20/05</span>
            </div>
        </div>
    </div>
    
    <!-- Biểu đồ donut trạng thái đơn -->
    <div class="card" style="margin-bottom: 0;">
        <h3 class="card-title" style="margin-bottom: 15px;">Trạng thái đơn hàng</h3>
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%;">
            <!-- Donut chart generated via CSS conic-gradient -->
            <div class="donut-chart" style="width: 140px; height: 140px; border-radius: 50%; background: conic-gradient(#EF4444 0% 9.1%, #F59E0B 9.1% 24.7%, #10B981 24.7% 54.8%, #3B82F6 54.8% 100%); display: flex; align-items: center; justify-content: center; margin-bottom: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.03);">
                <!-- Inner circle to create the donut hole -->
                <div style="width: 90px; height: 90px; border-radius: 50%; background-color: #FFFFFF; display: flex; flex-direction: column; align-items: center; justify-content: center; font-family: sans-serif;">
                    <span style="font-size: 10px; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Tổng đơn</span>
                    <strong style="font-size: 18px; color: var(--text-primary); font-weight: 800;"><?= number_format($stats['total_orders']) ?></strong>
                </div>
            </div>
            
            <!-- Legends list layout matching mockup color tokens -->
            <div style="width: 100%; display: flex; flex-direction: column; gap: 8px; font-size: 12px; font-weight: 500;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="fa-solid fa-circle" style="color: #3B82F6; margin-right: 6px; font-size: 10px;"></i> Chờ xác nhận</span>
                    <span style="font-weight: 600; color: var(--text-primary);">45.2%</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="fa-solid fa-circle" style="color: #10B981; margin-right: 6px; font-size: 10px;"></i> Đang giao</span>
                    <span style="font-weight: 600; color: var(--text-primary);">30.1%</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="fa-solid fa-circle" style="color: #F59E0B; margin-right: 6px; font-size: 10px;"></i> Hoàn thành</span>
                    <span style="font-weight: 600; color: var(--text-primary);">15.6%</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="fa-solid fa-circle" style="color: #EF4444; margin-right: 6px; font-size: 10px;"></i> Đã hủy</span>
                    <span style="font-weight: 600; color: var(--text-primary);">9.1%</span>
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
                    <div class="low-stock-item" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-elem); background-color: #FFFFFF; transition: var(--transition);">
                        <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                            <!-- Product Mini Image -->
                            <img src="<?= productImageUrl($prod['image'] ?? '') ?>" alt="<?= e($prod['name']) ?>" style="width: 42px; height: 42px; object-fit: contain; border: 1px solid var(--border); border-radius: 6px; padding: 2px; background: #FFFFFF; flex-shrink: 0;" onerror="this.src='https://placehold.co/100x100?text=SP'">
                            <div style="min-width: 0;">
                                <span class="product-title-cell" style="font-size: 13px; font-weight: 600; color: var(--text-primary); margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;" title="<?= e($prod['name']) ?>"><?= e($prod['name']) ?></span>
                                <small style="color: var(--text-secondary); font-size: 11px; font-weight: 500; display: block;">Giá: <?= formatPrice($prod['price']) ?></small>
                            </div>
                        </div>
                        <div style="text-align: right; flex-shrink: 0;">
                            <span class="badge badge--danger" style="background-color: #FEE2E2; color: #EF4444; border-radius: 6px; padding: 4px 8px; font-size: 11px; font-weight: 700; display: inline-block;">Còn <?= (int)$prod['stock'] ?></span>
                        </div>
                    </div>
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
