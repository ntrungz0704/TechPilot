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
                <img src="<?= e(productImageUrl($product['image'] ?? '')) ?>" alt="thumb">
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
</body>

</html>