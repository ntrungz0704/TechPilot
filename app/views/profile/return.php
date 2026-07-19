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
                <h2 style="margin: 0;">Đăng ký Đổi trả / Hoàn tiền</h2>
                <a href="<?= url('profile/orders') ?>" style="text-decoration: none; font-size: 14.5px; color: var(--primary); font-weight: 600;"><i class="fa-solid fa-arrow-left"></i> Quay lại đơn hàng</a>
            </div>

            <?php if (isset($flashes['error'])): ?>
                <div class="alert alert--danger" style="margin-bottom: 20px; padding: 12px; background-color: #FDE8E8; color: #9B1C1C; border-radius: 8px;">
                    <?= e($flashes['error']) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= url('profile/submit_return') ?>" style="background-color: var(--bg-white); border: 1px solid var(--border); border-radius: 12px; padding: 25px; box-shadow: var(--shadow-card); display: flex; flex-direction: column; gap: 20px;">
                <?= csrf_field() ?>
                <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">

                <div>
                    <h3 style="font-weight: 700; margin: 0 0 15px 0; font-size: 15.5px;">1. Chọn sản phẩm cần đổi trả</h3>
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <?php foreach ($order['items'] as $item): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; gap: 15px; border-bottom: 1px solid var(--border); padding-bottom: 15px; flex-wrap: wrap;">
                                <div style="display: flex; gap: 15px; align-items: center; max-width: 60%;">
                                    <img src="<?= url('assets/images/' . e($item['image'])) ?>" alt="<?= e($item['product_name']) ?>" style="width: 50px; height: 50px; object-fit: contain; border: 1px solid var(--border); border-radius: 6px; padding: 4px; background: #FFF;">
                                    <div>
                                        <h4 style="margin: 0; font-size: 14px; font-weight: 600;"><?= e($item['product_name']) ?></h4>
                                        <span style="font-size: 12.5px; color: var(--text-secondary); display: block; margin-top: 2px;">Đã mua: <?= (int)$item['quantity'] ?> sản phẩm</span>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                                    <!-- Nhập số lượng cần trả -->
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <label style="font-size: 12px; color: var(--text-secondary);">Số lượng trả:</label>
                                        <input type="number" name="quantity[<?= (int)$item['id'] ?>]" min="0" max="<?= (int)$item['quantity'] ?>" value="0" style="width: 70px; padding: 6px; border: 1px solid var(--border); border-radius: 6px; text-align: center;">
                                    </div>
                                    <!-- Chọn giải pháp -->
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <label style="font-size: 12px; color: var(--text-secondary);">Yêu cầu:</label>
                                        <select name="resolution[<?= (int)$item['id'] ?>]" style="padding: 6px; border: 1px solid var(--border); border-radius: 6px; font-size: 13.5px; font-weight: 500;">
                                            <option value="refund">Hoàn tiền</option>
                                            <option value="replace">Đổi hàng mới</option>
                                            <option value="repair">Sửa chữa bảo hành</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <h3 style="font-weight: 700; margin: 20px 0 15px 0; font-size: 15.5px;">2. Lý do đổi trả hàng</h3>
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <label style="font-size: 14px; font-weight: 600; color: var(--text-secondary);">Lý do chính <span style="color: #EF4444;">*</span></label>
                            <select name="reason" required style="padding: 10px; border: 1px solid var(--border); border-radius: 8px; font-size: 14.5px;">
                                <option value="">-- Chọn lý do chính --</option>
                                <option value="Hàng lỗi kỹ thuật">Sản phẩm bị lỗi kỹ thuật, không lên nguồn/không hoạt động</option>
                                <option value="Bể vỡ, móp méo">Sản phẩm bị trầy xước, móp méo, bể vỡ khi nhận hàng</option>
                                <option value="Giao sai mẫu">Giao sai sản phẩm, sai màu sắc hoặc sai thông số cấu hình</option>
                                <option value="Thiếu phụ kiện">Sản phẩm bị thiếu phụ kiện đi kèm (sách HDSD, cáp sạc, adapter)</option>
                                <option value="Khác">Lý do cá nhân khác</option>
                            </select>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <label style="font-size: 14px; font-weight: 600; color: var(--text-secondary);">Mô tả chi tiết tình trạng lỗi</label>
                            <textarea name="description" placeholder="Vui lòng mô tả chi tiết lỗi sản phẩm gặp phải..." style="padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 14.5px; min-height: 100px; resize: vertical;"></textarea>
                        </div>
                    </div>
                </div>

                <div style="border-top: 1px solid var(--border); padding-top: 20px; display: flex; justify-content: flex-end; gap: 15px;">
                    <a href="<?= url('profile/orders') ?>" class="btn btn--light" style="padding: 12px 24px;">Hủy bỏ</a>
                    <button type="submit" class="btn" style="padding: 12px 30px; font-weight: 700; background-color: #F59E0B; border-color: #D97706;">Gửi Yêu Cầu</button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
