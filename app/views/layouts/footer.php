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
    $isProductDetail = (strpos($_SERVER['REQUEST_URI'], '/product/detail/') !== false);
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

        // Chạy ngay khi tải trang
        checkNotifications();

        // Thăm dò định kỳ mỗi 4 giây
        setInterval(checkNotifications, 4000);
    });
    </script>
    <?php endif; ?>
</body>

</html>