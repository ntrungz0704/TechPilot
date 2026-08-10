<?php include ROOT_PATH . '/app/views/layouts/header.php'; ?>

<main class="container section" id="main-content" style="margin-top: 40px; min-height: 60vh;">
    <div style="display: flex; gap: 30px; flex-wrap: wrap;">
        <!-- Left Sidebar Menu -->
        <?php $activeMenu = 'orders'; include ROOT_PATH . '/app/views/profile/_sidebar.php'; ?>

        <!-- Right Content Area -->
        <div style="flex: 1; min-width: 300px;">
            <div class="section__head" style="margin-bottom: 20px;">
                <h2>Đơn hàng của tôi</h2>
            </div>

            <?php if (isset($flashes['success'])): ?>
                <div class="alert alert--success" style="margin-bottom: 20px; padding: 12px; background-color: #DEF7EC; color: #03543F; border-radius: 8px;">
                    <?= e($flashes['success']) ?>
                </div>
            <?php endif; ?>
            <?php if (isset($flashes['error'])): ?>
                <div class="alert alert--danger" style="margin-bottom: 20px; padding: 12px; background-color: #FDE8E8; color: #9B1C1C; border-radius: 8px;">
                    <?= e($flashes['error']) ?>
                </div>
            <?php endif; ?>

            <?php if (empty($orders)): ?>
                <div style="text-align: center; padding: 40px 20px; background-color: var(--bg-white); border: 1px solid var(--border); border-radius: 12px;">
                    <i class="fa-solid fa-cart-arrow-down" style="font-size: 48px; color: var(--text-secondary); margin-bottom: 15px; display: block;"></i>
                    <p style="color: var(--text-secondary);">Bạn chưa đặt đơn hàng nào.</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <?php foreach ($orders as $order): ?>
                        <div style="background-color: var(--bg-white); border: 1px solid var(--border); border-radius: 12px; padding: 20px; box-shadow: var(--shadow-card); display: flex; flex-direction: column; gap: 15px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                                <div>
                                    <strong style="font-size: 15px; color: var(--primary);">Mã đơn: <?= e($order['order_code']) ?></strong>
                                    <span style="font-size: 13px; color: var(--text-secondary); margin-left: 15px;">Ngày đặt: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
                                </div>
                                <div>
                                    <span style="padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; 
                                        <?php if ($order['status'] === 'pending') echo 'background-color: #FEF3C7; color: #D97706;';
                                              elseif ($order['status'] === 'completed' || $order['status'] === 'delivered') echo 'background-color: #D1FAE5; color: #059669;';
                                              else echo 'background-color: #FEE2E2; color: #DC2626;'; ?>">
                                        <?php $statusLabels = ['pending'=>'Chờ xác nhận','confirmed'=>'Đã xác nhận','processing'=>'Đang chuẩn bị hàng','shipping'=>'Đang giao hàng','completed'=>'Hoàn thành','cancelled'=>'Đã hủy']; ?>
                                        <?= e($statusLabels[$order['status']] ?? $order['status']) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                                <div>
                                    <p style="margin: 0; font-size: 14px; color: var(--text-secondary);">Phương thức: <strong><?= e($order['payment_method']) ?></strong></p>
                                    <p style="margin: 4px 0 0 0; font-size: 15px; color: var(--text-secondary);">Tổng số tiền: <strong style="color: var(--primary); font-size: 16px; font-weight: 700;"><?= number_format($order['total_amount'], 0, ',', '.') ?>đ</strong></p>
                                </div>
                                <div style="display: flex; gap: 10px;">
                                    <a href="<?= url('profile/order_detail?id=' . (int)$order['id']) ?>" class="btn btn--light btn--sm">Chi Tiết</a>
                                    <!-- Nút đổi trả chỉ cho phép nếu trạng thái thích hợp -->
                                    <a href="<?= url('profile/return?order_id=' . (int)$order['id']) ?>" class="btn btn--sm" style="background-color: #F59E0B; border-color: #D97706;">Yêu Cầu Đổi Trả</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
