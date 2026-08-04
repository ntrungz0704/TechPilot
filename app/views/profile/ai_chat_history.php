<?php include ROOT_PATH . '/app/views/layouts/header.php'; ?>

<main class="container section" id="main-content" style="margin-top: 40px; min-height: 65vh;">
    <div style="display: flex; gap: 30px; flex-wrap: wrap;">
        <!-- Left Sidebar Menu -->
        <aside style="width: 250px; background-color: var(--bg-white); border: 1px solid var(--border); border-radius: 12px; padding: 20px; box-shadow: var(--shadow-card); align-self: flex-start;">
            <h3 style="font-weight: 700; margin-bottom: 20px; font-size: 16px;"><i class="fa-solid fa-user-gear" style="margin-right: 8px; color: var(--primary);"></i> Quản lý tài khoản</h3>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px; font-size: 14.5px;">
                <li><a href="<?= url('profile') ?>" style="text-decoration: none; color: var(--text-secondary);"><i class="fa-solid fa-user" style="width: 20px;"></i> Hồ sơ cá nhân</a></li>
                <li><a href="<?= url('profile/orders') ?>" style="text-decoration: none; color: var(--text-secondary);"><i class="fa-solid fa-box-open" style="width: 20px;"></i> Đơn hàng của tôi</a></li>
                <li><a href="<?= url('profile/wishlist') ?>" style="text-decoration: none; color: var(--text-secondary);"><i class="fa-solid fa-heart" style="width: 20px;"></i> Sản phẩm yêu thích</a></li>
                <li><a href="<?= url('profile/ai-chat-history') ?>" style="text-decoration: none; color: var(--primary); font-weight: 700;"><i class="fa-solid fa-robot" style="width: 20px;"></i> Lịch sử trò chuyện AI</a></li>
                <li><a href="<?= url('profile/notifications') ?>" style="text-decoration: none; color: var(--text-secondary);"><i class="fa-solid fa-bell" style="width: 20px;"></i> Thông báo hệ thống</a></li>
                <?php if (($user['role'] ?? '') === 'admin'): ?>
                    <li><a href="<?= url('admin') ?>" style="text-decoration: none; color: #0B63E5; font-weight: 700;"><i class="fa-solid fa-user-shield" style="width: 20px;"></i> Trang quản lý Admin</a></li>
                <?php endif; ?>
                <li><a href="<?= url('auth/logout') ?>" style="text-decoration: none; color: #EF4444;" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất?');"><i class="fa-solid fa-right-from-bracket" style="width: 20px;"></i> Đăng xuất</a></li>
            </ul>
        </aside>

        <!-- Right Content Area -->
        <div style="flex: 1; min-width: 300px; display: flex; flex-direction: column; gap: 24px;">
            <div style="background-color: var(--bg-white); border: 1px solid var(--border); border-radius: 12px; padding: 24px; box-shadow: var(--shadow-card);">
                <div style="border-bottom: 1px solid var(--border); padding-bottom: 14px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="font-size: 18px; font-weight: 700; color: var(--text-primary); margin: 0;">
                        <i class="fa-solid fa-robot" style="margin-right: 8px; color: var(--primary);"></i> Lịch sử trò chuyện AI theo sản phẩm
                    </h3>
                    <span style="font-size: 13px; color: var(--text-secondary); background: #F1F5F9; padding: 4px 12px; border-radius: 20px;">
                        <?= count($sessions) ?> sản phẩm đã tư vấn
                    </span>
                </div>

                <?php if (empty($sessions)): ?>
                    <div style="text-align: center; padding: 50px 20px; color: var(--text-secondary);">
                        <i class="fa-solid fa-comments" style="font-size: 48px; color: #CBD5E1; margin-bottom: 16px;"></i>
                        <h4 style="font-size: 16px; font-weight: 600; margin-bottom: 8px; color: var(--text-primary);">Chưa có lịch sử trò chuyện AI</h4>
                        <p style="font-size: 14px; max-width: 450px; margin: 0 auto 20px auto;">
                            Khi bạn đặt câu hỏi cho Trợ lý AI trên các trang chi tiết sản phẩm, toàn bộ cuộc trò chuyện sẽ được lưu tại đây.
                        </p>
                        <a href="<?= url('products') ?>" class="btn" style="padding: 10px 20px; font-size: 14px; text-decoration: none;">
                            Khám phá sản phẩm ngay
                        </a>
                    </div>
                <?php else: ?>
                    <div style="display: grid; grid-template-columns: 320px 1fr; gap: 20px; min-height: 480px;">
                        
                        <!-- Cột trái: Danh sách sản phẩm có chat -->
                        <div style="border-right: 1px solid var(--border); padding-right: 15px; display: flex; flex-direction: column; gap: 10px; max-height: 520px; overflow-y: auto;">
                            <?php foreach ($sessions as $s): ?>
                                <?php 
                                    $isSelected = ($selectedProduct && (int)$selectedProduct['id'] === (int)$s['product_id']);
                                    $imgUrl = !empty($s['product_image']) ? url(ltrim($s['product_image'], '/')) : url('assets/images/no-image.png');
                                ?>
                                <a href="<?= url('profile/ai-chat-history?product_id=' . $s['product_id']) ?>" 
                                   style="display: flex; gap: 12px; padding: 12px; border-radius: 10px; border: 1px solid <?= $isSelected ? 'var(--primary)' : 'var(--border)' ?>; background-color: <?= $isSelected ? '#EFF6FF' : '#FAFAFA' ?>; text-decoration: none; color: inherit; transition: all 0.2s ease;">
                                    <img src="<?= e($imgUrl) ?>" alt="<?= e($s['product_name']) ?>" style="width: 50px; height: 50px; object-fit: contain; border-radius: 6px; background: #FFF; padding: 2px;">
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="font-size: 13.5px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--text-primary);">
                                            <?= e($s['product_name']) ?>
                                        </div>
                                        <div style="font-size: 12px; color: var(--text-secondary); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?= ($s['last_message_role'] === 'user' ? 'Bạn: ' : 'AI: ') . e($s['last_message']) ?>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 6px; font-size: 11px; color: #94A3B8;">
                                            <span><?= date('d/m H:i', strtotime($s['last_chat_at'])) ?></span>
                                            <span style="background: #E2E8F0; color: #475569; padding: 1px 6px; border-radius: 10px; font-weight: 600;"><?= $s['total_messages'] ?> tin</span>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>

                        <!-- Cột phải: Xem full hội thoại sản phẩm được chọn -->
                        <div style="display: flex; flex-direction: column; height: 520px;">
                            <?php if ($selectedProduct): ?>
                                <!-- Product Card Header -->
                                <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 12px; border-bottom: 1px solid var(--border); margin-bottom: 12px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <?php $pImg = !empty($selectedProduct['image']) ? url(ltrim($selectedProduct['image'], '/')) : url('assets/images/no-image.png'); ?>
                                        <img src="<?= e($pImg) ?>" alt="<?= e($selectedProduct['name']) ?>" style="width: 42px; height: 42px; object-fit: contain;">
                                        <div>
                                            <div style="font-size: 14.5px; font-weight: 700; color: var(--text-primary);"><?= e($selectedProduct['name']) ?></div>
                                            <div style="font-size: 13px; color: var(--primary); font-weight: 600;"><?= formatPrice((float)$selectedProduct['price']) ?></div>
                                        </div>
                                    </div>
                                    <a href="<?= url('product/detail/' . ($selectedProduct['slug'] ?? $selectedProduct['id'])) ?>" class="btn btn--outline" style="padding: 6px 14px; font-size: 12.5px; text-decoration: none;">
                                        Xem sản phẩm <i class="fa-solid fa-arrow-up-right-from-square" style="margin-left: 4px;"></i>
                                    </a>
                                </div>

                                <!-- Messages Box -->
                                <div id="profileAiChatMessages" style="flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 14px; padding-right: 8px; font-family: inherit;">
                                    <?php foreach ($selectedHistory as $h): ?>
                                        <?php if ($h['role'] === 'user'): ?>
                                            <div style="display: flex; gap: 10px; align-self: flex-end; flex-direction: row-reverse;">
                                                <div style="background-color: var(--primary); color: #FFFFFF; border-radius: 12px; padding: 10px 14px; font-size: 13px; max-width: 85%; line-height: 1.5;">
                                                    <?= nl2br(e($h['message'])) ?>
                                                    <div style="font-size: 10px; opacity: 0.8; text-align: right; margin-top: 4px;"><?= date('H:i, d/m/Y', strtotime($h['created_at'])) ?></div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div style="display: flex; gap: 10px; align-self: flex-start;">
                                                <div style="width: 30px; height: 30px; border-radius: 50%; background-color: var(--primary); display: flex; align-items: center; justify-content: center; color: #FFFFFF; font-size: 12px; overflow: hidden; flex-shrink: 0;">
                                                    <img src="<?= url('assets/images/chatbot-avatar.png') ?>" alt="AI" style="width:100%; height:100%; object-fit:cover;">
                                                </div>
                                                <div style="background-color: #F8FAFC; border: 1px solid var(--border); border-radius: 12px; padding: 10px 14px; font-size: 13px; max-width: 85%; line-height: 1.5; color: var(--text-primary);">
                                                    <div><?= nl2br(e($h['message'])) ?></div>
                                                    <div style="font-size: 10px; color: #94A3B8; margin-top: 4px;"><?= date('H:i, d/m/Y', strtotime($h['created_at'])) ?></div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: var(--text-secondary); font-size: 14px;">
                                    Vui lòng chọn một sản phẩm ở danh sách bên trái để xem cuộc trò chuyện.
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const msgContainer = document.getElementById('profileAiChatMessages');
    if (msgContainer) {
        msgContainer.scrollTop = msgContainer.scrollHeight;
    }
});
</script>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
