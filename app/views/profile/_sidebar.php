<?php
/**
 * Profile Sidebar Partial
 * $activeMenu: 'profile' | 'addresses' | 'orders' | 'wishlist' | 'ai-chat-history' | 'notifications'
 */
$activeMenu = $activeMenu ?? 'profile';
$currentUser = currentUser();
?>
<aside style="width: 250px; background-color: var(--bg-white); border: 1px solid var(--border); border-radius: 12px; padding: 20px; box-shadow: var(--shadow-card); align-self: flex-start; flex-shrink: 0;">
    <h3 style="font-weight: 700; margin-bottom: 20px; font-size: 16px;">
        <i class="fa-solid fa-user-gear" style="margin-right: 8px; color: var(--primary);"></i> Quản lý tài khoản
    </h3>
    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px; font-size: 14.5px;">
        <li>
            <a href="<?= url('profile') ?>" style="text-decoration: none; color: <?= $activeMenu === 'profile' ? 'var(--primary)' : 'var(--text-secondary)' ?>; font-weight: <?= $activeMenu === 'profile' ? '700' : '500' ?>;">
                <i class="fa-solid fa-user" style="width: 20px;"></i> Hồ sơ cá nhân
            </a>
        </li>
        <li>
            <a href="<?= url('profile/addresses') ?>" style="text-decoration: none; color: <?= $activeMenu === 'addresses' ? 'var(--primary)' : 'var(--text-secondary)' ?>; font-weight: <?= $activeMenu === 'addresses' ? '700' : '500' ?>;">
                <i class="fa-solid fa-location-dot" style="width: 20px;"></i> Quản lý địa chỉ
            </a>
        </li>
        <li>
            <a href="<?= url('profile/orders') ?>" style="text-decoration: none; color: <?= $activeMenu === 'orders' ? 'var(--primary)' : 'var(--text-secondary)' ?>; font-weight: <?= $activeMenu === 'orders' ? '700' : '500' ?>;">
                <i class="fa-solid fa-box-open" style="width: 20px;"></i> Đơn hàng của tôi
            </a>
        </li>
        <li>
            <a href="<?= url('profile/wishlist') ?>" style="text-decoration: none; color: <?= $activeMenu === 'wishlist' ? 'var(--primary)' : 'var(--text-secondary)' ?>; font-weight: <?= $activeMenu === 'wishlist' ? '700' : '500' ?>;">
                <i class="fa-solid fa-heart" style="width: 20px;"></i> Sản phẩm yêu thích
            </a>
        </li>
        <li>
            <a href="<?= url('profile/ai-chat-history') ?>" style="text-decoration: none; color: <?= $activeMenu === 'ai-chat-history' ? 'var(--primary)' : 'var(--text-secondary)' ?>; font-weight: <?= $activeMenu === 'ai-chat-history' ? '700' : '500' ?>;">
                <i class="fa-solid fa-robot" style="width: 20px;"></i> Lịch sử trò chuyện AI
            </a>
        </li>
        <li>
            <a href="<?= url('profile/notifications') ?>" style="text-decoration: none; color: <?= $activeMenu === 'notifications' ? 'var(--primary)' : 'var(--text-secondary)' ?>; font-weight: <?= $activeMenu === 'notifications' ? '700' : '500' ?>;">
                <i class="fa-solid fa-bell" style="width: 20px;"></i> Thông báo hệ thống
            </a>
        </li>
        <?php if (!empty($currentUser['role']) && $currentUser['role'] === 'admin'): ?>
            <li>
                <a href="<?= url('admin') ?>" style="text-decoration: none; color: #0B63E5; font-weight: 700;">
                    <i class="fa-solid fa-user-shield" style="width: 20px;"></i> Trang quản lý Admin
                </a>
            </li>
        <?php endif; ?>
        <li>
            <a href="<?= url('auth/logout') ?>" style="text-decoration: none; color: #EF4444;" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất?');">
                <i class="fa-solid fa-right-from-bracket" style="width: 20px;"></i> Đăng xuất
            </a>
        </li>
    </ul>
</aside>
