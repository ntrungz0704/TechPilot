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
                    <img src="<?= url('assets/images/logo.png') ? alt="TechPilot Asset">" alt="TechPilot Logo" style="height: 40px; object-fit: contain; display: block;">
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
                <img src="<?= e(productImageUrl($product['image'] ?? '', $product['category_slug'] ?? $product['name'] ?? '', (int)($product['id'] ?? 0))) ? alt="TechPilot Asset">" alt="thumb">
                <div class="fixed-buy-bar__txt">
                    <span class="fixed-buy-bar__name"><?= e($product['name']) ?></span>
                    <span class="fixed-buy-bar__price"><?= formatPrice($product['final_price'] ?? $product['effective_price'] ?? $product['price']) ?></span>
                </div>
            </div>
            <button type="button" class="fixed-buy-bar__btn" onclick="mobileAddToCart()"><i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ</button>
        </div>
    <?php else: ?>
        <div class="mobile-bottom-nav">
            <a href="<?= url('/') ?>" class="mobile-bottom-nav__item">
                <i class="fa-solid fa-house"></i>
                <span>Trang chủ</span>
            </a>
            <button type="button" class="mobile-bottom-nav__item" id="mobileBottomNavCats" style="background: none; border: none; cursor: pointer; color: inherit;" aria-expanded="false" aria-controls="categoryMegaDropdown">
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
            <?php $bottomCartCount = (int)cartCount(); ?>
            <a href="<?= url('cart') ?>" class="mobile-bottom-nav__item" aria-label="Giỏ hàng<?= $bottomCartCount > 0 ? ', có ' . $bottomCartCount . ' sản phẩm' : '' ?>">
                <i class="fa-solid fa-cart-shopping"></i>
                <span>Giỏ hàng</span>
                <span class="cart-badge" style="display: <?= $bottomCartCount > 0 ? 'flex' : 'none' ?>"><?= $bottomCartCount ?></span>
            </a>
        </div>
    <?php endif; ?>

    <script src="<?= url('assets/js/main.js?v=10.0') ?>"></script>
    <script src="<?= url('assets/js/category-mega-menu.js?v=10.0') ?>"></script>
    <?php foreach ($pageScripts ?? [] as $script): ?>
        <script src="<?= url($script) ?>"></script>
    <?php endforeach; ?>
    <script>
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

    <script>
        function toggleWishlist(productId, btn) {
            const metaCsrf = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = metaCsrf ? metaCsrf.getAttribute('content') : '';

            fetch('<?= url("wishlist/toggle") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&csrf_token=${encodeURIComponent(csrfToken)}`
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    if (data.requireLogin) {
                        window.location.href = '<?= url("auth/login") ?>';
                    } else {
                        alert(data.message || 'Có lỗi xảy ra.');
                    }
                    return;
                }

                if (btn) {
                    const icon = btn.querySelector('i') || btn;
                    if (data.inWishlist) {
                        icon.className = 'fa-solid fa-heart';
                        icon.style.color = '#EF4444';
                        btn.classList.add('is-active');
                    } else {
                        icon.className = 'fa-regular fa-heart';
                        icon.style.color = '';
                        btn.classList.remove('is-active');
                    }
                }

                const wlBadge = document.getElementById('wishlistBadge');
                if (wlBadge) {
                    wlBadge.textContent = data.count;
                    wlBadge.style.display = data.count > 0 ? 'flex' : 'none';
                }
            })
            .catch(err => console.error('Wishlist toggle error:', err));
        }
    </script>
    <script>
        window.showStorefrontToast = function(type, message) {
            let container = document.querySelector('.toast-container');
            if (!container) {
                container = document.createElement('div');
                container.className = 'toast-container';
                container.style.cssText = 'position: fixed; bottom: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; max-width: 360px; pointer-events: none;';
                document.body.appendChild(container);

                if (!document.getElementById('toast-styles')) {
                    const styleEl = document.createElement('style');
                    styleEl.id = 'toast-styles';
                    styleEl.innerHTML = `
                        .toast-item { background-color: var(--bg-card, #FFFFFF); color: var(--text-primary, #0F172A); border: 1px solid var(--border, #E2E8F0); border-radius: 12px; padding: 16px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08); display: flex; gap: 12px; align-items: flex-start; animation: toastSlideIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; pointer-events: auto; transition: all 0.3s ease; border-left: 4px solid var(--primary, #0A5BFF); }
                        .toast-item.success { border-left-color: #10B981; }
                        .toast-item.error { border-left-color: #EF4444; }
                        .toast-item.info { border-left-color: #0A5BFF; }
                        @keyframes toastSlideIn { from { transform: translateX(120%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
                        .toast-item.fade-out { transform: translateX(120%); opacity: 0; }
                        .dark-mode .toast-item { background-color: #1E293B; border-color: #334155; color: #F8FAFC; }
                    `;
                    document.head.appendChild(styleEl);
                }
            }

            const toast = document.createElement('div');
            toast.setAttribute('role', 'status');
            toast.setAttribute('aria-live', 'polite');
            toast.className = 'toast-item ' + (type === 'success' ? 'success' : (type === 'error' ? 'error' : 'info'));
            let iconHtml = '<i class="fa-solid fa-circle-info" style="color:#0A5BFF;"></i>';
            if (type === 'success') iconHtml = '<i class="fa-solid fa-circle-check" style="color:#10B981;"></i>';
            else if (type === 'error') iconHtml = '<i class="fa-solid fa-circle-exclamation" style="color:#EF4444;"></i>';

            toast.innerHTML = `
                <div style="font-size: 20px;">${iconHtml}</div>
                <div class="toast-message-body" style="flex: 1; font-size: 13.5px; line-height: 1.5; font-weight: 500;"></div>
                <button type="button" class="toast-item__close" style="background:none; border:none; color:var(--text-secondary); cursor:pointer; font-size:14px; margin-left:8px;"><i class="fa-solid fa-xmark"></i></button>
            `;
            toast.querySelector('.toast-message-body').textContent = String(message);

            toast.querySelector('.toast-item__close').addEventListener('click', () => {
                toast.classList.add('fade-out');
                setTimeout(() => toast.remove(), 300);
            });

            container.appendChild(toast);
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.classList.add('fade-out');
                    setTimeout(() => toast.remove(), 300);
                }
            }, 3500);
        };
        document.addEventListener('DOMContentLoaded', function() {
            const addForms = document.querySelectorAll('.product-card__add-form');
            addForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const intentInput = form.querySelector('input[name="intent"]');
                    const intent = intentInput ? intentInput.value : 'add';
                    
                    if (intent === 'buy_now') {
                        return; // Let the form submit normally
                    }

                    e.preventDefault();
                    
                    const btn = form.querySelector('button[type="submit"]');
                    if (btn) btn.disabled = true;

                    const formData = new FormData(form);

                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => {
                        if (!res.ok && res.status !== 401 && res.status !== 403 && res.status !== 404 && res.status !== 409) {
                            throw new Error('Network error');
                        }
                        return res.json();
                    })
                    .then(data => {
                        if (btn) btn.disabled = false;
                        
                        if (!data.success) {
                            if (data.auth_required && data.login_url) {
                                window.location.href = data.login_url;
                            } else {
                                // Show error toast if available, otherwise alert
                                if (typeof window.showStorefrontToast === 'function') {
                                    window.showStorefrontToast('error', data.message || 'Có lỗi xảy ra.');
                                } else {
                                    alert(data.message || 'Có lỗi xảy ra.');
                                }
                            }
                            return;
                        }

                        // Success Add
                        if (typeof window.showStorefrontToast === 'function') {
                            window.showStorefrontToast('success', data.message || 'Thêm thành công.');
                        } else {
                            // minimal toast fallback
                            const toastFallback = document.createElement('div');
                            toastFallback.style.cssText = 'position:fixed;bottom:20px;right:20px;background:#10B981;color:#fff;padding:12px 20px;border-radius:4px;z-index:9999;box-shadow:0 4px 6px rgba(0,0,0,0.1);font-family:sans-serif;';
                            toastFallback.textContent = data.message || 'Đã thêm vào giỏ hàng.';
                            document.body.appendChild(toastFallback);
                            setTimeout(() => toastFallback.remove(), 3000);
                        }

                        if (data.cart_count !== undefined) {
                            const badges = document.querySelectorAll('.cart-badge, #cartBadge');
                            badges.forEach(badge => {
                                badge.textContent = data.cart_count;
                                badge.style.display = data.cart_count > 0 ? 'flex' : 'none';
                                const parentLink = badge.closest('a');
                                if (parentLink) {
                                    parentLink.setAttribute('aria-label', 'Giỏ hàng có ' + data.cart_count + ' sản phẩm');
                                }
                            });
                        }
                    })
                    .catch(err => {
                        if (btn) btn.disabled = false;
                        if (typeof window.showStorefrontToast === 'function') {
                            window.showStorefrontToast('error', 'Lỗi kết nối mạng.');
                        } else {
                            alert('Lỗi kết nối mạng.');
                        }
                    });
                });
            });
        });
    </script>

    <?php if (currentUser()): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
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

            let msg = notif.title + ': ' + notif.content;
            if (typeof window.showStorefrontToast === 'function') {
                window.showStorefrontToast('info', msg);
            }
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
            animation: tpMascotFloat 3s ease-in-out infinite;
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
        @keyframes tpMascotFloat {
            0% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(-8px) scale(1.05); }
            100% { transform: translateY(0px) scale(1); }
        }

        /* Chat Window with Glassmorphism & Neon Glow */
        .tp-chatbot-window {
            position: fixed;
            bottom: 105px;
            right: 30px;
            width: 380px;
            height: 590px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px) saturate(180%);
            -webkit-backdrop-filter: blur(12px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.45);
            box-shadow: 0 15px 40px rgba(10, 91, 255, 0.15);
            z-index: 100004 !important;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            opacity: 0;
            pointer-events: none;
            transform: translateY(20px) scale(0.95);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .dark-mode .tp-chatbot-window {
            background: rgba(15, 23, 42, 0.88);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
        }
        .tp-chatbot-window.is-open {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0) scale(1);
            animation: tpBorderGlow 4s linear infinite;
        }
        @keyframes tpBorderGlow {
            0%, 100% { border-color: rgba(10, 91, 255, 0.4); box-shadow: 0 15px 40px rgba(10, 91, 255, 0.2); }
            50% { border-color: rgba(147, 51, 234, 0.4); box-shadow: 0 15px 40px rgba(147, 51, 234, 0.2); }
        }


        /* Suggestion Chips styling (Hidden per user request) */
        .tp-chatbot-suggestions-chips {
            display: none !important;
        }
        .dark-mode .tp-chatbot-suggestions-chips {
            background: rgba(15, 23, 42, 0.45);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }
        .tp-chatbot-suggestions-chips::-webkit-scrollbar {
            height: 3px;
        }
        .tp-chatbot-suggestions-chips::-webkit-scrollbar-thumb {
            background: rgba(10, 91, 255, 0.2);
            border-radius: 3px;
        }
        .tp-chip {
            background: var(--surface-card, #FFFFFF);
            border: 1px solid var(--border, #E2E8F0);
            border-radius: 100px;
            padding: 6px 12px;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-primary, #0F172A);
            cursor: pointer;
            transition: all 0.2s;
            display: inline-block;
        }
        .tp-chip:hover {
            border-color: var(--primary);
            background: rgba(10, 91, 255, 0.05);
            color: var(--primary);
            transform: translateY(-1px);
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
        <img src="<?= url('assets/images/chatbot-avatar.png') ? alt="TechPilot Asset">" alt="AI Avatar" style="width: 100%; height: 100%; object-fit: cover; display: block;">
        <span class="tp-chatbot-launcher-pulse"></span>
    </button>

    <!-- Chat Window -->
    <div class="tp-chatbot-window" id="tpChatbotWindow">
        <!-- Header -->
        <div class="tp-chatbot-header">
            <div class="tp-chatbot-header-left">
                <span class="tp-chatbot-header-avatar"><img src="<?= url('assets/images/chatbot-avatar.png') ? alt="TechPilot Asset">" alt="AI Avatar" style="width: 100%; height: 100%; object-fit: cover;"></span>
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

        <!-- Smart Suggestions (Disabled per user request) -->
        <div class="tp-chatbot-suggestions-chips" id="tpChatbotChips" style="display: none !important;"></div>

        <!-- Footer Input -->
        <div class="tp-chatbot-footer">
            <input type="text" id="tpChatbotInput" placeholder="Hỏi AI về Laptop, RAM, CPU..." onkeydown="handleChatbotKey(event)">
            <button type="button" class="tp-chatbot-send" onclick="sendChatbotMessage()"><i class="fa-solid fa-paper-plane"></i></button>
        </div>
    </div>

    <!-- Chatbot Logics -->
    <script>
        // ==========================================
        // SYSTEM ARCHITECTURE: BEHAVIOR TRACKER & INTEREST ANALYZER (Phase 1 & 2)
        // ==========================================
        class BehaviorTracker {
            constructor() {
                this.MAX_HISTORY = 50;
                this.weights = {
                    'view_category': 1,
                    'view_product': 3,
                    'product_detail': 5,
                    'read_review': 4,
                    'search': 3,
                    'compare': 10,
                    'wishlist': 8,
                    'add_cart': 15,
                    'purchase': 100,
                    'click_image': 2,
                    'filter': 2,
                    'sort': 2
                };
            }

            getHistory() {
                try {
                    const saved = localStorage.getItem('tp_user_history');
                    return saved ? JSON.parse(saved) : [];
                } catch(e) {
                    return [];
                }
            }

            pushAction(type, value, metadata = {}) {
                try {
                    let history = this.getHistory();
                    
                    // Tránh ghi nhận trùng lặp liên tiếp của cùng loại hoạt động trong 2 giây
                    if (history.length > 0 && history[0].type === type && JSON.stringify(history[0].value) === JSON.stringify(value)) {
                        if (Date.now() - history[0].timestamp < 2000) {
                            return; 
                        }
                    }

                    history.unshift({
                        type: type,
                        value: value,
                        metadata: metadata,
                        timestamp: Date.now()
                    });

                    if (history.length > this.MAX_HISTORY) {
                        history = history.slice(0, this.MAX_HISTORY);
                    }

                    localStorage.setItem('tp_user_history', JSON.stringify(history));
                    
                    // Đồng bộ phân tích ngay sau khi có hành động
                    this.updateInterestProfile();
                    this.updateCurrentSession();
                } catch(e) {
                    console.error("Tracker push error:", e);
                }
            }

            updateCurrentSession() {
                let history = this.getHistory();
                let counts = {};
                history.forEach(act => {
                    if (act.type === 'product_detail' || act.type === 'view_product') {
                        const name = (typeof act.value === 'object') ? (act.value.name || '') : act.value;
                        if (name) {
                            counts[name] = (counts[name] || 0) + 1;
                        }
                    }
                });
                sessionStorage.setItem('tp_current_session_views', JSON.stringify(counts));
            }

            updateInterestProfile() {
                let history = this.getHistory();
                let brandScores = {};
                let categoryScores = {};
                let searchKeywords = [];
                let budgetList = [];

                history.forEach(act => {
                    const weight = this.weights[act.type] || 1;
                    
                    // Thuật toán Interest Decay (Giảm 2% mỗi ngày)
                    const daysPassed = (Date.now() - act.timestamp) / (1000 * 60 * 60 * 24);
                    const decayFactor = Math.pow(0.98, daysPassed);
                    const finalScore = weight * decayFactor;

                    const meta = act.metadata || {};
                    
                    if (meta.brand) {
                        const bName = meta.brand.toUpperCase().trim();
                        brandScores[bName] = (brandScores[bName] || 0) + finalScore;
                    }
                    
                    if (meta.category) {
                        categoryScores[meta.category] = (categoryScores[meta.category] || 0) + finalScore;
                    }

                    if (act.type === 'search' && typeof act.value === 'string') {
                        if (!searchKeywords.includes(act.value)) {
                            searchKeywords.push(act.value);
                        }
                    }

                    if (meta.price) {
                        budgetList.push(parseFloat(meta.price));
                    }
                });

                let budgetMin = 0;
                let budgetMax = 0;
                if (budgetList.length > 0) {
                    budgetMin = Math.min(...budgetList) * 0.8;
                    budgetMax = Math.max(...budgetList) * 1.2;
                }

                const profile = {
                    brands: brandScores,
                    categories: categoryScores,
                    keywords: searchKeywords.slice(0, 5),
                    budget: { min: Math.round(budgetMin), max: Math.round(budgetMax) },
                    updatedAt: Date.now()
                };

                localStorage.setItem('tp_interest_profile', JSON.stringify(profile));
            }
        }
        window.tpBehaviorTracker = new BehaviorTracker();

        // Cloud Database & Sync (Phase 3)
        window.syncUserBehavior = function() {
            const history = window.tpBehaviorTracker.getHistory();
            fetch('<?= url("chatbot/sync") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': <?= json_encode($_SESSION['csrf_token'] ?? '') ?>
                },
                body: JSON.stringify({ history: history })
            })
            .then(res => {
                const ct = res.headers.get('content-type') || '';
                if (!ct.includes('application/json')) {
                    throw new Error('Server returned non-JSON response');
                }
                return res.json();
            })
            .then(res => {
                if (res.success) {
                    localStorage.setItem('tp_user_history', JSON.stringify(res.history));
                    localStorage.setItem('tp_interest_profile', JSON.stringify(res.profile));
                }
            })
            .catch(err => console.error("Cloud Sync error:", err));
        }

        <?php if (currentUser()): ?>
        // Tự động đồng bộ khi người dùng đã đăng nhập tải trang
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(window.syncUserBehavior, 1000); // delay nhẹ để không chặn trang chính
        });
        <?php endif; ?>

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
            // Theo dõi hoạt động để đề xuất gợi ý động
            trackUserActivity();
            registerEventTrackers();

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

        // Track User Activity dynamically for Smart AI suggestions using sliding queue (MAX 20 items)
        const MAX_HISTORY = 20;

        function pushUserActivity(type, value) {
            try {
                let history = [];
                const saved = localStorage.getItem('tp_user_history');
                if (saved) {
                    history = JSON.parse(saved);
                }
                
                // Tránh trùng lặp liên tiếp của cùng loại hoạt động
                if (history.length > 0 && history[0].type === type && JSON.stringify(history[0].value) === JSON.stringify(value)) {
                    return; 
                }
                
                // Thêm vào đầu queue
                history.unshift({
                    type: type,
                    value: value,
                    timestamp: Date.now()
                });
                
                // Xóa phần tử cũ hơn nếu vượt quá giới hạn
                if (history.length > MAX_HISTORY) {
                    history = history.slice(0, MAX_HISTORY);
                }
                
                localStorage.setItem('tp_user_history', JSON.stringify(history));
            } catch(e) {
                console.error("Error saving user history:", e);
            }
        }

        function trackUserActivity() {
            try {
                // 1. Nếu đang ở trang chi tiết sản phẩm
                const productNameEl = document.querySelector('.product-detail__info h1') || document.querySelector('.product-detail__name') || document.querySelector('.product-info h1') || document.querySelector('.product-detail-name');
                if (productNameEl) {
                    const name = productNameEl.innerText.trim();
                    const urlPath = window.location.pathname;
                    const slug = urlPath.substring(urlPath.lastIndexOf('/') + 1);
                    const brand = document.querySelector('.product-detail__brand')?.innerText?.trim() || '';
                    const priceText = document.querySelector('.price-now')?.innerText || '0';
                    const price = parseFloat(priceText.replace(/[^\d]/g, '')) || 0;
                    
                    window.tpBehaviorTracker.pushAction('product_detail', { name: name, slug: slug }, { brand: brand, price: price });
                }

                // 2. Nếu đang ở trang tìm kiếm / danh mục
                const urlParams = new URLSearchParams(window.location.search);
                const cat = urlParams.get('cat');
                const q = urlParams.get('q');
                if (cat) {
                    window.tpBehaviorTracker.pushAction('view_category', cat, { category: cat });
                }
                if (q) {
                    window.tpBehaviorTracker.pushAction('search', q, { keyword: q });
                }
            } catch(e) {
                console.error("Tracking error:", e);
            }
        }

        function registerEventTrackers() {
            try {
                // 1. Theo dõi thêm vào giỏ hàng & Wishlist qua form submit
                document.addEventListener('submit', (e) => {
                    const form = e.target;
                    const action = form.action || '';
                    if (action.includes('cart/add')) {
                        const productId = form.querySelector('input[name="product_id"]')?.value;
                        const card = form.closest('.product-card') || form.closest('.product-detail');
                        const name = card?.querySelector('.product-card__name, .product-detail__info h1')?.innerText?.trim() || ('Product #' + productId);
                        const brand = card?.querySelector('.product-detail__brand')?.innerText?.trim() || '';
                        const priceText = card?.querySelector('.price-now, .product-card__price-now')?.innerText || '0';
                        const price = parseFloat(priceText.replace(/[^\d]/g, '')) || 0;
                        window.tpBehaviorTracker.pushAction('add_cart', { id: productId, name: name }, { price: price, brand: brand });
                    }
                    if (action.includes('wishlist/add')) {
                        const productId = form.querySelector('input[name="product_id"]')?.value;
                        const card = form.closest('.product-card') || form.closest('.product-detail');
                        const name = card?.querySelector('.product-card__name, .product-detail__info h1')?.innerText?.trim() || ('Product #' + productId);
                        const brand = card?.querySelector('.product-detail__brand')?.innerText?.trim() || '';
                        window.tpBehaviorTracker.pushAction('wishlist', { id: productId, name: name }, { brand: brand });
                    }
                });

                // 2. Theo dõi click ảnh thumbnail
                document.addEventListener('click', (e) => {
                    if (e.target.closest('.product-detail__thumb')) {
                        const detailContainer = e.target.closest('.product-detail');
                        const name = detailContainer?.querySelector('.product-detail__info h1')?.innerText?.trim() || 'Product';
                        window.tpBehaviorTracker.pushAction('click_image', name);
                    }
                });

                // 3. Theo dõi đọc review
                document.addEventListener('click', (e) => {
                    const target = e.target;
                    if (target.closest('[href="#reviews"]') || target.closest('.review-tab-trigger')) {
                        const name = document.querySelector('.product-detail__info h1')?.innerText?.trim() || 'Product';
                        window.tpBehaviorTracker.pushAction('read_review', name);
                    }
                });

                // 4. Theo dõi đổi Sort
                document.addEventListener('change', (e) => {
                    if (e.target.id === 'sortBy') {
                        window.tpBehaviorTracker.pushAction('sort', e.target.value);
                    }
                });

                // 5. Theo dõi bộ lọc (Filter)
                document.addEventListener('click', (e) => {
                    if (e.target.closest('.price-apply-btn') || e.target.closest('.btn-filter-apply')) {
                        const activeFilter = localStorage.getItem('tp_last_category') || 'Filter';
                        window.tpBehaviorTracker.pushAction('filter', activeFilter);
                    }
                });
            } catch(e) {
                console.error("Event trackers registration failed:", e);
            }
        }

        function generateDynamicChips() {
            const chipsContainer = document.getElementById('tpChatbotChips');
            if (!chipsContainer) return;

            let chips = [];
            const path = window.location.pathname;

            // 1. Kiểm tra ngữ cảnh Trang hiện tại (Phase 7: Context-Aware Chips)
            if (path.includes('/product/')) {
                chips.push({
                    label: "Có đáng mua không?",
                    query: "Mẫu máy tính này có ưu nhược điểm gì chính và có đáng để xuống tiền không?"
                });
                chips.push({
                    label: "So sánh Lenovo LOQ",
                    query: "So sánh cấu hình máy này với Lenovo LOQ xem dòng nào ngon hơn và đáng mua hơn?"
                });
                chips.push({
                    label: "Chơi Wukong được không?",
                    query: "Mẫu máy này cấu hình có đủ chơi game Black Myth Wukong mượt không?"
                });
            } else if (path.includes('/cart') || path.includes('/checkout')) {
                chips.push({
                    label: "Mã giảm giá",
                    query: "TechPilot hiện đang có những chương trình khuyến mãi hay mã giảm giá nào không?"
                });
                chips.push({
                    label: "Freeship",
                    query: "Chính sách giao hàng và miễn phí vận chuyển của TechPilot như thế nào?"
                });
                chips.push({
                    label: "Hỗ trợ trả góp",
                    query: "TechPilot có hỗ trợ mua trả góp không và lãi suất như thế nào?"
                });
            } else {
                // Sử dụng Lịch sử cá nhân hóa (Personalized Prompt Chips)
                let history = [];
                try {
                    const saved = localStorage.getItem('tp_user_history');
                    if (saved) history = JSON.parse(saved);
                } catch(e) {}

                const viewedProducts = history.filter(h => h.type === 'product_detail').map(h => h.value);
                const viewedCategories = history.filter(h => h.type === 'view_category').map(h => h.value);
                const searchKeywords = history.filter(h => h.type === 'search').map(h => h.value);

                if (viewedProducts.length >= 2) {
                    const p1 = viewedProducts[0];
                    const p2 = viewedProducts[1];
                    if (p1.name !== p2.name) {
                        const s1 = p1.name.split(' ').slice(0, 2).join(' ');
                        const s2 = p2.name.split(' ').slice(0, 2).join(' ');
                        chips.push({
                            label: `So sánh ${s1} & ${s2}`,
                            query: `Hãy so sánh cấu hình chi tiết giữa "${p1.name}" và "${p2.name}"`
                        });
                    }
                }

                if (viewedProducts.length >= 1) {
                    const p = viewedProducts[0];
                    const shortName = p.name.split(' ').slice(0, 3).join(' ');
                    chips.push({
                        label: `Đánh giá ${shortName}`,
                        query: `Đánh giá hiệu năng và phân tích cấu hình của mẫu "${p.name}"`
                    });
                }

                if (viewedCategories.length >= 1) {
                    const cat = viewedCategories[0];
                    const catFormatted = cat.replace(/-/g, ' ');
                    chips.push({
                        label: `Tư vấn ${catFormatted}`,
                        query: `Tư vấn cho tôi mẫu máy tốt nhất thuộc danh mục "${catFormatted}"`
                    });
                }

                if (searchKeywords.length >= 1) {
                    const kw = searchKeywords[0];
                    chips.push({
                        label: `Hỏi về "${kw}"`,
                        query: `Tôi đang quan tâm đến "${kw}", tư vấn sản phẩm thích hợp`
                    });
                }
            }

            // Fallback gợi ý mặc định
            if (chips.length < 3) {
                chips.push({
                    label: "Tư vấn nhu cầu",
                    query: "Tôi muốn tư vấn chọn Laptop theo nhu cầu"
                });
            }
            if (chips.length < 3) {
                chips.push({
                    label: "AI So sánh",
                    query: "Tôi muốn so sánh cấu hình các máy"
                });
            }
            if (chips.length < 3) {
                chips.push({
                    label: "Chọn theo ngân sách",
                    query: "Tôi muốn tìm laptop theo khoảng giá cụ thể"
                });
            }

            // Lọc bỏ những Prompt Chips đã bị tắt (dismissed) trước đó
            let dismissed = [];
            try {
                const saved = localStorage.getItem('tp_dismissed_chips');
                if (saved) dismissed = JSON.parse(saved);
            } catch(e) {}

            const activeChips = chips.filter(c => !dismissed.includes(c.label));

            // Render active chips kèm nút tắt "x"
            chipsContainer.innerHTML = '';
            activeChips.slice(0, 3).forEach(chip => {
                const wrapper = document.createElement('div');
                wrapper.className = 'tp-chip-wrapper';
                wrapper.style.display = 'inline-flex';
                wrapper.style.alignItems = 'center';
                wrapper.style.background = 'rgba(255, 255, 255, 0.1)';
                wrapper.style.border = '1px solid rgba(255, 255, 255, 0.2)';
                wrapper.style.borderRadius = '20px';
                wrapper.style.margin = '4px';
                wrapper.style.padding = '4px 10px';
                wrapper.style.cursor = 'pointer';
                wrapper.style.fontSize = '12px';
                wrapper.style.backdropFilter = 'blur(10px)';
                wrapper.style.color = '#FFFFFF';
                wrapper.style.transition = 'all 0.2s';

                const textSpan = document.createElement('span');
                textSpan.innerText = chip.label;
                textSpan.style.marginRight = '6px';
                textSpan.onclick = () => askChip(chip.query);

                const closeBtn = document.createElement('span');
                closeBtn.innerHTML = '&times;';
                closeBtn.style.fontWeight = 'bold';
                closeBtn.style.color = 'rgba(255,255,255,0.6)';
                closeBtn.style.cursor = 'pointer';
                closeBtn.style.fontSize = '14px';
                closeBtn.style.lineHeight = '1';
                closeBtn.onmouseover = () => closeBtn.style.color = '#FF4D4D';
                closeBtn.onmouseout = () => closeBtn.style.color = 'rgba(255,255,255,0.6)';
                closeBtn.onclick = (e) => {
                    e.stopPropagation();
                    dismissed.push(chip.label);
                    localStorage.setItem('tp_dismissed_chips', JSON.stringify(dismissed));
                    window.tpBehaviorTracker.pushAction('dismiss_chip', chip.label);
                    generateDynamicChips();
                };

                wrapper.appendChild(textSpan);
                wrapper.appendChild(closeBtn);
                chipsContainer.appendChild(wrapper);
            });
        }

        function loadChatHistory() {
            try {
                const history = JSON.parse(sessionStorage.getItem('tp_chat_history') || '[]');
                const msgBox = document.getElementById('tpChatbotMessages');
                if (!msgBox) return;

                if (msgBox.children.length > 0) return;

                if (history.length === 0) {
                    const hour = new Date().getHours();
                    const month = new Date().getMonth() + 1;
                    let greeting = "Xin chào! 👋 Tôi là trợ lý ảo **TechPilot AI**.";

                    if (hour >= 22) {
                        greeting = "Chào bạn buổi tối muộn! 🌙 Bạn vẫn đang online tìm kiếm cấu hình máy tốt sao? Mình luôn sẵn sàng tư vấn giúp bạn nhé!";
                    } else if (hour >= 18) {
                        greeting = "Chào buổi tối! 🌆 Đã đến lúc nghỉ ngơi thư giãn, bạn đang tìm cấu hình để làm việc hay chiến game thế?";
                    } else if (hour >= 12) {
                        greeting = "Chào buổi trưa/chiều! ☀️ Chúc bạn ngày làm việc hiệu quả. Hôm nay mình có thể giúp gì cho bạn nhỉ?";
                    } else {
                        greeting = "Chào buổi sáng! 🌅 Ngày mới năng lượng nhé. Hãy cho mình biết bạn đang tìm mẫu laptop hay PC như thế nào?";
                    }

                    if (month === 8 || month === 9) {
                        greeting += " 🎓 Đặc biệt, chương trình **Back To School** đang bùng nổ tại cửa hàng! Rất nhiều laptop học tập cực chất giá ưu đãi đang chờ bạn khám phá đấy!";
                    }

                    renderBotMessage(greeting, true, true);
                    renderInitialActions();
                    return;
                }

                history.forEach(msg => {
                    if (msg.sender === 'user') {
                        renderUserMessage(msg.content, false);
                    } else {
                        if (msg.type === 'recommendations') {
                            renderBotMessage(msg.content, false, false);
                            renderRecommendations(msg.recommendations, false);
                        } else {
                            renderBotMessage(msg.content, false, false);
                        }
                    }
                });
            } catch(e) {
                console.error("Error loading chat history:", e);
            }
        }

        // Toggle Chat Window
        function toggleChatbot() {
            const windowEl = document.getElementById('tpChatbotWindow');
            const launcherEl = document.getElementById('tpChatbotLauncher');
            chatbotOpen = !chatbotOpen;
            if (chatbotOpen) {
                windowEl.classList.add('is-open');
                if (launcherEl) launcherEl.style.display = 'none';
                
                // Khởi tạo gợi ý động dựa trên lịch sử xem/tìm kiếm của user
                generateDynamicChips();

                // Tải lại lịch sử cuộc trò chuyện (Phase 6: Conversation Retention)
                loadChatHistory();
            } else {
                windowEl.classList.remove('is-open');
                if (launcherEl) launcherEl.style.display = 'flex';
            }
        }



        window.feedbackMessage = function(btn, isPositive, text) {
            const icon = btn.querySelector('i');
            if (isPositive) {
                icon.className = 'fa-solid fa-thumbs-up';
                btn.style.color = '#10B981';
                window.tpBehaviorTracker.pushAction('recommend_success', text.substring(0, 100));
            } else {
                icon.className = 'fa-solid fa-thumbs-down';
                btn.style.color = '#EF4444';
                window.tpBehaviorTracker.pushAction('recommend_failed', text.substring(0, 100));
            }
            const parent = btn.parentElement;
            parent.querySelectorAll('.tp-feedback-btn').forEach(b => {
                b.disabled = true;
                b.style.cursor = 'default';
                if (b !== btn) b.style.opacity = '0.3';
            });
        }

        window.askChip = function(text) {
            const input = document.getElementById('tpChatbotInput');
            if (input) {
                input.value = text;
                sendChatbotMessage();
            }
        }

        // Renders
        function renderBotMessage(html, useTypewriter = true, shouldSave = true) {
            const msgBox = document.getElementById('tpChatbotMessages');
            const wrapper = document.createElement('div');
            wrapper.className = 'tp-message bot';
            
            const tempDiv = document.createElement('div');
            const formatted = formatMarkdownText(html);
            tempDiv.innerHTML = formatted;
            const plainText = tempDiv.textContent || tempDiv.innerText || "";
            const sanitizedText = plainText.replace(/"/g, '&quot;').replace(/'/g, "\\'").replace(/\n/g, ' ');
            
            if (shouldSave) {
                saveToChatHistory('bot', html, 'text');
            }

            wrapper.innerHTML = `
                <div class="tp-message-avatar">
                    <img src="<?= url('assets/images/chatbot-avatar.png') ? alt="TechPilot Asset">" alt="Bot Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div style="display: flex; flex-direction: column; width: 100%;">
                    <div class="tp-message-content"></div>
                    <div style="display: flex; gap: 8px; align-items: center; margin-top: 4px;">
                        <button type="button" class="tp-feedback-btn thumbs-up" onclick="feedbackMessage(this, true, '${sanitizedText.trim()}')" title="Hữu ích" style="border: none; background: none; color: rgba(255,255,255,0.5); cursor: pointer; font-size: 12px; display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 4px; transition: all 0.2s;">
                            <i class="fa-regular fa-thumbs-up"></i>
                        </button>
                        <button type="button" class="tp-feedback-btn thumbs-down" onclick="feedbackMessage(this, false, '${sanitizedText.trim()}')" title="Không hữu ích" style="border: none; background: none; color: rgba(255,255,255,0.5); cursor: pointer; font-size: 12px; display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 4px; transition: all 0.2s;">
                            <i class="fa-regular fa-thumbs-down"></i>
                        </button>
                    </div>
                </div>
            `;
            msgBox.appendChild(wrapper);
            
            const contentEl = wrapper.querySelector('.tp-message-content');
            
            if (useTypewriter && html.length > 10) {
                const words = html.split(' ');
                let currentWordIndex = 0;
                contentEl.innerHTML = "";
                
                function typeWord() {
                    if (currentWordIndex < words.length) {
                        contentEl.innerHTML = formatMarkdownText(words.slice(0, currentWordIndex + 1).join(' '));
                        currentWordIndex++;
                        scrollChatToBottom();
                        setTimeout(typeWord, 40); // natural fast reading pace
                    } else {
                        scrollChatToBottom();
                    }
                }
                typeWord();
            } else {
                contentEl.innerHTML = formatted;
                scrollChatToBottom();
            }
            
            return wrapper;
        }

        function saveToChatHistory(sender, content, type = 'text', recommendations = null) {
            try {
                let history = JSON.parse(sessionStorage.getItem('tp_chat_history') || '[]');
                history.push({
                    sender: sender,
                    content: content,
                    type: type,
                    recommendations: recommendations,
                    timestamp: Date.now()
                });
                if (history.length > 50) {
                    history = history.slice(history.length - 50);
                }
                sessionStorage.setItem('tp_chat_history', JSON.stringify(history));
            } catch(e) {
                console.error("Error saving chat history:", e);
            }
        }

        function renderUserMessage(text, shouldSave = true) {
            const msgBox = document.getElementById('tpChatbotMessages');
            const wrapper = document.createElement('div');
            wrapper.className = 'tp-message user';
            wrapper.innerHTML = `
                <div class="tp-message-content">${formatMarkdownText(text)}</div>
            `;
            msgBox.appendChild(wrapper);
            scrollChatToBottom();
            
            if (shouldSave) {
                saveToChatHistory('user', text);
            }
        }

        function renderTypingIndicator() {
            const msgBox = document.getElementById('tpChatbotMessages');
            const wrapper = document.createElement('div');
            wrapper.className = 'tp-message bot tp-typing-wrapper';
            wrapper.innerHTML = `
                <div class="tp-message-avatar"><img src="<?= url('assets/images/chatbot-avatar.png') ? alt="TechPilot Asset">" alt="Bot Avatar" style="width: 100%; height: 100%; object-fit: cover;"></div>
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
                <button type="button" class="tp-action-btn" onclick="sendNaturalQuery('Cửa hàng có những dòng sản phẩm nổi bật nào?')">
                    <i class="fa-solid fa-circle-question"></i>
                    <span>Hỏi về sản phẩm</span>
                </button>
                <button type="button" class="tp-action-btn" onclick="sendNaturalQuery('Chính sách bảo hành tại TechPilot thế nào?')">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Chính sách bảo hành</span>
                </button>
                <button type="button" class="tp-action-btn" onclick="sendNaturalQuery('Cửa hàng có hỗ trợ trả góp không?')">
                    <i class="fa-solid fa-credit-card"></i>
                    <span>Trả góp 0%</span>
                </button>
                <button type="button" class="tp-action-btn" onclick="sendNaturalQuery('Địa chỉ cửa hàng ở đâu và mấy giờ mở cửa?')">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Địa chỉ & Giờ mở cửa</span>
                </button>
            `;
            msgBox.appendChild(grid);
            scrollChatToBottom();
        }

        function renderNavigationButton(label, navUrl) {
            const msgBox = document.getElementById('tpChatbotMessages');
            const btnWrap = document.createElement('div');
            btnWrap.className = 'tp-options-list';
            btnWrap.style.marginTop = '8px';
            btnWrap.innerHTML = `
                <a href="${navUrl}" class="tp-option-choice" style="text-decoration:none; display:inline-flex; align-items:center; gap:6px; background-color:var(--primary); color:#FFFFFF; border-radius:8px; padding:10px 16px; font-weight:700;">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> ${label}
                </a>
            `;
            msgBox.appendChild(btnWrap);
            scrollChatToBottom();
        }

        // ==========================================
        // GỬI TIN NHẮN TỰ NHIÊN (CHAT INPUT)
        // ==========================================
        function handleChatbotKey(event) {
            if (event.key === 'Enter') {
                sendChatbotMessage();
            }
        }

        function sendNaturalQuery(text) {
            document.getElementById('tpChatbotInput').value = text;
            sendChatbotMessage();
        }

        let isSubmittingChat = false;
        function sendChatbotMessage() {
            const inputEl = document.getElementById('tpChatbotInput');
            const text = inputEl.value.trim();
            if (text === '' || isSubmittingChat) return;

            inputEl.value = '';
            renderUserMessage(text);

            const lower = text.toLowerCase().trim();
            if (lower === 'reset' || lower === 'từ đầu' || lower === 'tu dau') {
                sessionStorage.removeItem('tp_chat_history');
                const msgBox = document.getElementById('tpChatbotMessages');
                if (msgBox) msgBox.innerHTML = '';
            }

            requestChatbot(text);
        }

        function requestChatbot(text) {
            if (isSubmittingChat) return;
            isSubmittingChat = true;

            const sendBtn = document.querySelector('.tp-chatbot-send');
            if (sendBtn) sendBtn.disabled = true;

            const indicator = renderTypingIndicator();

            let url = '<?= url("chatbot/query?q=") ?>' + encodeURIComponent(text);

            let productIdParam = '';
            if (window.tp_product_id) {
                productIdParam = window.tp_product_id;
            } else {
                const pMatch = window.location.pathname.match(/\/product\/(?:detail\/)?(\d+)/i);
                if (pMatch) productIdParam = pMatch[1];
            }
            if (productIdParam) url += '&product_id=' + encodeURIComponent(productIdParam);

            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 20000);

            fetch(url, { signal: controller.signal })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        renderBotMessage(res.message || '');
                        if (res.type === 'navigation' && res.action && res.action.url) {
                            renderNavigationButton(res.action.label || 'Mở trang', res.action.url);
                        }
                    } else {
                        const errObj = res.error || {};
                        renderBotMessage(errObj.message || res.message || "🤖 Trợ lý AI đang tạm thời không khả dụng. Vui lòng thử lại sau.");
                    }
                })
                .catch(err => {
                    renderBotMessage("🤖 Trợ lý AI đang tạm thời không khả dụng. Vui lòng thử lại sau.");
                })
                .finally(() => {
                    clearTimeout(timeoutId);
                    removeTypingIndicator(indicator);
                    isSubmittingChat = false;
                    if (sendBtn) sendBtn.disabled = false;
                });
        }
    </script>
    <script type="module" src="<?= url('assets/js/brandLogos.js') ?>"></script>
</body>

</html>
