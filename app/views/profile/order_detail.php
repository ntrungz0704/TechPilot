<?php include ROOT_PATH . '/app/views/layouts/header.php'; ?>

<main class="container section" id="main-content" style="margin-top: 40px; min-height: 60vh;">
    <div style="display: flex; gap: 30px; flex-wrap: wrap;">
        <!-- Left Sidebar Menu -->
        <aside style="width: 250px; background-color: var(--bg-white); border: 1px solid var(--border); border-radius: 12px; padding: 20px; box-shadow: var(--shadow-card); align-self: flex-start;">
            <h3 style="font-weight: 700; margin-bottom: 20px; font-size: 16px;"><i class="fa-solid fa-user-gear" style="margin-right: 8px; color: var(--primary);"></i> Quản lý tài khoản</h3>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px; font-size: 14.5px;">
                <li><a href="<?= url('profile/orders') ?>" style="text-decoration: none; color: var(--primary); font-weight: 700;"><i class="fa-solid fa-box-open" style="width: 20px;"></i> Đơn hàng của tôi</a></li>
                <li><a href="<?= url('profile/notifications') ?>" style="text-decoration: none; color: var(--text-secondary);"><i class="fa-solid fa-bell" style="width: 20px;"></i> Thông báo hệ thống</a></li>
                <li><a href="<?= url('wishlist') ?>" style="text-decoration: none; color: var(--text-secondary);"><i class="fa-solid fa-heart" style="width: 20px;"></i> Sản phẩm yêu thích</a></li>
            </ul>
        </aside>

        <!-- Right Content Area -->
        <div style="flex: 1; min-width: 300px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                <h2 style="margin: 0;">Chi tiết đơn hàng #<?= e($order['order_code']) ?></h2>
                <a href="<?= url('profile/orders') ?>" style="text-decoration: none; font-size: 14.5px; color: var(--primary); font-weight: 600;"><i class="fa-solid fa-arrow-left"></i> Quay lại lịch sử</a>
            </div>

            <div style="background-color: var(--bg-white); border: 1px solid var(--border); border-radius: 12px; padding: 20px; box-shadow: var(--shadow-card); display: flex; flex-direction: column; gap: 20px; margin-bottom: 30px;">
                <!-- Thông tin giao nhận -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
                    <div>
                        <h4 style="margin: 0 0 10px 0; color: var(--text-secondary); font-size: 13px; text-transform: uppercase;">Thông tin người nhận</h4>
                        <p style="margin: 0; font-weight: 600; font-size: 15px;"><?= e($order['customer_name']) ?></p>
                        <p style="margin: 4px 0 0 0; font-size: 14px; color: var(--text-secondary);"><?= e($order['phone']) ?></p>
                        <p style="margin: 4px 0 0 0; font-size: 14px; color: var(--text-secondary);"><?= e($order['email']) ?></p>
                    </div>
                    <div>
                        <h4 style="margin: 0 0 10px 0; color: var(--text-secondary); font-size: 13px; text-transform: uppercase;">Địa chỉ giao hàng</h4>
                        <p style="margin: 0; font-size: 14.5px; line-height: 1.5;"><?= e($order['address']) ?></p>
                    </div>
                    <div>
                        <h4 style="margin: 0 0 10px 0; color: var(--text-secondary); font-size: 13px; text-transform: uppercase;">Thanh toán & Vận chuyển</h4>
                        <p style="margin: 0; font-size: 14px;">Hình thức: <strong><?= e($order['payment_method']) ?></strong></p>
                        <p style="margin: 4px 0 0 0; font-size: 14px;">Thanh toán: <strong style="text-transform: uppercase;"><?= e($order['payment_status']) ?></strong></p>
                        <p style="margin: 4px 0 0 0; font-size: 14px;">Vận chuyển: <strong style="text-transform: uppercase;"><?= e($order['fulfillment_status']) ?></strong></p>
                    </div>
                </div>

                <?php if (!empty($order['note'])): ?>
                    <div style="border-top: 1px solid var(--border); padding-top: 15px;">
                        <h4 style="margin: 0 0 5px 0; color: var(--text-secondary); font-size: 13px; text-transform: uppercase;">Ghi chú</h4>
                        <p style="margin: 0; font-style: italic; font-size: 14px;"><?= e($order['note']) ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Danh sách sản phẩm -->
            <div style="background-color: var(--bg-white); border: 1px solid var(--border); border-radius: 12px; padding: 20px; box-shadow: var(--shadow-card);">
                <h3 style="font-weight: 700; margin: 0 0 20px 0; font-size: 16px;">Sản phẩm trong đơn hàng</h3>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <?php foreach ($order['items'] as $item): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 15px; border-bottom: 1px solid var(--border); padding-bottom: 15px;">
                            <div style="display: flex; gap: 15px; align-items: center;">
                                <img src="<?= url('assets/images/' . e($item['image'])) ?>" alt="<?= e($item['product_name']) ?>" style="width: 60px; height: 60px; object-fit: contain; border: 1px solid var(--border); border-radius: 8px; padding: 4px; background: #FFF;">
                                <div>
                                    <h4 style="margin: 0; font-size: 14.5px; font-weight: 600;"><?= e($item['product_name']) ?></h4>
                                    <?php if (!empty($item['variant_name'])): ?>
                                        <span style="font-size: 12px; color: var(--text-secondary); display: block; margin-top: 2px;">Phân loại: <?= e($item['variant_name']) ?></span>
                                    <?php endif; ?>
                                    <span style="font-size: 13.5px; color: var(--text-secondary); display: block; margin-top: 4px;">Số lượng: <?= (int)$item['quantity'] ?></span>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <strong style="font-size: 15px; color: var(--primary);"><?= number_format($item['price'], 0, ',', '.') ?>đ</strong>
                                <span style="font-size: 12.5px; color: var(--text-secondary); display: block; margin-top: 4px;">Tổng: <?= number_format($item['line_total'], 0, ',', '.') ?>đ</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Tóm tắt chi phí -->
                <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 10px; align-items: flex-end; font-size: 14.5px;">
                    <div style="display: flex; width: 280px; justify-content: space-between;">
                        <span style="color: var(--text-secondary);">Tạm tính:</span>
                        <strong><?= number_format($order['subtotal'], 0, ',', '.') ?>đ</strong>
                    </div>
                    <?php if ((float)$order['discount_amount'] > 0): ?>
                        <div style="display: flex; width: 280px; justify-content: space-between; color: #DC2626;">
                            <span>Giảm giá:</span>
                            <strong>-<?= number_format($order['discount_amount'], 0, ',', '.') ?>đ</strong>
                        </div>
                    <?php endif; ?>
                    <div style="display: flex; width: 280px; justify-content: space-between;">
                        <span style="color: var(--text-secondary);">Phí giao hàng:</span>
                        <strong><?= number_format($order['shipping_fee'], 0, ',', '.') ?>đ</strong>
                    </div>
                    <div style="display: flex; width: 280px; justify-content: space-between; border-top: 1px solid var(--border); padding-top: 10px; font-size: 16px;">
                        <span style="font-weight: 700;">Tổng thanh toán:</span>
                        <strong style="color: var(--primary); font-size: 18px; font-weight: 800;"><?= number_format($order['total_amount'], 0, ',', '.') ?>đ</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
