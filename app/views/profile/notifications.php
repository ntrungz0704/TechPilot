<?php include ROOT_PATH . '/app/views/layouts/header.php'; ?>

<main class="container section" id="main-content" style="margin-top: 40px; min-height: 60vh;">
    <div style="display: flex; gap: 30px; flex-wrap: wrap;">
        <!-- Left Sidebar Menu -->
        <aside style="width: 250px; background-color: var(--bg-white); border: 1px solid var(--border); border-radius: 12px; padding: 20px; box-shadow: var(--shadow-card); align-self: flex-start;">
            <h3 style="font-weight: 700; margin-bottom: 20px; font-size: 16px;"><i class="fa-solid fa-user-gear" style="margin-right: 8px; color: var(--primary);"></i> Quản lý tài khoản</h3>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px; font-size: 14.5px;">
                <li><a href="<?= url('profile/orders') ?>" style="text-decoration: none; color: var(--text-secondary);"><i class="fa-solid fa-box-open" style="width: 20px;"></i> Đơn hàng của tôi</a></li>
                <li><a href="<?= url('profile/notifications') ?>" style="text-decoration: none; color: var(--primary); font-weight: 700;"><i class="fa-solid fa-bell" style="width: 20px;"></i> Thông báo hệ thống</a></li>
                <li><a href="<?= url('wishlist') ?>" style="text-decoration: none; color: var(--text-secondary);"><i class="fa-solid fa-heart" style="width: 20px;"></i> Sản phẩm yêu thích</a></li>
            </ul>
        </aside>

        <!-- Right Content Area -->
        <div style="flex: 1; min-width: 300px;">
            <div class="section__head" style="margin-bottom: 20px;">
                <h2>Thông báo hệ thống</h2>
            </div>

            <?php if (empty($notifications)): ?>
                <div style="text-align: center; padding: 40px 20px; background-color: var(--bg-white); border: 1px solid var(--border); border-radius: 12px;">
                    <i class="fa-regular fa-bell-slash" style="font-size: 48px; color: var(--text-secondary); margin-bottom: 15px; display: block;"></i>
                    <p style="color: var(--text-secondary);">Bạn không có thông báo nào.</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <?php foreach ($notifications as $n): ?>
                        <div style="border: 1px solid var(--border); border-radius: 12px; padding: 15px 20px; display: flex; gap: 15px; align-items: flex-start; transition: background-color 0.2s;
                            <?= $n['is_read'] ? 'background-color: var(--bg-white);' : 'background-color: #EFF6FF; border-color: #BFDBFE;' ?>">
                            <div style="font-size: 20px; color: var(--primary); margin-top: 2px;">
                                <i class="fa-solid fa-circle-info"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 10px; margin-bottom: 5px;">
                                    <strong style="font-size: 14.5px;"><?= e($n['title']) ?></strong>
                                    <span style="font-size: 11.5px; color: var(--text-secondary);"><?= date('d/m/Y H:i', strtotime($n['created_at'])) ?></span>
                                </div>
                                <p style="margin: 0; font-size: 13.5px; color: #4B5563; line-height: 1.5;"><?= e($n['content']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
