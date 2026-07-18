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
                <i class="fa-solid fa-credit-card"></i>
                <h5>Thanh toán linh hoạt</h5>
                <p>Hỗ trợ thẻ tín dụng, chuyển khoản, ví điện tử, Trả góp 0%</p>
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
                    <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#"><i class="fa-brands fa-youtube"></i></a>
                    <a href="#"><i class="fa-brands fa-tiktok"></i></a>
                    <a href="#"><i class="fa-brands fa-instagram"></i></a>
                </div>
            </div>
            
            <div class="footer-col">
                <h4>Về chúng tôi</h4>
                <a href="#">Giới thiệu TechPilot</a>
                <a href="#">Tuyển dụng nhân viên</a>
                <a href="#">Liên hệ hợp tác</a>
                <a href="#">Hệ thống cửa hàng</a>
                <a href="#">Chính sách bảo mật</a>
            </div>
            
            <div class="footer-col">
                <h4>Hỗ trợ khách hàng</h4>
                <a href="#">Hướng dẫn mua hàng online</a>
                <a href="#">Chính sách đổi trả sản phẩm</a>
                <a href="#">Chính sách bảo hành sửa chữa</a>
                <a href="#">Tra cứu hóa đơn điện tử</a>
                <a href="#">Gửi yêu cầu hỗ trợ kỹ thuật</a>
            </div>
            
            <div class="footer-col">
                <h4>Đăng ký nhận ưu đãi</h4>
                <p style="font-size: 13px; color: #94A3B8; margin-bottom: 12px;">Đăng ký để nhận những thông báo khuyến mãi công nghệ sớm nhất.</p>
                <form class="newsletter-form" onsubmit="return false;">
                    <input type="email" placeholder="Email của bạn..." required>
                    <button type="submit">Đăng ký</button>
                </form>
                <div class="payment-icons" style="margin-top: 24px;">
                    <i class="fa-brands fa-cc-visa" title="Visa"></i>
                    <i class="fa-brands fa-cc-mastercard" title="Mastercard"></i>
                    <i class="fa-brands fa-cc-jcb" title="JCB"></i>
                    <i class="fa-solid fa-qrcode" title="QR Pay"></i>
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
            <button type="button" class="fixed-buy-bar__btn" onclick="buyNowSubmit()"><i class="fa-solid fa-cart-plus"></i> Thêm giỏ</button>
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

    <script src="<?= url('assets/js/main.js?v=7.0') ?>"></script>
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
</body>

</html>