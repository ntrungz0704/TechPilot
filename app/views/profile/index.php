<?php include ROOT_PATH . '/app/views/layouts/header.php'; ?>

<main class="container section" id="main-content" style="margin-top: 40px; min-height: 60vh;">
    <div style="display: flex; gap: 30px; flex-wrap: wrap;">
        <!-- Left Sidebar Menu -->
        <aside style="width: 250px; background-color: var(--bg-white); border: 1px solid var(--border); border-radius: 12px; padding: 20px; box-shadow: var(--shadow-card); align-self: flex-start;">
            <h3 style="font-weight: 700; margin-bottom: 20px; font-size: 16px;"><i class="fa-solid fa-user-gear" style="margin-right: 8px; color: var(--primary);"></i> Quản lý tài khoản</h3>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px; font-size: 14.5px;">
                <li><a href="<?= url('profile') ?>" style="text-decoration: none; color: var(--primary); font-weight: 700;"><i class="fa-solid fa-user" style="width: 20px;"></i> Hồ sơ cá nhân</a></li>
                <li><a href="<?= url('profile/orders') ?>" style="text-decoration: none; color: var(--text-secondary);"><i class="fa-solid fa-box-open" style="width: 20px;"></i> Đơn hàng của tôi</a></li>
                <li><a href="<?= url('profile/wishlist') ?>" style="text-decoration: none; color: var(--text-secondary);"><i class="fa-solid fa-heart" style="width: 20px;"></i> Sản phẩm yêu thích</a></li>
                <li><a href="<?= url('profile/notifications') ?>" style="text-decoration: none; color: var(--text-secondary);"><i class="fa-solid fa-bell" style="width: 20px;"></i> Thông báo hệ thống</a></li>
                <li><a href="<?= url('auth/logout') ?>" style="text-decoration: none; color: #EF4444;" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất?');"><i class="fa-solid fa-right-from-bracket" style="width: 20px;"></i> Đăng xuất</a></li>
            </ul>
        </aside>

        <!-- Right Content Area -->
        <div style="flex: 1; min-width: 300px; display: flex; flex-direction: column; gap: 24px;">
            
            <?php if (isset($flashes['success'])): ?>
                <div class="alert alert--success" style="padding: 12px; background-color: #DEF7EC; color: #03543F; border-radius: 8px;">
                    <?= e($flashes['success']) ?>
                </div>
            <?php endif; ?>
            <?php if (isset($flashes['error'])): ?>
                <div class="alert alert--danger" style="padding: 12px; background-color: #FDE8E8; color: #9B1C1C; border-radius: 8px;">
                    <?= e($flashes['error']) ?>
                </div>
            <?php endif; ?>

            <!-- Hộp 1: Thông tin cá nhân -->
            <div style="background-color: var(--bg-white); border: 1px solid var(--border); border-radius: 12px; padding: 24px; box-shadow: var(--shadow-card);">
                <div style="border-bottom: 1px solid var(--border); padding-bottom: 12px; margin-bottom: 20px;">
                    <h3 style="font-size: 18px; font-weight: 700; color: var(--text-primary); margin: 0;"><i class="fa-regular fa-address-card" style="margin-right: 8px; color: var(--primary);"></i> Thông tin cá nhân</h3>
                </div>
                
                <form method="post" action="<?= url('profile') ?>" style="display: flex; flex-direction: column; gap: 16px;">
                    <?= csrf_field() ?>
                    
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-size: 13.5px; font-weight: 600; color: var(--text-secondary);">Họ và tên</label>
                        <input type="text" name="full_name" value="<?= e($user['full_name'] ?? '') ?>" required style="padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; font-size: 14.5px; outline: none;">
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-size: 13.5px; font-weight: 600; color: var(--text-secondary);">Địa chỉ Email (Không được sửa)</label>
                        <input type="email" value="<?= e($user['email'] ?? '') ?>" disabled style="padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; font-size: 14.5px; background-color: #F1F5F9; color: var(--text-secondary); cursor: not-allowed;">
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-size: 13.5px; font-weight: 600; color: var(--text-secondary);">Số điện thoại</label>
                        <input type="text" name="phone" value="<?= e($user['phone'] ?? '') ?>" required style="padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; font-size: 14.5px; outline: none;">
                    </div>

                    <button type="submit" class="btn" style="align-self: flex-start; padding: 10px 24px; font-size: 14px; font-weight: 600;">Lưu thay đổi</button>
                </form>
            </div>

            <!-- Hộp 2: Đổi mật khẩu -->
            <div style="background-color: var(--bg-white); border: 1px solid var(--border); border-radius: 12px; padding: 24px; box-shadow: var(--shadow-card);">
                <div style="border-bottom: 1px solid var(--border); padding-bottom: 12px; margin-bottom: 20px;">
                    <h3 style="font-size: 18px; font-weight: 700; color: var(--text-primary); margin: 0;"><i class="fa-solid fa-key" style="margin-right: 8px; color: var(--primary);"></i> Đổi mật khẩu</h3>
                </div>

                <form method="post" action="<?= url('profile/change_password') ?>" style="display: flex; flex-direction: column; gap: 16px;">
                    <?= csrf_field() ?>

                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-size: 13.5px; font-weight: 600; color: var(--text-secondary);">Mật khẩu hiện tại</label>
                        <input type="password" name="old_password" required style="padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; font-size: 14.5px; outline: none;">
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-size: 13.5px; font-weight: 600; color: var(--text-secondary);">Mật khẩu mới</label>
                        <input type="password" name="new_password" required style="padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; font-size: 14.5px; outline: none;">
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-size: 13.5px; font-weight: 600; color: var(--text-secondary);">Xác nhận mật khẩu mới</label>
                        <input type="password" name="confirm_password" required style="padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; font-size: 14.5px; outline: none;">
                    </div>

                    <button type="submit" class="btn" style="align-self: flex-start; padding: 10px 24px; font-size: 14px; font-weight: 600; background-color: var(--primary);">Cập nhật mật khẩu</button>
                </form>
            </div>

        </div>
    </div>
</main>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
