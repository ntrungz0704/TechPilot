</main>

    <!-- 14. Bottom Features -->
    <section class="container" style="margin-top: 40px;">
        <div class="bottom-features">
            <div class="bottom-feature-item">
                <i class="fa-solid fa-map-location-dot"></i>
                <h5>Hệ thống 50+ cửa hàng</h5>
                <p>Trải nghiệm thực tế sản phẩm công nghệ cao cấp trên toàn quốc</p>
            </div>
            <div class="bottom-feature-item">
                <i class="fa-solid fa-money-bill-wave"></i>
                <h5>Thanh toán khi nhận hàng</h5>
                <p>Hỗ trợ thanh toán COD (tiền mặt) khi nhận hàng trên toàn quốc</p>
            </div>
            <div class="bottom-feature-item">
                <i class="fa-solid fa-user-shield"></i>
                <h5>Bảo mật tuyệt đối</h5>
                <p>Cam kết bảo mật thông tin khách hàng và giao dịch an toàn</p>
            </div>
            <div class="bottom-feature-item">
                <i class="fa-solid fa-gift"></i>
                <h5>Đặc quyền thành viên</h5>
                <p>Tích điểm nâng hạng Member, nhận ưu đãi giảm giá độc quyền</p>
            </div>
        </div>
    </section>

    <!-- 15. Footer -->
    <footer class="site-footer">
        <div class="container footer-grid">
            <div class="footer-col footer-brand">
                <a href="<?= url('/') ?>" class="logo" style="display: flex; align-items: center; gap: 12px; text-decoration: none; margin-bottom: 20px;">
                    <img src="<?= url('assets/images/logo.png') ?>" alt="TechPilot Logo" style="height: 40px; object-fit: contain; display: block;">
                    <div class="logo-brand-info">
                        <span class="logo-brand-title">Tech<span>Pilot</span></span>
                        <span class="logo-brand-tagline" style="color: rgba(255,255,255,0.65);">Technology • Trust • Future</span>
                    </div>
                </a>
                <p>TechPilot - Chuỗi siêu thị máy tính, laptop và gaming gear hàng đầu Việt Nam. Cam kết chất lượng, bảo hành vượt trội, giá tốt nhất.</p>
                <div class="footer-social">
                    <a href="https://facebook.com" target="_blank" rel="noopener"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="https://youtube.com" target="_blank" rel="noopener"><i class="fa-brands fa-youtube"></i></a>
                    <a href="https://tiktok.com" target="_blank" rel="noopener"><i class="fa-brands fa-tiktok"></i></a>
                    <a href="https://instagram.com" target="_blank" rel="noopener"><i class="fa-brands fa-instagram"></i></a>
                </div>
            </div>
            
            <div class="footer-col">
                <h4>Về chúng tôi</h4>
                <a href="<?= url('post') ?>">Giới thiệu TechPilot</a>
                <a href="<?= url('post') ?>">Tuyển dụng nhân viên</a>
                <a href="<?= url('post') ?>">Liên hệ hợp tác</a>
                <a href="<?= url('post') ?>">Hệ thống cửa hàng</a>
                <a href="<?= url('post') ?>">Chính sách bảo mật</a>
            </div>
            
            <div class="footer-col">
                <h4>Hỗ trợ khách hàng</h4>
                <a href="<?= url('post') ?>">Hướng dẫn mua hàng online</a>
                <a href="<?= url('profile') ?>">Chính sách đổi trả sản phẩm</a>
                <a href="<?= url('post') ?>">Chính sách bảo hành sửa chữa</a>
                <a href="<?= url('profile/orders') ?>">Tra cứu hóa đơn điện tử</a>
                <a href="<?= url('post') ?>">Gửi yêu cầu hỗ trợ kỹ thuật</a>
            </div>
            
            <div class="footer-col">
                <h4>Đăng ký nhận ưu đãi</h4>
                <p style="font-size: 13px; color: #94A3B8; margin-bottom: 12px;">Đăng ký để nhận những thông báo khuyến mãi công nghệ sớm nhất.</p>
                <form class="newsletter-form" onsubmit="return false;">
                    <input type="email" placeholder="Email của bạn..." required>
                    <button type="submit">Đăng ký</button>
                </form>
                <div class="payment-icons" style="margin-top: 24px;">
                    <i class="fa-solid fa-money-bill-wave" title="Tiền mặt COD"></i>
                    <i class="fa-solid fa-truck-fast" title="Giao hàng tận nơi"></i>
                    <i class="fa-solid fa-shield-check" title="Bảo đảm chất lượng"></i>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="container footer-bottom__inner">
                <span>© <?= date('Y') ?> TechPilot. Bản quyền thuộc về đội ngũ phát triển TechPilot.</span>
                <span>Kết nối công nghệ – Kiến tạo tương lai</span>
            </div>
        </div>
    </footer>

    <!-- Adaptive Bottom Nav / Fixed Buy Bar for Mobile (Display: None on Desktop) -->
    <?php 
    $reqUri = $_SERVER['REQUEST_URI'] ?? '';
    $isProductDetail = (strpos($reqUri, '/product/detail/') !== false);
    if ($isProductDetail && !empty($product)): 
    ?>
        <div class="mobile-fixed-buy-bar">
            <div class="fixed-buy-bar__info">
                <img src="<?= e(productImageUrl($product['image'] ?? '', $product['category_slug'] ?? $product['name'] ?? '')) ?>" alt="thumb">
                <div class="fixed-buy-bar__txt">
                    <span class="fixed-buy-bar__name"><?= e($product['name']) ?></span>
                    <span class="fixed-buy-bar__price"><?= formatPrice($product['price']) ?></span>
                </div>
            </div>
            <button type="button" class="fixed-buy-bar__btn" onclick="buyNowSubmit()"><i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ</button>
        </div>
    <?php else: ?>
        <div class="mobile-bottom-nav">
            <a href="<?= url('/') ?>" class="mobile-bottom-nav__item">
                <i class="fa-solid fa-house"></i>
                <span>Trang chủ</span>
            </a>
            <button type="button" class="mobile-bottom-nav__item" id="mobileBottomNavCats" style="background: none; border: none; cursor: pointer; color: inherit;">
                <i class="fa-solid fa-list"></i>
                <span>Danh mục</span>
            </button>
            <button type="button" class="mobile-bottom-nav__item" id="mobileBottomNavSearch" style="background: none; border: none; cursor: pointer; color: inherit;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span>Tìm kiếm</span>
            </button>
            <a href="<?= url('wishlist') ?>" class="mobile-bottom-nav__item">
                <i class="fa-solid fa-heart"></i>
                <span>Yêu thích</span>
            </a>
            <a href="<?= url('cart') ?>" class="mobile-bottom-nav__item">
                <i class="fa-solid fa-cart-shopping"></i>
                <span>Giỏ hàng</span>
                <span class="cart-badge"><?= (int)cartCount() ?></span>
            </a>
        </div>
    <?php endif; ?>

    <script src="<?= url('assets/js/main.js?v=7.2') ?>"></script>
    <script src="<?= url('assets/js/category-mega-menu.js?v=2.7') ?>"></script>
    <?php foreach ($pageScripts ?? [] as $script): ?>
        <script src="<?= url($script) ?>"></script>
    <?php endforeach; ?>
    <script>
        // Xử lý menu hamburger và drawer menu trên Mobile
        const mainNav = document.querySelector('.main-nav');
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const mobileDrawerClose = document.getElementById('mobileDrawerClose');
        const bottomNavCats = document.getElementById('mobileBottomNavCats');

        function openMenu() {
            mainNav?.classList.add('is-active');
        }

        function closeMenu() {
            mainNav?.classList.remove('is-active');
        }

        mobileMenuToggle?.addEventListener('click', openMenu);
        mobileDrawerClose?.addEventListener('click', closeMenu);
        bottomNavCats?.addEventListener('click', openMenu);

        // Bấm "Tìm kiếm" ở bottom nav -> cuộn lên đầu & focus vào search bar di động
        document.getElementById('mobileBottomNavSearch')?.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
            setTimeout(function() {
                const searchInput = document.querySelector('.mobile-search-bar input');
                if (searchInput) {
                    searchInput.focus();
                }
            }, 300);
        });

        // Xử lý accordion của Footer trên Mobile
        document.querySelectorAll('.site-footer .footer-col h4').forEach(function(header) {
            header.addEventListener('click', function() {
                if (window.innerWidth <= 575) {
                    const parent = this.parentElement;
                    parent.classList.toggle('is-active');
                }
            });
        });
    </script>

    <script>
        /**
         * Xóa các parameter rỗng trước khi submit form search
         * URL sẽ clean: ?q=lap thay vì ?q=lap&cat=&brand=
         */
        function cleanSearchParams(form) {
            const emptyParams = ['q', 'cat', 'brand', 'min_price', 'max_price', 'stock', 'sort', 'page'];
            // Build URL từ action của form
            const url = new URL(form.action);
            const params = new URLSearchParams();

            // Duyệt qua từng field trong form
            for (const el of form.elements) {
                if (!el.name) continue;
                const val = el.value.trim();
                // Chỉ thêm vào URL nếu có giá trị
                if (val !== '' && val !== '0') {
                    params.set(el.name, val);
                }
            }

            // Reset page về 1 khi search mới
            params.delete('page');

            url.search = params.toString();
            window.location.href = url.toString();
            return false; // ngăn form submit mặc định
        }

        // Tự động áp dụng cleanSearchParams cho tất cả form search
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('form[action*="home/search"]').forEach(function(form) {
                if (!form.hasAttribute('onsubmit')) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        cleanSearchParams(this);
                    });
                }
            });
        });
    </script>

    <?php if (currentUser()): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tạo container chứa các thông báo Toast ở góc dưới bên phải
        const toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        toastContainer.style.cssText = 'position: fixed; bottom: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; max-width: 360px; pointer-events: none;';
        document.body.appendChild(toastContainer);

        // Định nghĩa CSS bổ sung cho Toast
        const styleEl = document.createElement('style');
        styleEl.innerHTML = `
            .toast-item {
                background-color: var(--bg-card, #FFFFFF);
                color: var(--text-primary, #0F172A);
                border: 1px solid var(--border, #E2E8F0);
                border-left: 4px solid var(--primary, #0A5BFF);
                border-radius: 12px;
                padding: 16px;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.08);
                display: flex;
                gap: 12px;
                align-items: flex-start;
                animation: toastSlideIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
                pointer-events: auto;
                transition: all 0.3s ease;
            }
            @keyframes toastSlideIn {
                from { transform: translateX(120%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            .toast-item.fade-out {
                transform: translateX(120%);
                opacity: 0;
            }
            .dark-mode .toast-item {
                background-color: #1E293B;
                border-color: #334155;
                color: #F8FAFC;
            }
        `;
        document.head.appendChild(styleEl);

        // Danh sách thông báo đã hiển thị Toast trong session này để tránh trùng lặp khi F5
        let toastedIds = [];
        try {
            const stored = sessionStorage.getItem('techpilot-toasted-ids');
            if (stored) toastedIds = JSON.parse(stored);
        } catch(e) {}

        function showToast(notif) {
            if (toastedIds.includes(notif.id)) return;
            
            toastedIds.push(notif.id);
            try {
                sessionStorage.setItem('techpilot-toasted-ids', JSON.stringify(toastedIds));
            } catch(e) {}

            const toast = document.createElement('div');
            toast.className = 'toast-item';
            toast.innerHTML = `
                <div style="font-size: 20px; color: var(--primary);"><i class="fa-solid fa-circle-info"></i></div>
                <div style="flex: 1; font-size: 13.5px;">
                    <strong style="display: block; margin-bottom: 4px; font-weight: 700;">${notif.title}</strong>
                    <span>${notif.content}</span>
                </div>
                <button type="button" class="toast-item__close" style="background:none; border:none; color:var(--text-secondary); cursor:pointer; font-size:14px; margin-left:8px;"><i class="fa-solid fa-xmark"></i></button>
            `;

            toast.querySelector('.toast-item__close').addEventListener('click', () => {
                toast.classList.add('fade-out');
                setTimeout(() => toast.remove(), 300);
            });

            toastContainer.appendChild(toast);

            // Tự động ẩn sau 7 giây
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.classList.add('fade-out');
                    setTimeout(() => toast.remove(), 300);
                }
            }, 7000);
        }

        function checkNotifications() {
            fetch('<?= url("api/notifications/unread") ?>')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Cập nhật số lượng trên chuông ở Header
                        const bellLink = document.querySelector('.header-actions__notifications');
                        if (bellLink) {
                            let badge = bellLink.querySelector('.notification-badge');
                            if (data.count > 0) {
                                if (!badge) {
                                    badge = document.createElement('span');
                                    badge.className = 'notification-badge';
                                    badge.style.cssText = 'position: absolute; top: 0; right: 0; background-color: #EF4444; color: #FFFFFF; font-size: 10px; font-weight: 700; min-width: 16px; height: 16px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 1.5px solid var(--bg-card); padding: 0 3px; transform: translate(30%, -30%);';
                                    bellLink.appendChild(badge);
                                }
                                badge.textContent = data.count;
                            } else {
                                if (badge) badge.remove();
                            }
                        }

                        // Hiển thị popup toast cho thông báo mới
                        if (data.notifications && data.notifications.length > 0) {
                            data.notifications.forEach(showToast);
                        }
                    }
                })
                .catch(err => console.error("Error polling notifications:", err));
        }

        function checkNotifications() {
            fetch('<?= url("api/notifications/unread") ?>')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Cập nhật số lượng trên chuông ở Header
                        const bellLink = document.querySelector('.header-actions__notifications');
                        if (bellLink) {
                            let badge = bellLink.querySelector('.notification-badge');
                            if (data.count > 0) {
                                if (!badge) {
                                    badge = document.createElement('span');
                                    badge.className = 'notification-badge';
                                    badge.style.cssText = 'position: absolute; top: 0; right: 0; background-color: #EF4444; color: #FFFFFF; font-size: 10px; font-weight: 700; min-width: 16px; height: 16px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 1.5px solid var(--bg-card); padding: 0 3px; transform: translate(30%, -30%);';
                                    bellLink.appendChild(badge);
                                }
                                badge.textContent = data.count;
                            } else {
                                if (badge) badge.remove();
                            }
                        }

                        // Hiển thị popup toast cho thông báo mới
                        if (data.notifications && data.notifications.length > 0) {
                            data.notifications.forEach(showToast);
                        }
                    }
                })
                .catch(err => console.error("Error polling notifications:", err));
        }

        // Chạy ngay khi tải trang
        checkNotifications();

        // Thăm dò định kỳ mỗi 4 giây
        setInterval(checkNotifications, 4000);
    });
    </script>
    <?php endif; ?>

    <!-- =======================================================
         TECHPILOT DYNAMIC AI CHATBOT INTEGRATION
         ======================================================= -->
    <style>
        /* Chatbot Launcher */
        .tp-chatbot-launcher {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
            color: #FFFFFF;
            border: none;
            cursor: pointer;
            z-index: 100005 !important;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 8px 24px rgba(10, 91, 255, 0.3);
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s;
            overflow: hidden;
            padding: 0;
        }
        .tp-chatbot-launcher:hover {
            transform: scale(1.1);
            box-shadow: 0 12px 30px rgba(10, 91, 255, 0.4);
        }
        .tp-chatbot-launcher-pulse {
            position: absolute;
            top: 0; left: 0;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 2px solid var(--primary);
            animation: tpPulse 2s infinite;
            opacity: 0;
            pointer-events: none;
        }
        @keyframes tpPulse {
            0% { transform: scale(1); opacity: 0.6; }
            100% { transform: scale(1.5); opacity: 0; }
        }

        /* Chat Window */
        .tp-chatbot-window {
            position: fixed;
            bottom: 105px;
            right: 30px;
            width: 380px;
            height: 580px;
            border-radius: 16px;
            background: var(--surface-card, #FFFFFF);
            border: 1px solid var(--border, #E2E8F0);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
            z-index: 100004 !important;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            opacity: 0;
            pointer-events: none;
            transform: translateY(20px) scale(0.95);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .tp-chatbot-window.is-open {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0) scale(1);
        }

        /* Header */
        .tp-chatbot-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #FFFFFF;
        }
        .tp-chatbot-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .tp-chatbot-header-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            overflow: hidden;
        }
        .tp-chatbot-header-info h4 {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
        }
        .tp-chatbot-header-status {
            font-size: 11px;
            opacity: 0.85;
            display: flex;
            align-items: center;
            gap: 4px;
            margin-top: 2px;
        }
        .status-dot {
            width: 7px;
            height: 7px;
            background: #10B981;
            border-radius: 50%;
            display: inline-block;
        }
        .tp-chatbot-close {
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.8);
            font-size: 18px;
            cursor: pointer;
            transition: color 0.2s;
        }
        .tp-chatbot-close:hover {
            color: #FFFFFF;
        }

        /* Body */
        .tp-chatbot-body {
            flex: 1;
            overflow-y: auto;
            background: var(--surface-muted, #F8FAFC);
            padding: 16px;
        }
        .tp-chatbot-messages {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* Bubbles */
        .tp-message {
            display: flex;
            gap: 10px;
            max-width: 85%;
            margin-bottom: 4px;
        }
        .tp-message.user {
            align-self: flex-end;
            flex-direction: row-reverse;
        }
        .tp-message.bot {
            align-self: flex-start;
        }
        .tp-message-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--primary);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            flex-shrink: 0;
            overflow: hidden;
        }
        .tp-message-content {
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            line-height: 1.6;
            word-break: break-word;
        }
        .tp-message.bot .tp-message-content {
            background: var(--surface-card, #FFFFFF);
            color: var(--text-primary, #0F172A);
            border: 1px solid var(--border, #E2E8F0);
            border-top-left-radius: 2px;
        }
        .tp-message.user .tp-message-content {
            background: var(--primary);
            color: #FFFFFF;
            border-top-right-radius: 2px;
        }
        .tp-message-content p {
            margin: 0 0 8px 0;
        }
        .tp-message-content p:last-child {
            margin-bottom: 0;
        }
        .tp-message-content ul, .tp-message-content ol {
            margin: 0;
            padding-left: 18px;
        }

        /* Actions Grid & Buttons */
        .tp-actions-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 10px;
            width: 100%;
        }
        .tp-action-btn {
            background: var(--surface-card, #FFFFFF);
            border: 1px solid var(--border, #E2E8F0);
            border-radius: 8px;
            padding: 10px 8px;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            color: var(--text-primary, #0F172A);
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }
        .tp-action-btn i {
            font-size: 16px;
            color: var(--primary);
        }
        .tp-action-btn:hover {
            border-color: var(--primary);
            background: rgba(10, 91, 255, 0.05);
            transform: translateY(-1px);
        }
        
        .tp-options-list {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-top: 10px;
        }
        .tp-option-choice {
            background: var(--surface-card, #FFFFFF);
            border: 1px solid var(--border, #E2E8F0);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            color: var(--text-primary, #0F172A);
            transition: all 0.2s;
            text-align: left;
        }
        .tp-option-choice:hover {
            border-color: var(--primary);
            background: rgba(10, 91, 255, 0.05);
            color: var(--primary);
        }

        /* Recommendations layout */
        .tp-recommendations {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 10px;
        }
        .tp-rec-card {
            background: var(--surface-card, #FFFFFF);
            border: 1px solid var(--border, #E2E8F0);
            border-radius: 12px;
            padding: 12px;
            display: flex;
            gap: 12px;
            align-items: center;
            transition: all 0.2s;
            box-shadow: var(--shadow-card);
        }
        .tp-rec-card:hover {
            border-color: var(--primary);
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .tp-rec-img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
            background: #F1F5F9;
            flex-shrink: 0;
        }
        .tp-rec-info {
            flex: 1;
            min-width: 0;
        }
        .tp-rec-info h5 {
            margin: 0 0 4px 0;
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--text-primary, #0F172A);
        }
        .tp-rec-price {
            font-size: 12px;
            font-weight: 700;
            color: #EF4444;
            margin-bottom: 6px;
        }
        .tp-rec-score {
            font-size: 10px;
            font-weight: 700;
            background: #D1FAE5;
            color: #065F46;
            padding: 2px 6px;
            border-radius: 20px;
            display: inline-block;
        }
        .tp-rec-reasons {
            font-size: 10.5px;
            color: var(--text-secondary, #64748B);
            list-style: none;
            padding: 0;
            margin: 6px 0 0 0;
        }
        .tp-rec-reasons li {
            margin-bottom: 2px;
        }
        .tp-rec-link {
            font-size: 11px;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-top: 6px;
        }

        /* Comparison matrix */
        .tp-compare-container {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 100%;
        }
        .tp-compare-slots-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 4px;
        }
        .tp-compare-slot {
            background: var(--surface-card, #fff);
            border: 2px dashed var(--border);
            border-radius: 8px;
            padding: 10px 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            min-height: 150px;
            transition: all 0.2s ease;
        }
        .tp-compare-slot.drag-over {
            border-color: var(--primary);
            background: rgba(10, 91, 255, 0.08);
            transform: scale(1.02);
        }
        .tp-compare-slot-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
            font-size: 10px;
            text-align: center;
            pointer-events: none;
        }
        .tp-compare-slot-placeholder i {
            font-size: 24px;
            margin-bottom: 6px;
            color: var(--primary);
        }
        .tp-compare-slot-selected {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            width: 100%;
            position: relative;
        }
        .tp-compare-slot-img {
            width: 60px;
            height: 60px;
            object-fit: contain;
            margin-bottom: 6px;
            border-radius: 4px;
        }
        .tp-compare-slot-name {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-primary);
            display: -webkit-box;
            max-height: 32px;
            overflow: hidden;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            line-height: 1.2;
            margin-bottom: 4px;
        }
        .tp-compare-slot-clear {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #EF4444;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 11px;
            line-height: 18px;
            text-align: center;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 5;
        }
        .tp-compare-search-box {
            width: 100%;
            margin-top: 8px;
            position: relative;
        }
        .tp-compare-search-input {
            width: 100%;
            padding: 6px 8px;
            border-radius: 6px;
            border: 1px solid var(--border);
            font-size: 10px;
            background: var(--surface-card);
            color: var(--text-primary);
        }
        .tp-compare-suggestions {
            position: absolute;
            bottom: 100%;
            left: 0;
            width: 100%;
            max-height: 120px;
            overflow-y: auto;
            background: var(--surface-card);
            border: 1px solid var(--border);
            border-radius: 6px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            z-index: 100;
            margin-bottom: 4px;
        }
        .tp-compare-suggestion-item {
            padding: 6px 8px;
            font-size: 10px;
            cursor: pointer;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--text-primary);
            text-align: left;
        }
        .tp-compare-suggestion-item:hover {
            background: rgba(10, 91, 255, 0.08);
            color: var(--primary);
        }
        .tp-chatbot-launcher.drag-active {
            transform: scale(1.15) rotate(5deg);
            box-shadow: 0 0 20px rgba(10, 91, 255, 0.6);
            background: var(--primary-hover);
        }
        .tp-chatbot-window.drag-active {
            border: 2px dashed var(--primary) !important;
            box-shadow: 0 0 30px rgba(10, 91, 255, 0.4);
        }
        .tp-compare-btn {
            width: 100%;
            background: var(--primary);
            color: #FFFFFF;
            border: none;
            padding: 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }
        .tp-compare-btn:hover {
            background: var(--primary-hover);
        }
        
        .tp-compare-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-top: 8px;
            color: var(--text-primary);
        }
        .tp-compare-table th {
            background: var(--border);
            padding: 6px;
            font-weight: 700;
            text-align: center;
        }
        .tp-compare-table td {
            padding: 6px;
            border-bottom: 1px solid var(--border);
            text-align: center;
        }
        .tp-compare-table td:first-child {
            text-align: left;
            font-weight: 700;
            background: var(--surface-muted);
        }
        .tp-compare-stars {
            color: #FBBF24;
        }

        /* Footer Input */
        .tp-chatbot-footer {
            padding: 10px 15px;
            border-top: 1px solid var(--border, #E2E8F0);
            background: var(--surface-card, #FFFFFF);
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .tp-chatbot-footer input {
            flex: 1;
            border: 1px solid var(--border, #E2E8F0);
            background: var(--surface-muted, #F8FAFC);
            color: var(--text-primary, #0F172A);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            outline: none;
            transition: border-color 0.2s;
        }
        .tp-chatbot-footer input:focus {
            border-color: var(--primary);
        }
        .tp-chatbot-send {
            background: var(--primary);
            color: #FFFFFF;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            transition: background 0.2s;
        }
        .tp-chatbot-send:hover {
            background: var(--primary-hover);
        }

        /* Typing indicator */
        .tp-typing {
            display: inline-flex;
            gap: 4px;
            align-items: center;
            padding: 6px 12px;
        }
        .tp-typing span {
            width: 6px;
            height: 6px;
            background: var(--text-secondary, #64748B);
            border-radius: 50%;
            animation: tpBounce 1.4s infinite ease-in-out both;
        }
        .tp-typing span:nth-child(1) { animation-delay: -0.32s; }
        .tp-typing span:nth-child(2) { animation-delay: -0.16s; }
        @keyframes tpBounce {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1); }
        }

        /* Dark Mode overrides */
        .dark-mode .tp-message.bot .tp-message-content {
            background: #1E293B;
            border-color: #334155;
            color: #F8FAFC;
        }
        .dark-mode .tp-action-btn,
        .dark-mode .tp-option-choice,
        .dark-mode .tp-rec-card {
            background: #1E293B;
            border-color: #334155;
            color: #F8FAFC;
        }
        .dark-mode .tp-action-btn:hover,
        .dark-mode .tp-option-choice:hover {
            border-color: var(--primary);
            background: rgba(10, 91, 255, 0.1);
        }
        .dark-mode .tp-compare-slot,
        .dark-mode .tp-compare-search-input,
        .dark-mode .tp-compare-suggestions {
            background: #1E293B;
            border-color: #334155;
            color: #F8FAFC;
        }
        .dark-mode .tp-compare-table td:first-child {
            background: #0F172A;
        }

        /* Responsive */
        @media (max-width: 575px) {
            .tp-chatbot-window {
                width: calc(100% - 30px);
                right: 15px;
                left: 15px;
                bottom: 150px;
                height: calc(100vh - 220px);
            }
            .tp-chatbot-launcher {
                bottom: 80px;
                right: 20px;
            }
        }
    </style>

    <!-- Floating Launcher Button -->
    <button type="button" class="tp-chatbot-launcher" id="tpChatbotLauncher" onclick="toggleChatbot()">
        <img src="<?= url('assets/images/chatbot-avatar.png') ?>" alt="AI Avatar" style="width: 100%; height: 100%; object-fit: cover; display: block;">
        <span class="tp-chatbot-launcher-pulse"></span>
    </button>

    <!-- Chat Window -->
    <div class="tp-chatbot-window" id="tpChatbotWindow">
        <!-- Header -->
        <div class="tp-chatbot-header">
            <div class="tp-chatbot-header-left">
                <span class="tp-chatbot-header-avatar"><img src="<?= url('assets/images/chatbot-avatar.png') ?>" alt="AI Avatar" style="width: 100%; height: 100%; object-fit: cover;"></span>
                <div class="tp-chatbot-header-info">
                    <h4>TechPilot AI</h4>
                    <span class="tp-chatbot-header-status"><span class="status-dot"></span> Trợ lý ảo online</span>
                </div>
            </div>
            <button type="button" class="tp-chatbot-close" onclick="toggleChatbot()"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <!-- Body / Messages -->
        <div class="tp-chatbot-body" id="tpChatbotBody">
            <div class="tp-chatbot-messages" id="tpChatbotMessages">
                <!-- Welcome message will be added dynamically -->
            </div>
        </div>

        <!-- Footer Input -->
        <div class="tp-chatbot-footer">
            <input type="text" id="tpChatbotInput" placeholder="Hỏi AI về Laptop, RAM, CPU..." onkeydown="handleChatbotKey(event)">
            <button type="button" class="tp-chatbot-send" onclick="sendChatbotMessage()"><i class="fa-solid fa-paper-plane"></i></button>
        </div>
    </div>

    <!-- Chatbot Logics -->
    <script>
        let chatbotOpen = false;
        let chatbotProducts = [];
        let chatbotQuizState = {
            active: false,
            step: 0,
            profile: {
                group: '',
                budget: '',
                priority: ''
            }
        };

        // Global Drag and Drop and Autocomplete Setup
        document.addEventListener('dragstart', (e) => {
            const card = e.target.closest('.product-card');
            if (card) {
                const id = card.querySelector('input[name="product_id"]')?.value || card.dataset.id;
                const name = card.querySelector('.product-card__name')?.innerText.trim() || card.dataset.name;
                const image = card.querySelector('.product-card__image')?.getAttribute('src') || card.dataset.image;
                
                const dragData = {
                    id: id,
                    name: name,
                    image: image
                };
                
                e.dataTransfer.setData('text/plain', JSON.stringify(dragData));
                e.dataTransfer.effectAllowed = 'copy';
                
                const windowEl = document.getElementById('tpChatbotWindow');
                const launcherEl = document.getElementById('tpChatbotLauncher');
                if (windowEl) windowEl.classList.add('drag-active');
                if (launcherEl) launcherEl.classList.add('drag-active');
            }
        });

        document.addEventListener('dragend', () => {
            const windowEl = document.getElementById('tpChatbotWindow');
            const launcherEl = document.getElementById('tpChatbotLauncher');
            if (windowEl) windowEl.classList.remove('drag-active');
            if (launcherEl) launcherEl.classList.remove('drag-active');
            
            document.querySelectorAll('.tp-compare-slot').forEach(slot => {
                slot.classList.remove('drag-over');
            });
        });

        // MutationObserver to automatically make all product cards draggable
        const cardObserver = new MutationObserver(() => {
            document.querySelectorAll('.product-card:not([draggable])').forEach(card => {
                card.setAttribute('draggable', 'true');
            });
        });
        cardObserver.observe(document.body, { childList: true, subtree: true });
        
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.product-card').forEach(card => {
                card.setAttribute('draggable', 'true');
            });
            
            // Set up launcher drop handler
            const launcher = document.getElementById('tpChatbotLauncher');
            if (launcher) {
                launcher.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'copy';
                });
                launcher.addEventListener('drop', (e) => {
                    e.preventDefault();
                    try {
                        const data = JSON.parse(e.dataTransfer.getData('text/plain'));
                        if (data && data.id) {
                            if (!chatbotOpen) {
                                toggleChatbot();
                            }
                            startCompareFlow();
                            setCompareSlot('left', data);
                        }
                    } catch(err) {}
                });
            }

            // Set up window drop handler
            const chatWindow = document.getElementById('tpChatbotWindow');
            if (chatWindow) {
                chatWindow.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'copy';
                });
                chatWindow.addEventListener('drop', (e) => {
                    const leftVal = document.getElementById('compare_left_val');
                    const rightVal = document.getElementById('compare_right_val');
                    if (leftVal || rightVal) { // Compare mode active
                        if (!e.target.closest('.tp-compare-slot')) {
                            e.preventDefault();
                            try {
                                const data = JSON.parse(e.dataTransfer.getData('text/plain'));
                                if (data && data.id) {
                                    if (!leftVal.value) {
                                        setCompareSlot('left', data);
                                    } else if (!rightVal.value) {
                                        setCompareSlot('right', data);
                                    } else {
                                        setCompareSlot('left', data);
                                    }
                                }
                            } catch(err) {}
                        }
                    }
                });
            }
        });

        // Close search suggestions on click outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.tp-compare-search-box')) {
                document.querySelectorAll('.tp-compare-suggestions').forEach(box => {
                    box.innerHTML = '';
                    box.style.display = 'none';
                });
            }
        });

        // Toggle Chat Window
        function toggleChatbot() {
            const windowEl = document.getElementById('tpChatbotWindow');
            const launcherEl = document.getElementById('tpChatbotLauncher');
            chatbotOpen = !chatbotOpen;
            if (chatbotOpen) {
                windowEl.classList.add('is-open');
                if (launcherEl) launcherEl.style.display = 'none';
                // Khởi tạo tin nhắn chào mừng nếu chưa có
                const msgBox = document.getElementById('tpChatbotMessages');
                if (msgBox.children.length === 0) {
                    renderBotMessage("Xin chào! 👋 Tôi là trợ lý ảo **TechPilot AI**. Tôi có thể giúp gì cho bạn hôm nay?");
                    renderInitialActions();
                }
                // Tải trước danh sách laptop nếu chưa tải
                if (chatbotProducts.length === 0) {
                    loadChatbotProducts();
                }
            } else {
                windowEl.classList.remove('is-open');
                if (launcherEl) launcherEl.style.display = 'flex';
            }
        }

        // Tải danh sách laptop phục vụ so sánh
        function loadChatbotProducts() {
            fetch('<?= url("chatbot/products") ?>')
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        chatbotProducts = res.data;
                    }
                })
                .catch(err => console.error("Error loading chatbot products:", err));
        }

        // Renders
        function renderBotMessage(html) {
            const msgBox = document.getElementById('tpChatbotMessages');
            const wrapper = document.createElement('div');
            wrapper.className = 'tp-message bot';
            wrapper.innerHTML = `
                <div class="tp-message-avatar"><img src="<?= url('assets/images/chatbot-avatar.png') ?>" alt="Bot Avatar" style="width: 100%; height: 100%; object-fit: cover;"></div>
                <div class="tp-message-content">${formatMarkdownText(html)}</div>
            `;
            msgBox.appendChild(wrapper);
            scrollChatToBottom();
            return wrapper;
        }

        function renderUserMessage(text) {
            const msgBox = document.getElementById('tpChatbotMessages');
            const wrapper = document.createElement('div');
            wrapper.className = 'tp-message user';
            wrapper.innerHTML = `
                <div class="tp-message-content">${formatMarkdownText(text)}</div>
            `;
            msgBox.appendChild(wrapper);
            scrollChatToBottom();
        }

        function renderTypingIndicator() {
            const msgBox = document.getElementById('tpChatbotMessages');
            const wrapper = document.createElement('div');
            wrapper.className = 'tp-message bot tp-typing-wrapper';
            wrapper.innerHTML = `
                <div class="tp-message-avatar"><img src="<?= url('assets/images/chatbot-avatar.png') ?>" alt="Bot Avatar" style="width: 100%; height: 100%; object-fit: cover;"></div>
                <div class="tp-message-content">
                    <div class="tp-typing">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            `;
            msgBox.appendChild(wrapper);
            scrollChatToBottom();
            return wrapper;
        }

        function removeTypingIndicator(indicatorEl) {
            if (indicatorEl && indicatorEl.parentNode) {
                indicatorEl.parentNode.removeChild(indicatorEl);
            }
        }

        function scrollChatToBottom() {
            const bodyEl = document.getElementById('tpChatbotBody');
            bodyEl.scrollTop = bodyEl.scrollHeight;
        }

        // Format Markdown cơ bản
        function formatMarkdownText(text) {
            return text
                .replace(/\n/g, '<br>')
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/✔️/g, '<span style="color:#10B981;">✔️</span>')
                .replace(/✔/g, '<span style="color:#10B981;">✔</span>')
                .replace(/⚠️/g, '<span style="color:#FBBF24;">⚠️</span>')
                .replace(/• (.*?)(<br>|$)/g, '<li style="margin-left: 10px;">$1</li>');
        }

        // Khởi động các tác vụ nhanh ban đầu
        function renderInitialActions() {
            const msgBox = document.getElementById('tpChatbotMessages');
            const grid = document.createElement('div');
            grid.className = 'tp-actions-grid';
            grid.innerHTML = `
                <button type="button" class="tp-action-btn" onclick="triggerAction('quiz')">
                    <i class="fa-solid fa-graduation-cap"></i>
                    <span>Tư vấn theo nhu cầu</span>
                </button>
                <button type="button" class="tp-action-btn" onclick="triggerAction('compare')">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>AI So sánh</span>
                </button>
                <button type="button" class="tp-action-btn" onclick="triggerAction('budget')">
                    <i class="fa-solid fa-wallet"></i>
                    <span>Chọn theo ngân sách</span>
                </button>
                <button type="button" class="tp-action-btn" onclick="triggerAction('faq')">
                    <i class="fa-solid fa-circle-question"></i>
                    <span>Hỏi đáp AI</span>
                </button>
            `;
            msgBox.appendChild(grid);
            scrollChatToBottom();
        }

        // Kích hoạt nút tác vụ
        function triggerAction(action) {
            // Hủy trạng thái quiz nếu đang dở
            chatbotQuizState.active = false;

            if (action === 'quiz') {
                renderUserMessage("Tôi muốn tư vấn chọn Laptop theo nhu cầu");
                startQuizFlow();
            } else if (action === 'compare') {
                renderUserMessage("Tôi muốn so sánh sản phẩm");
                startCompareFlow();
            } else if (action === 'budget') {
                renderUserMessage("Tôi muốn tìm laptop theo ngân sách");
                startBudgetFlow();
            } else if (action === 'faq') {
                renderUserMessage("Tôi muốn hỏi đáp AI");
                startFaqFlow();
            }
        }

        // ==========================================
        // CHỨC NĂNG 1: TƯ VẤN THEO NHU CẦU (QUIZ FLOW)
        // ==========================================
        function startQuizFlow() {
            chatbotQuizState.active = true;
            chatbotQuizState.step = 1;
            chatbotQuizState.profile = { group: '', budget: '', priority: '' };

            renderBotMessage("Bạn thuộc nhóm đối tượng nào?");
            
            const msgBox = document.getElementById('tpChatbotMessages');
            const optList = document.createElement('div');
            optList.className = 'tp-options-list';
            optList.innerHTML = `
                <button type="button" class="tp-option-choice" onclick="selectQuizChoice('group', 'Sinh viên')">○ Sinh viên</button>
                <button type="button" class="tp-option-choice" onclick="selectQuizChoice('group', 'Người đi làm')">○ Người đi làm</button>
                <button type="button" class="tp-option-choice" onclick="selectQuizChoice('group', 'Designer / Đồ họa')">○ Designer / Đồ họa</button>
                <button type="button" class="tp-option-choice" onclick="selectQuizChoice('group', 'Game thủ')">○ Game thủ</button>
                <button type="button" class="tp-option-choice" onclick="selectQuizChoice('group', 'Lập trình viên')">○ Lập trình viên</button>
                <button type="button" class="tp-option-choice" onclick="selectQuizChoice('group', 'Khác')">○ Khác</button>
            `;
            msgBox.appendChild(optList);
            scrollChatToBottom();
        }

        function selectQuizChoice(field, value) {
            // Xóa danh sách nút cũ
            const activeChoiceLists = document.querySelectorAll('.tp-options-list');
            if (activeChoiceLists.length > 0) {
                const lastList = activeChoiceLists[activeChoiceLists.length - 1];
                lastList.innerHTML = `<div style="font-size: 12px; color: var(--text-secondary); padding: 5px 10px;">✔ Đã chọn: <strong>${value}</strong></div>`;
            }

            renderUserMessage(value);

            if (field === 'group') {
                chatbotQuizState.profile.group = value;
                chatbotQuizState.step = 2;
                
                // Hỏi tiếp Ngân sách
                setTimeout(() => {
                    renderBotMessage("Hạn mức ngân sách tối đa của bạn là bao nhiêu?");
                    const msgBox = document.getElementById('tpChatbotMessages');
                    const optList = document.createElement('div');
                    optList.className = 'tp-options-list';
                    optList.innerHTML = `
                        <button type="button" class="tp-option-choice" onclick="selectQuizChoice('budget', 'under_5m')">○ Dưới 5 triệu</button>
                        <button type="button" class="tp-option-choice" onclick="selectQuizChoice('budget', '5_10m')">○ 5 - 10 triệu</button>
                        <button type="button" class="tp-option-choice" onclick="selectQuizChoice('budget', '10_20m')">○ 10 - 20 triệu</button>
                        <button type="button" class="tp-option-choice" onclick="selectQuizChoice('budget', 'over_20m')">○ Trên 20 triệu</button>
                    `;
                    msgBox.appendChild(optList);
                    scrollChatToBottom();
                }, 400);

            } else if (field === 'budget') {
                chatbotQuizState.profile.budget = value;
                chatbotQuizState.step = 3;

                // Hỏi tiếp Ưu tiên
                setTimeout(() => {
                    renderBotMessage("Bạn ưu tiên điều gì nhất ở Laptop?");
                    const msgBox = document.getElementById('tpChatbotMessages');
                    const optList = document.createElement('div');
                    optList.className = 'tp-options-list';
                    optList.innerHTML = `
                        <button type="button" class="tp-option-choice" onclick="selectQuizChoice('priority', 'Giá')">○ Tiết kiệm giá bán</button>
                        <button type="button" class="tp-option-choice" onclick="selectQuizChoice('priority', 'Hiệu năng')">○ Hiệu năng CPU mạnh mẽ</button>
                        <button type="button" class="tp-option-choice" onclick="selectQuizChoice('priority', 'Pin')">○ Pin khỏe dùng lâu</button>
                        <button type="button" class="tp-option-choice" onclick="selectQuizChoice('priority', 'Mỏng nhẹ')">○ Mỏng nhẹ dễ di chuyển</button>
                        <button type="button" class="tp-option-choice" onclick="selectQuizChoice('priority', 'Chơi game')">○ Card đồ họa chiến game mượt</button>
                    `;
                    msgBox.appendChild(optList);
                    scrollChatToBottom();
                }, 400);

            } else if (field === 'priority') {
                chatbotQuizState.profile.priority = value;
                chatbotQuizState.active = false; // Kết thúc quiz flow

                // Gọi API lấy kết quả tư vấn từ Backend
                setTimeout(() => {
                    const indicator = renderTypingIndicator();
                    const params = new URLSearchParams({
                        group: chatbotQuizState.profile.group,
                        budget: chatbotQuizState.profile.budget,
                        priority: chatbotQuizState.profile.priority
                    });

                    fetch('<?= url("chatbot/query?") ?>' + params.toString())
                        .then(res => res.json())
                        .then(res => {
                            removeTypingIndicator(indicator);
                            if (res.success) {
                                renderBotMessage(res.ai_message);
                                renderRecommendations(res.recommendations);
                            } else {
                                renderBotMessage("🤖 Đã có lỗi xảy ra trong quá trình xử lý: " + res.message);
                            }
                        })
                        .catch(err => {
                            removeTypingIndicator(indicator);
                            renderBotMessage("🤖 Lỗi kết nối máy chủ tư vấn.");
                        });
                }, 500);
            }
        }

        // Hiển thị Card các sản phẩm đề xuất
        function renderRecommendations(recs) {
            if (!recs || recs.length === 0) return;

            const msgBox = document.getElementById('tpChatbotMessages');
            const container = document.createElement('div');
            container.className = 'tp-recommendations';

            recs.forEach(item => {
                const card = document.createElement('div');
                card.className = 'tp-rec-card';
                
                let reasonsHtml = '';
                item.reasons.forEach(r => {
                    reasonsHtml += `<li>✔️ ${r}</li>`;
                });

                const baseUrl = '<?= rtrim(url(''), '/') ?>';
                const imgSrc = item.image
                    ? (item.image.startsWith('http') || item.image.startsWith('/') ? item.image
                        : (item.image.includes('/') ? baseUrl + '/' + item.image
                            : baseUrl + '/public/assets/images/products/' + item.image))
                    : baseUrl + '/public/assets/images/laptop-gaming.jpg';
                card.innerHTML = `
                    <img class="tp-rec-img" src="${imgSrc}" onerror="this.src='${baseUrl}/public/assets/images/laptop-gaming.jpg'">
                    <div class="tp-rec-info">
                        <h5>${item.name}</h5>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 5px;">
                            <span class="tp-rec-price">${item.price_formatted}</span>
                            <span class="tp-rec-score" style="background-color:#D1FAE5; color:#065F46; padding:2px 6px; border-radius:4px; font-size:10px; font-weight:700;">Fit ${item.score}%</span>
                        </div>
                        <ul class="tp-rec-reasons">${reasonsHtml}</ul>
                        <div style="display: flex; flex-direction: column; gap: 6px; margin-top: 8px;">
                            <a href="<?= url('product/detail/') ?>${item.slug}" class="tp-rec-link" target="_blank" style="text-align: center; text-decoration: none; padding: 6px; font-size: 11px; background-color: #F1F5F9; color: #1E293B; border-radius: 4px; font-weight:600; display: block;">
                                Xem chi tiết <i class="fa-solid fa-circle-arrow-right"></i>
                            </a>
                            <form method="post" action="<?= url('cart/add') ?>" style="margin: 0; padding: 0; background: none; border: none; box-shadow: none;">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <input type="hidden" name="product_id" value="${item.id}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="tp-rec-link" style="width: 100%; border: none; background-color: var(--primary); color: #FFFFFF; font-weight: 700; cursor: pointer; border-radius: 4px; padding: 6px; font-size: 11px; display: flex; align-items: center; justify-content: center; gap: 4px; box-sizing: border-box;">
                                    <i class="fa-solid fa-cart-shopping"></i> Thêm giỏ hàng
                                </button>
                            </form>
                        </div>
                    </div>
                `;
                container.appendChild(card);
            });

            msgBox.appendChild(container);
            
            // Hiện lại menu chính sau khi hoàn thành tư vấn
            setTimeout(() => {
                renderBotMessage("Bạn muốn tiếp tục hỏi về vấn đề gì nữa không?");
                renderInitialActions();
            }, 800);
            
            scrollChatToBottom();
        }

        // ==========================================
        // CHỨC NĂNG 2: AI SO SÁNH SẢN PHẨM
        // ==========================================
        function startCompareFlow() {
            renderBotMessage("Vui lòng kéo thả 2 sản phẩm từ trang web vào đây, hoặc gõ tìm kiếm bên dưới để so sánh:");

            const msgBox = document.getElementById('tpChatbotMessages');
            const container = document.createElement('div');
            container.className = 'tp-compare-container';

            container.innerHTML = `
                <div class="tp-compare-slots-wrapper">
                    <!-- Slot Left -->
                    <div class="tp-compare-slot" id="compare_slot_left" data-side="left">
                        <div class="tp-compare-slot-placeholder">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <p style="margin: 0; padding: 0 4px;">Kéo thả SP 1 vào đây</p>
                        </div>
                        <div class="tp-compare-slot-selected" style="display:none;">
                            <button type="button" class="tp-compare-slot-clear" onclick="clearCompareSlot('left')">&times;</button>
                            <img class="tp-compare-slot-img" src="" alt="">
                            <span class="tp-compare-slot-name">Tên sản phẩm</span>
                        </div>
                        <div class="tp-compare-search-box">
                            <input type="text" class="tp-compare-search-input" id="search_left" placeholder="Hoặc gõ tìm kiếm..." oninput="handleCompareSearch(event, 'left')">
                            <div class="tp-compare-suggestions" id="suggestions_left" style="display:none;"></div>
                        </div>
                        <input type="hidden" id="compare_left_val" value="">
                    </div>

                    <!-- Slot Right -->
                    <div class="tp-compare-slot" id="compare_slot_right" data-side="right">
                        <div class="tp-compare-slot-placeholder">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <p style="margin: 0; padding: 0 4px;">Kéo thả SP 2 vào đây</p>
                        </div>
                        <div class="tp-compare-slot-selected" style="display:none;">
                            <button type="button" class="tp-compare-slot-clear" onclick="clearCompareSlot('right')">&times;</button>
                            <img class="tp-compare-slot-img" src="" alt="">
                            <span class="tp-compare-slot-name">Tên sản phẩm</span>
                        </div>
                        <div class="tp-compare-search-box">
                            <input type="text" class="tp-compare-search-input" id="search_right" placeholder="Hoặc gõ tìm kiếm..." oninput="handleCompareSearch(event, 'right')">
                            <div class="tp-compare-suggestions" id="suggestions_right" style="display:none;"></div>
                        </div>
                        <input type="hidden" id="compare_right_val" value="">
                    </div>
                </div>
                <button type="button" class="tp-compare-btn" onclick="submitCompareFlow()">So sánh cấu hình</button>
            `;
            msgBox.appendChild(container);

            // Đăng ký sự kiện kéo thả cho các slot
            container.querySelectorAll('.tp-compare-slot').forEach(slot => {
                const side = slot.dataset.side;
                
                slot.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    slot.classList.add('drag-over');
                    e.dataTransfer.dropEffect = 'copy';
                });
                
                slot.addEventListener('dragleave', () => {
                    slot.classList.remove('drag-over');
                });
                
                slot.addEventListener('drop', (e) => {
                    e.preventDefault();
                    slot.classList.remove('drag-over');
                    try {
                        const data = JSON.parse(e.dataTransfer.getData('text/plain'));
                        if (data && data.id) {
                            setCompareSlot(side, data);
                        }
                    } catch(err) {
                        console.error("Drop error:", err);
                    }
                });
            });

            scrollChatToBottom();
        }

        // Autocomplete search handler
        window.handleCompareSearch = function(event, side) {
            const query = event.target.value.toLowerCase().trim();
            const suggestionsBox = document.getElementById(`suggestions_${side}`);
            
            if (query.length < 2) {
                suggestionsBox.innerHTML = '';
                suggestionsBox.style.display = 'none';
                return;
            }
            
            const matches = chatbotProducts.filter(p => p.name.toLowerCase().includes(query)).slice(0, 5);
            suggestionsBox.innerHTML = '';
            
            if (matches.length === 0) {
                const item = document.createElement('div');
                item.style.padding = '8px';
                item.style.fontSize = '10px';
                item.style.color = 'var(--text-secondary)';
                item.innerText = 'Không tìm thấy sản phẩm';
                suggestionsBox.appendChild(item);
                suggestionsBox.style.display = 'block';
                return;
            }
            
            matches.forEach(p => {
                const item = document.createElement('div');
                item.className = 'tp-compare-suggestion-item';
                item.innerText = `${p.name} (${p.price_formatted})`;
                item.addEventListener('click', () => {
                    const pData = {
                        id: p.id,
                        name: p.name,
                        image: p.image,
                        price: p.price_formatted,
                        slug: p.slug
                    };
                    setCompareSlot(side, pData);
                    suggestionsBox.innerHTML = '';
                    suggestionsBox.style.display = 'none';
                    const searchInput = document.getElementById(`search_${side}`);
                    if (searchInput) searchInput.value = '';
                });
                suggestionsBox.appendChild(item);
            });
            suggestionsBox.style.display = 'block';
        }

        // Set comparison product slot values
        window.setCompareSlot = function(side, data) {
            const slot = document.getElementById(`compare_slot_${side}`);
            if (!slot) return;
            
            const placeholder = slot.querySelector('.tp-compare-slot-placeholder');
            const selected = slot.querySelector('.tp-compare-slot-selected');
            const imgEl = slot.querySelector('.tp-compare-slot-img');
            const nameEl = slot.querySelector('.tp-compare-slot-name');
            const inputHidden = document.getElementById(`compare_${side}_val`);
            
            inputHidden.value = data.id;
            
            let imgUrl = data.image;
            if (imgUrl && !imgUrl.startsWith('http') && !imgUrl.startsWith('/') && !imgUrl.includes('public/')) {
                imgUrl = `<?= url('public/uploads/products/') ?>` + imgUrl;
            } else if (!imgUrl) {
                imgUrl = `<?= url('public/assets/images/laptop-gaming.jpg') ?>`;
            }
            
            imgEl.src = imgUrl;
            nameEl.innerText = data.name;
            
            placeholder.style.display = 'none';
            selected.style.display = 'flex';
        }

        // Clear comparison product slot values
        window.clearCompareSlot = function(side) {
            const slot = document.getElementById(`compare_slot_${side}`);
            if (!slot) return;
            
            const placeholder = slot.querySelector('.tp-compare-slot-placeholder');
            const selected = slot.querySelector('.tp-compare-slot-selected');
            const inputHidden = document.getElementById(`compare_${side}_val`);
            const searchInput = document.getElementById(`search_${side}`);
            
            inputHidden.value = '';
            if (searchInput) searchInput.value = '';
            placeholder.style.display = 'flex';
            selected.style.display = 'none';
        }

        function submitCompareFlow() {
            const leftId = document.getElementById('compare_left_val').value;
            const rightId = document.getElementById('compare_right_val').value;

            if (!leftId || !rightId) {
                alert("Vui lòng chọn đầy đủ 2 sản phẩm!");
                return;
            }

            const compareBtn = document.querySelector('.tp-compare-btn');
            if (compareBtn) compareBtn.disabled = true;

            const indicator = renderTypingIndicator();

            fetch(`<?= url("chatbot/compare?left_id=") ?>${leftId}&right_id=${rightId}`)
                .then(res => res.json())
                .then(res => {
                    removeTypingIndicator(indicator);
                    if (res.success) {
                        renderBotMessage("📊 **Bảng so sánh chi tiết giữa 2 sản phẩm:**");
                        renderCompareTable(res.data);
                    } else {
                        renderBotMessage("🤖 Lỗi: " + res.message);
                    }
                })
                .catch(err => {
                    removeTypingIndicator(indicator);
                    renderBotMessage("🤖 Lỗi kết nối hệ thống so sánh.");
                });
        }

        function renderCompareTable(data) {
            const msgBox = document.getElementById('tpChatbotMessages');
            const tableWrapper = document.createElement('div');
            tableWrapper.style.width = '100%';
            tableWrapper.style.overflowX = 'auto';

            const starHtml = (count) => {
                let stars = '';
                for (let i = 0; i < 5; i++) {
                    stars += i < count ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
                }
                return `<span class="tp-compare-stars">${stars}</span>`;
            };

            tableWrapper.innerHTML = `
                <table class="tp-compare-table">
                    <thead>
                        <tr>
                            <th>Tiêu chí</th>
                            <th>${data.left.name.split(' ').slice(0,3).join(' ')}...</th>
                            <th>${data.right.name.split(' ').slice(0,3).join(' ')}...</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Giá</td>
                            <td style="color:#EF4444; font-weight:700;">${data.left.price}</td>
                            <td style="color:#EF4444; font-weight:700;">${data.right.price}</td>
                        </tr>
                        <tr>
                            <td>CPU</td>
                            <td>${data.left.specs.CPU}</td>
                            <td>${data.right.specs.CPU}</td>
                        </tr>
                        <tr>
                            <td>RAM</td>
                            <td>${data.left.specs.RAM}</td>
                            <td>${data.right.specs.RAM}</td>
                        </tr>
                        <tr>
                            <td>SSD</td>
                            <td>${data.left.specs.SSD}</td>
                            <td>${data.right.specs.SSD}</td>
                        </tr>
                        <tr>
                            <td>VGA</td>
                            <td>${data.left.specs.VGA.split(' ').slice(0,2).join(' ')}</td>
                            <td>${data.right.specs.VGA.split(' ').slice(0,2).join(' ')}</td>
                        </tr>
                        <tr>
                            <td>Game</td>
                            <td>${starHtml(data.left.ratings.game)}</td>
                            <td>${starHtml(data.right.ratings.game)}</td>
                        </tr>
                        <tr>
                            <td>Văn phòng</td>
                            <td>${starHtml(data.left.ratings.office)}</td>
                            <td>${starHtml(data.right.ratings.office)}</td>
                        </tr>
                        <tr>
                            <td>Đồ họa</td>
                            <td>${starHtml(data.left.ratings.graphic)}</td>
                            <td>${starHtml(data.right.ratings.graphic)}</td>
                        </tr>
                    </tbody>
                </table>
            `;
            msgBox.appendChild(tableWrapper);

            // In lời khuyên của AI
            setTimeout(() => {
                let adviceHtml = "🤖 **AI Đánh giá tổng quan:**\n\n";
                data.advice.forEach(adv => {
                    adviceHtml += adv + "\n";
                });
                renderBotMessage(adviceHtml);
                renderInitialActions();
            }, 500);

            scrollChatToBottom();
        }

        // ==========================================
        // CHỨC NĂNG 3: TÌM KIẾM THEO NGÂN SÁCH NHANH
        // ==========================================
        function startBudgetFlow() {
            renderBotMessage("Hãy chọn khoảng ngân sách của bạn:");

            const msgBox = document.getElementById('tpChatbotMessages');
            const optList = document.createElement('div');
            optList.className = 'tp-options-list';
            optList.innerHTML = `
                <button type="button" class="tp-option-choice" onclick="selectBudgetBracket('under_5m', 'Dưới 5 triệu')">○ Dưới 5 triệu</button>
                <button type="button" class="tp-option-choice" onclick="selectBudgetBracket('5_10m', '5 - 10 triệu')">○ 5 - 10 triệu</button>
                <button type="button" class="tp-option-choice" onclick="selectBudgetBracket('10_20m', '10 - 20 triệu')">○ 10 - 20 triệu</button>
                <button type="button" class="tp-option-choice" onclick="selectBudgetBracket('over_20m', 'Trên 20 triệu')">○ Trên 20 triệu</button>
            `;
            msgBox.appendChild(optList);
            scrollChatToBottom();
        }

        function selectBudgetBracket(bracket, label) {
            const activeChoiceLists = document.querySelectorAll('.tp-options-list');
            if (activeChoiceLists.length > 0) {
                activeChoiceLists[activeChoiceLists.length - 1].remove();
            }

            renderUserMessage(label);
            
            const indicator = renderTypingIndicator();
            fetch(`<?= url("chatbot/query?budget=") ?>${bracket}`)
                .then(res => res.json())
                .then(res => {
                    removeTypingIndicator(indicator);
                    if (res.success) {
                        renderBotMessage(`🤖 Tìm thấy các mẫu Laptop nổi bật trong phân khúc **${label}**:`);
                        renderRecommendations(res.recommendations);
                    }
                })
                .catch(err => {
                    removeTypingIndicator(indicator);
                    renderBotMessage("🤖 Lỗi kết nối hệ thống tìm kiếm ngân sách.");
                });
        }

        // ==========================================
        // CHỨC NĂNG 4: HỎI ĐÁP AI (FAQ CHIPS)
        // ==========================================
        function startFaqFlow() {
            renderBotMessage("Bạn có thể click trực tiếp các câu hỏi mẫu bên dưới để hỏi trợ lý AI:");

            const msgBox = document.getElementById('tpChatbotMessages');
            const optList = document.createElement('div');
            optList.className = 'tp-options-list';
            optList.innerHTML = `
                <button type="button" class="tp-option-choice" onclick="sendNaturalQuery('RAM 8GB với 16GB khác gì?')">❓ RAM 8GB với 16GB khác gì?</button>
                <button type="button" class="tp-option-choice" onclick="sendNaturalQuery('i3 học lập trình được không?')">❓ CPU i3 học lập trình được không?</button>
                <button type="button" class="tp-option-choice" onclick="sendNaturalQuery('So sánh Core i3 và i7?')">❓ So sánh Core i3 và Core i7?</button>
                <button type="button" class="tp-option-choice" onclick="sendNaturalQuery('Laptop này chơi Valorant được không?')">❓ Laptop này chơi Valorant được không?</button>
                <button type="button" class="tp-option-choice" onclick="sendNaturalQuery('Laptop văn phòng dùng được bao lâu?')">❓ Máy tính văn phòng dùng được bao lâu?</button>
            `;
            msgBox.appendChild(optList);
            scrollChatToBottom();
        }

        function sendNaturalQuery(text) {
            const activeChoiceLists = document.querySelectorAll('.tp-options-list');
            if (activeChoiceLists.length > 0) {
                activeChoiceLists[activeChoiceLists.length - 1].remove();
            }
            document.getElementById('tpChatbotInput').value = text;
            sendChatbotMessage();
        }

        // ==========================================
        // GỬI TIN NHẮN TỰ NHIÊN (CHAT INPUT)
        // ==========================================
        function handleChatbotKey(event) {
            if (event.key === 'Enter') {
                sendChatbotMessage();
            }
        }

        function sendChatbotMessage() {
            const inputEl = document.getElementById('tpChatbotInput');
            const text = inputEl.value.trim();
            if (text === '') return;

            inputEl.value = '';
            renderUserMessage(text);

            const indicator = renderTypingIndicator();

            fetch('<?= url("chatbot/query?q=") ?>' + encodeURIComponent(text))
                .then(res => res.json())
                .then(res => {
                    removeTypingIndicator(indicator);
                    if (res.success) {
                        if (res.type === 'start_quiz') {
                            renderBotMessage(res.message);
                            startQuizFlow();
                        } else if (res.type === 'recommendations') {
                            renderBotMessage(res.ai_message);
                            renderRecommendations(res.recommendations);
                        } else {
                            renderBotMessage(res.message);
                            setTimeout(() => {
                                renderInitialActions();
                            }, 500);
                        }
                    } else {
                        // Tìm kiếm thô sản phẩm phù hợp nếu AI không nhận diện được từ khóa cụ thể
                        searchRawProducts(text);
                    }
                })
                .catch(err => {
                    removeTypingIndicator(indicator);
                    renderBotMessage("🤖 Xin lỗi, tôi gặp sự cố kết nối máy chủ AI.");
                });
        }

        function searchRawProducts(text) {
            const indicator = renderTypingIndicator();
            fetch('<?= url("chatbot/query?q=") ?>' + encodeURIComponent(text) + '&priority=performance')
                .then(res => res.json())
                .then(res => {
                    removeTypingIndicator(indicator);
                    if (res.success && res.recommendations && res.recommendations.length > 0) {
                        renderBotMessage("🤖 Tôi chưa hiểu rõ hoàn toàn câu hỏi của bạn. Tuy nhiên, dưới đây là các mẫu máy tính có thể bạn quan tâm dựa trên từ khóa tìm kiếm:");
                        renderRecommendations(res.recommendations);
                    } else {
                        renderBotMessage("🤖 Tôi chưa hiểu rõ câu hỏi của bạn. Bạn hãy thử click vào các tác vụ nhanh bên dưới hoặc liên hệ tổng đài hỗ trợ để được giải đáp chi tiết hơn nhé!");
                        renderInitialActions();
                    }
                })
                .catch(err => {
                    removeTypingIndicator(indicator);
                    renderBotMessage("🤖 Không thể tra cứu sản phẩm liên quan.");
                    renderInitialActions();
                });
        }
    </script>
</body>

</html>