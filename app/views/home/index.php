<?php
$categories = $categories ?? [];
$flashSale = $flashSale ?? [];
$laptopGaming = $laptopGaming ?? [];
$laptopVanPhong = $laptopVanPhong ?? [];
$pcBuildSan = $pcBuildSan ?? [];
$pcLinhKien = $pcLinhKien ?? [];
$gamingGear = $gamingGear ?? [];
$monHinh = $monHinh ?? [];
$apple = $apple ?? [];

// Banners
$heroBanners = $heroBanners ?? [];
$sidebarBanners = $sidebarBanners ?? [];
$midBanners = $midBanners ?? [];
$longBanners = $longBanners ?? [];

// Tabs Best Seller
$bestSellersLaptop = $bestSellersLaptop ?? [];
$bestSellersGaming = $bestSellersGaming ?? [];
$bestSellersComponents = $bestSellersComponents ?? [];
$bestSellersMonitor = $bestSellersMonitor ?? [];
$bestSellersAccessories = $bestSellersAccessories ?? [];

// Layout Hot, New, Promo
$featuredProducts = $featuredProducts ?? [];
$newProducts = $newProducts ?? [];
$promoProducts = $promoProducts ?? [];

// Khác
$brands = $brands ?? [];
$posts = $posts ?? [];
$reviews = $reviews ?? [];
?>

<!-- ===== 4. HERO SECTION ===== -->
<div class="home-page-wrapper">
<section class="container hero-section">
    <!-- Left: Vertical Category Menu -->
    <div class="hero-section__left" id="heroCategorySlot">
        <?php 
        $isStatic = true; 
        require ROOT_PATH . '/app/views/layouts/partials/category-mega-menu.php'; 
        ?>
    </div>

    <!-- Center: Large Hero Banner Carousel -->
    <div class="hero-section__center" id="heroCarousel">
        <div class="carousel-slide is-active" style="background-image: linear-gradient(90deg, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.5) 50%, rgba(0, 0, 0, 0) 100%), url('<?= url('assets/images/rog-banner-bg.jpg') ?>');">
            <div class="carousel-slide__content">
                <span class="carousel-slide__tag">Sức mạnh vượt trội</span>
                <h2>ROG ZEPHYRUS G16</h2>
                <p>Hiệu năng đỉnh cao cho game thủ &amp; creator</p>
                <ul class="carousel-slide__specs">
                    <li><i class="fa-solid fa-circle-check"></i> Intel® Core™ Ultra 9</li>
                    <li><i class="fa-solid fa-circle-check"></i> NVIDIA® GeForce RTX™ 4070</li>
                    <li><i class="fa-solid fa-circle-check"></i> Màn hình OLED 2.5K 240Hz</li>
                    <li><i class="fa-solid fa-circle-check"></i> RAM LPDDR5X 32GB</li>
                </ul>
                <div class="carousel-slide__price">
                    Chỉ từ <strong>39.990.000đ</strong>
                </div>
                <a href="<?= url('product/detail/asus-rog-zephyrus-g16') ?>" class="btn btn--light">Mua ngay <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>
        
        <!-- Slide 2: MacBook Pro -->
        <div class="carousel-slide" style="background-image: linear-gradient(90deg, rgba(0, 0, 0, 0.85) 0%, rgba(0, 0, 0, 0.5) 45%, rgba(0, 0, 0, 0) 100%), url('<?= url('assets/images/apple-banner-bg.jpg') ?>');">
            <div class="carousel-slide__content">
                <span class="carousel-slide__tag">Cực phẩm Apple</span>
                <h2>MacBook Pro 16 M3 Max</h2>
                <p>Quái vật hiệu năng dành cho dân chuyên nghiệp</p>
                <ul class="carousel-slide__specs">
                    <li><i class="fa-solid fa-circle-check"></i> Chip Apple M3 Max 14-core</li>
                    <li><i class="fa-solid fa-circle-check"></i> GPU 30-core cực mạnh</li>
                    <li><i class="fa-solid fa-circle-check"></i> Màn hình Liquid Retina XDR</li>
                    <li><i class="fa-solid fa-circle-check"></i> 36GB Unified Memory, 1TB SSD</li>
                </ul>
                <div class="carousel-slide__price">
                    Chỉ từ <strong>89.990.000đ</strong>
                </div>
                <a href="<?= url('product/detail/macbook-pro-16-m3-max') ?>" class="btn btn--light">Mua ngay <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- Slide 3: PC Gaming Ultra -->
        <div class="carousel-slide" style="background-image: linear-gradient(90deg, rgba(0, 0, 0, 0.9) 0%, rgba(0, 0, 0, 0.6) 50%, rgba(0, 0, 0, 0) 100%), url('<?= url('assets/images/banner-1.jpg') ?>');">
            <div class="carousel-slide__content">
                <span class="carousel-slide__tag">Chiến thần hiệu năng</span>
                <h2>PC Gaming Ultra Max</h2>
                <p>Cỗ máy hủy diệt mọi tựa game AAA ở thiết lập cao nhất</p>
                <ul class="carousel-slide__specs">
                    <li><i class="fa-solid fa-circle-check"></i> CPU Intel Core i9-14900K</li>
                    <li><i class="fa-solid fa-circle-check"></i> VGA NVIDIA RTX 4090 24GB</li>
                    <li><i class="fa-solid fa-circle-check"></i> RAM 64GB DDR5 6000MHz</li>
                    <li><i class="fa-solid fa-circle-check"></i> Tản nhiệt nước Custom</li>
                </ul>
                <div class="carousel-slide__price">
                    Chỉ từ <strong>125.000.000đ</strong>
                </div>
                <a href="<?= url('build-pc') ?>" class="btn btn--light">Build ngay <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- Slide 4: Monitor Odyssey G9 -->
        <div class="carousel-slide" style="background-image: linear-gradient(90deg, rgba(0, 0, 0, 0.85) 0%, rgba(0, 0, 0, 0.5) 50%, rgba(0, 0, 0, 0) 100%), url('<?= url('assets/images/banner-3.jpg') ?>');">
            <div class="carousel-slide__content">
                <span class="carousel-slide__tag">Trải nghiệm vô cực</span>
                <h2>Odyssey OLED G9</h2>
                <p>Màn hình cong 49 inch chuẩn điện ảnh cho đắm chìm tuyệt đối</p>
                <ul class="carousel-slide__specs">
                    <li><i class="fa-solid fa-circle-check"></i> Tấm nền QD-OLED siêu nét</li>
                    <li><i class="fa-solid fa-circle-check"></i> Tần số quét 240Hz</li>
                    <li><i class="fa-solid fa-circle-check"></i> Tốc độ phản hồi 0.03ms</li>
                    <li><i class="fa-solid fa-circle-check"></i> Tỷ lệ siêu rộng 32:9</li>
                </ul>
                <div class="carousel-slide__price">
                    Chỉ từ <strong>34.500.000đ</strong>
                </div>
                <a href="<?= url('product/detail/samsung-odyssey-oled-g9') ?>" class="btn btn--light">Khám phá <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- Slide 5: RTX 50 SERIES (Coming soon) -->
        <div class="carousel-slide" style="background-image: linear-gradient(90deg, rgba(0, 0, 0, 0.85) 0%, rgba(0, 0, 0, 0.6) 45%, rgba(0, 0, 0, 0) 100%), url('<?= url('assets/images/banner-rtx-bg.jpg') ?>');">
            <div class="carousel-slide__content">
                <span class="carousel-slide__tag">Sắp ra mắt</span>
                <h2>NVIDIA RTX 50 SERIES</h2>
                <p>Sẵn sàng cho kỷ nguyên đồ họa thế hệ mới</p>
                <ul class="carousel-slide__specs">
                    <li><i class="fa-solid fa-circle-check"></i> Kiến trúc Blackwell 4nm</li>
                    <li><i class="fa-solid fa-circle-check"></i> Bộ nhớ GDDR7 siêu tốc</li>
                    <li><i class="fa-solid fa-circle-check"></i> Tăng tốc AI gấp 2 lần</li>
                    <li><i class="fa-solid fa-circle-check"></i> DLSS 4 tối ưu hóa hiệu năng</li>
                </ul>
                <div class="carousel-slide__price">
                    Nhận thông tin sớm nhất
                </div>
                <a href="<?= url('auth/register') ?>" class="btn btn--light">Đăng ký ngay <i class="fa-solid fa-envelope"></i></a>
            </div>
        </div>

        <div class="carousel-controls">
            <div class="carousel-dot is-active" data-index="0"></div>
            <div class="carousel-dot" data-index="1"></div>
            <div class="carousel-dot" data-index="2"></div>
            <div class="carousel-dot" data-index="3"></div>
            <div class="carousel-dot" data-index="4"></div>
        </div>
    </div>

    <!-- Right: 3 Promotion Cards -->
    <div class="hero-section__right">
        <div class="promo-card promo-card--dark">
            <div>
                <h4>BUILD PC THEO YÊU CẦU</h4>
                <p>Tối ưu cấu hình - Cân mọi ngân sách</p>
            </div>
            <a href="<?= url('build-pc') ?>" class="btn btn--outline-light btn--sm">Xem ngay</a>
        </div>
        
        <div class="promo-card promo-card--blue">
            <div>
                <h4>MUA NGAY - TRẢ SAU</h4>
                <p>Nhận hàng, kiểm tra rồi thanh toán (COD)</p>
            </div>
            <span class="promo-card__number"><i class="fa-solid fa-truck-fast"></i></span>
            <a href="<?= url('home/search') ?>" class="btn btn--sm">Mua ngay</a>
        </div>
        
        <div class="promo-card">
            <div>
                <h4>THU CŨ ĐỔI MỚI MÁY CŨ</h4>
                <p>Trợ giá lên tới 6 triệu đồng</p>
            </div>
            <a href="<?= url('thu-cu-doi-moi') ?>" class="btn btn--sm">Xem chi tiết</a>
        </div>
    </div>
</section>

<!-- ===== 5. SERVICE FEATURES ===== -->
<section class="container">
    <div class="features-bar">
        <div class="feature-item">
            <i class="fa-solid fa-truck-fast"></i>
            <span>Miễn phí giao hàng<br><small>Đơn hàng từ 500k</small></span>
        </div>
        <div class="feature-item">
            <i class="fa-solid fa-shield-halved"></i>
            <span>Bảo hành chính hãng<br><small>Cam kết 100%</small></span>
        </div>
        <div class="feature-item">
            <i class="fa-solid fa-rotate-left"></i>
            <span>Đổi trả dễ dàng<br><small>Trong 7 ngày đầu</small></span>
        </div>
        <div class="feature-item">
            <i class="fa-solid fa-money-bill-wave"></i>
            <span>Thanh toán COD<br><small>Tiền mặt khi nhận hàng</small></span>
        </div>
        <div class="feature-item">
            <i class="fa-solid fa-headset"></i>
            <span>Hỗ trợ kỹ thuật 24/7<br><small>Hotline: 1900 1234</small></span>
        </div>
    </div>
</section>

<?php if (!empty($flashSale)): ?>
<!-- ===== 6. FLASH SALE ===== -->
<section class="flash-sale-section container">
    <header class="flash-sale-header">
        <div class="flash-sale-heading">
            <h2 class="flash-sale-title">
                <span class="flash-sale-icon" aria-hidden="true">⚡</span>
                FLASH SALE
            </h2>

            <span class="flash-sale-ending-text" id="flashEndingText">Kết thúc sau</span>

            <div
                class="flash-sale-countdown countdown"
                id="flashCountdown"
                data-end-time="<?= (!empty($flashSale) && isset($flashSale[0]['end_time'])) ? e($flashSale[0]['end_time']) : '' ?>"
                aria-label="Thời gian Flash Sale còn lại"
                aria-live="polite"
            >
                <div class="countdown-unit cd-days" style="display: none;">
                    <strong id="cd-d" data-countdown-days>00</strong>
                    <span>NGÀY</span>
                </div>
                
                <span class="countdown-separator cd-days-sep" style="display: none;">:</span>

                <div class="countdown-unit">
                    <strong id="cd-h" data-countdown-hours>00</strong>
                    <span>GIỜ</span>
                </div>

                <span class="countdown-separator">:</span>

                <div class="countdown-unit">
                    <strong id="cd-m" data-countdown-minutes>00</strong>
                    <span>PHÚT</span>
                </div>

                <span class="countdown-separator">:</span>

                <div class="countdown-unit">
                    <strong id="cd-s" data-countdown-seconds>00</strong>
                    <span>GIÂY</span>
                </div>
            </div>
        </div>

        <a class="flash-sale-view-all" href="<?= url('home/search?flash=1') ?>">
            Xem tất cả
            <span aria-hidden="true">→</span>
        </a>
    </header>

    <div style="position: relative;" class="flash-sale-wrapper">
        <!-- Navigation Arrows -->
        <button type="button" class="flash-arrow flash-prev" id="flashPrevBtn" aria-label="Sản phẩm trước" style="position: absolute; left: -18px; top: 50%; transform: translateY(-50%); z-index: 10; width: 38px; height: 38px; border-radius: 50%; border: 1px solid var(--border); background: #FFFFFF; color: var(--text-primary); font-size: 16px; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.12); display: flex; align-items: center; justify-content: center; transition: all 0.2s ease;">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <button type="button" class="flash-arrow flash-next" id="flashNextBtn" aria-label="Sản phẩm tiếp" style="position: absolute; right: -18px; top: 50%; transform: translateY(-50%); z-index: 10; width: 38px; height: 38px; border-radius: 50%; border: 1px solid var(--border); background: #FFFFFF; color: var(--text-primary); font-size: 16px; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.12); display: flex; align-items: center; justify-content: center; transition: all 0.2s ease;">
            <i class="fa-solid fa-chevron-right"></i>
        </button>

        <div id="flashSaleTrack" class="flash-sale-products" style="display: flex; gap: 16px; overflow-x: auto; scroll-behavior: smooth; scrollbar-width: none; -ms-overflow-style: none; padding: 4px 0;">
            <?php foreach ($flashSale as $p): ?>
                <div style="flex: 0 0 calc((100% - 80px) / 6); min-width: 190px;">
                    <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const track = document.getElementById('flashSaleTrack');
    const prevBtn = document.getElementById('flashPrevBtn');
    const nextBtn = document.getElementById('flashNextBtn');
    if (!track || !prevBtn || !nextBtn) return;

    const scrollStep = 220;
    let autoPlayTimer = null;

    // Tính chính xác chiều rộng 1 card (bao gồm gap)
    function getCardScrollWidth() {
        const firstCard = track.children[0];
        if (!firstCard) return scrollStep;
        const cardRect = firstCard.getBoundingClientRect();
        const gap = parseFloat(getComputedStyle(track).gap) || 16;
        return cardRect.width + gap;
    }

    function scrollNext() {
        if (track.scrollLeft + track.clientWidth >= track.scrollWidth - 10) {
            track.scrollTo({ left: 0, behavior: 'smooth' });
        } else {
            track.scrollBy({ left: getCardScrollWidth(), behavior: 'smooth' });
        }
    }

    function scrollPrev() {
        if (track.scrollLeft <= 10) {
            track.scrollTo({ left: track.scrollWidth, behavior: 'smooth' });
        } else {
            track.scrollBy({ left: -getCardScrollWidth(), behavior: 'smooth' });
        }
    }

    nextBtn.addEventListener('click', function() {
        scrollNext();
        resetAutoPlay();
    });

    prevBtn.addEventListener('click', function() {
        scrollPrev();
        resetAutoPlay();
    });

    function startAutoPlay() {
        autoPlayTimer = setInterval(scrollNext, 3500);
    }

    function resetAutoPlay() {
        clearInterval(autoPlayTimer);
        startAutoPlay();
    }

    startAutoPlay();
    track.addEventListener('mouseenter', () => clearInterval(autoPlayTimer));
    track.addEventListener('mouseleave', startAutoPlay);
});
</script>
<?php endif; ?>

<?php if (!empty($featuredProducts)): ?>
<!-- ===== SẢN PHẨM NỔI BẬT (HOT) ===== -->
<section class="container section">
    <div class="section__head">
        <h2><i class="fa-solid fa-fire" style="color: #ff4757; margin-right: 8px;"></i>Sản phẩm nổi bật</h2>
        <a href="<?= url('home/search?sort=popular') ?>" class="section__more">Xem tất cả <i class="fa-solid fa-chevron-right"></i></a>
    </div>
    <div class="section-slider-wrapper">
        <button type="button" class="product-slider-arrow prev" aria-label="Sản phẩm trước"><i class="fa-solid fa-chevron-left"></i></button>
        <button type="button" class="product-slider-arrow next" aria-label="Sản phẩm tiếp"><i class="fa-solid fa-chevron-right"></i></button>
        <div class="product-slider-track">
            <?php foreach ($featuredProducts as $p): ?>
                <div>
                    <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===== 7. POPULAR CATEGORIES ===== -->
<section class="container section">
    <div class="section__head">
        <h2>Danh mục sản phẩm</h2>
        <a href="<?= url('home/search') ?>" class="section__more">Xem tất cả <i class="fa-solid fa-chevron-right"></i></a>
    </div>
    <?php
    $popularCategoriesList = [
        ['name' => 'Laptop', 'slug' => 'laptop', 'cat' => 'laptop'],
        ['name' => 'PC', 'slug' => 'pc', 'cat' => 'pc'],
        ['name' => 'Màn hình', 'slug' => 'man-hinh', 'cat' => 'monitor'],
        ['name' => 'Mainboard', 'slug' => 'mainboard', 'cat' => 'mainboard'],
        ['name' => 'CPU', 'slug' => 'cpu', 'cat' => 'cpu'],
        ['name' => 'VGA', 'slug' => 'vga', 'cat' => 'vga'],
        ['name' => 'RAM', 'slug' => 'ram', 'cat' => 'ram'],
        ['name' => 'Ổ cứng', 'slug' => 'o-cung', 'cat' => 'storage'],
        ['name' => 'Case', 'slug' => 'case', 'cat' => 'case'],
        ['name' => 'Tản nhiệt', 'slug' => 'tan-nhiet', 'cat' => 'cooling'],
        ['name' => 'Nguồn', 'slug' => 'nguon', 'cat' => 'psu'],
        ['name' => 'Bàn phím', 'slug' => 'ban-phim', 'cat' => 'keyboard'],
        ['name' => 'Chuột', 'slug' => 'chuot', 'cat' => 'mouse'],
        ['name' => 'Ghế', 'slug' => 'ghe', 'cat' => 'chair'],
        ['name' => 'Tai nghe', 'slug' => 'tai-nghe', 'cat' => 'headset'],
        ['name' => 'Loa', 'slug' => 'loa', 'cat' => 'speaker'],
        ['name' => 'Console', 'slug' => 'console', 'cat' => 'console'],
        ['name' => 'Phụ kiện', 'slug' => 'phu-kien', 'cat' => 'accessories'],
        ['name' => 'Thiết bị VP', 'slug' => 'thiet-bi-vp', 'cat' => 'office-equipment'],
        ['name' => 'Sạc DP', 'slug' => 'sac-dp', 'cat' => 'power-bank']
    ];
    ?>
    <div class="category-strip">
        <?php foreach ($popularCategoriesList as $item): ?>
            <a href="<?= url('home/search?cat=' . urlencode($item['cat'])) ?>" class="category-strip__item">
                <div class="category-strip__icon">
                    <img src="<?= url('assets/images/categories/category-' . e($item['cat']) . '.png') ?>" alt="<?= e($item['name']) ?>" onerror="this.outerHTML='<i class=\'fa-solid fa-tag\' style=\'font-size: 24px; color: var(--primary);\'></i>'">
                </div>
                <span><?= e($item['name']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===== BANNER TRUNG GIAN RTX 50 ===== -->
<section class="container section">
    <div class="promo-banner" style="background-image: url('<?= url('assets/images/rtx-banner.jpg') ?>');">
        <div class="promo-banner__content">
            <span class="promo-banner__tag">SẮP RA MẮT</span>
            <h3>NVIDIA GeForce RTX 50 Series</h3>
            <p>Sức mạnh tối thượng từ kiến trúc Blackwell, dẫn đầu cuộc cách mạng đồ họa AI thế hệ mới.</p>
        </div>
        <a href="<?= url('auth/register') ?>" class="btn btn--light">Đăng ký thông tin <i class="fa-solid fa-bell"></i></a>
    </div>
</section>

<!-- ===== 8. BEST SELLER PRODUCTS WITH TABS ===== -->
<section class="container section section-best-sellers">
    <div class="best-seller-section__header">
        <h2>Sản phẩm bán chạy</h2>
        <div class="tabs-nav">
            <button class="tab-btn is-active" data-tab="tab-laptop">Laptop</button>
            <button class="tab-btn" data-tab="tab-gaming">Gaming Gear</button>
            <button class="tab-btn" data-tab="tab-components">Linh Kiện</button>
            <button class="tab-btn" data-tab="tab-monitor">Màn Hình</button>
            <button class="tab-btn" data-tab="tab-accessories">Phụ Kiện</button>
        </div>
    </div>
    
    <div class="tabs-content">
        <!-- Tab Laptop -->
        <div class="tabs-content__panel is-active" id="tab-laptop">
            <div class="section-slider-wrapper">
                <button type="button" class="product-slider-arrow prev" aria-label="Sản phẩm trước"><i class="fa-solid fa-chevron-left"></i></button>
                <button type="button" class="product-slider-arrow next" aria-label="Sản phẩm tiếp"><i class="fa-solid fa-chevron-right"></i></button>
                <div class="product-slider-track">
                    <?php foreach ($bestSellersLaptop as $p): ?>
                        <div>
                            <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <!-- Tab Gaming -->
        <div class="tabs-content__panel" id="tab-gaming">
            <div class="section-slider-wrapper">
                <button type="button" class="product-slider-arrow prev" aria-label="Sản phẩm trước"><i class="fa-solid fa-chevron-left"></i></button>
                <button type="button" class="product-slider-arrow next" aria-label="Sản phẩm tiếp"><i class="fa-solid fa-chevron-right"></i></button>
                <div class="product-slider-track">
                    <?php foreach ($bestSellersGaming as $p): ?>
                        <div>
                            <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <!-- Tab Components -->
        <div class="tabs-content__panel" id="tab-components">
            <div class="section-slider-wrapper">
                <button type="button" class="product-slider-arrow prev" aria-label="Sản phẩm trước"><i class="fa-solid fa-chevron-left"></i></button>
                <button type="button" class="product-slider-arrow next" aria-label="Sản phẩm tiếp"><i class="fa-solid fa-chevron-right"></i></button>
                <div class="product-slider-track">
                    <?php foreach ($bestSellersComponents as $p): ?>
                        <div>
                            <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <!-- Tab Monitor -->
        <div class="tabs-content__panel" id="tab-monitor">
            <div class="section-slider-wrapper">
                <button type="button" class="product-slider-arrow prev" aria-label="Sản phẩm trước"><i class="fa-solid fa-chevron-left"></i></button>
                <button type="button" class="product-slider-arrow next" aria-label="Sản phẩm tiếp"><i class="fa-solid fa-chevron-right"></i></button>
                <div class="product-slider-track">
                    <?php foreach ($bestSellersMonitor as $p): ?>
                        <div>
                            <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <!-- Tab Accessories -->
        <div class="tabs-content__panel" id="tab-accessories">
            <div class="section-slider-wrapper">
                <button type="button" class="product-slider-arrow prev" aria-label="Sản phẩm trước"><i class="fa-solid fa-chevron-left"></i></button>
                <button type="button" class="product-slider-arrow next" aria-label="Sản phẩm tiếp"><i class="fa-solid fa-chevron-right"></i></button>
                <div class="product-slider-track">
                    <?php foreach ($bestSellersAccessories as $p): ?>
                        <div>
                            <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($newProducts)): ?>
<!-- ===== SẢN PHẨM MỚI VỀ (NEW ARRIVALS) ===== -->
<section class="container section">
    <div class="section__head">
        <h2><i class="fa-solid fa-sparkles" style="color: #2ed573; margin-right: 8px;"></i>Sản phẩm mới về</h2>
        <a href="<?= url('home/search?sort=newest') ?>" class="section__more">Xem tất cả <i class="fa-solid fa-chevron-right"></i></a>
    </div>
    <div class="section-slider-wrapper">
        <button type="button" class="product-slider-arrow prev" aria-label="Sản phẩm trước"><i class="fa-solid fa-chevron-left"></i></button>
        <button type="button" class="product-slider-arrow next" aria-label="Sản phẩm tiếp"><i class="fa-solid fa-chevron-right"></i></button>
        <div class="product-slider-track">
            <?php foreach ($newProducts as $p): ?>
                <div>
                    <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===== 9. TRIPLE PROMOTION BANNER ===== -->
<section class="container section">
    <div class="promo-banners-container">
        <div class="promo-banner" style="background-image: url('<?= url('assets/images/promo-banner-1.jpg') ?>');">
            <div class="promo-banner__content">
                <h3>Build PC Cực Chất</h3>
                <p>Tặng kèm tản nhiệt khí khí lắp PC nguyên bộ.</p>
            </div>
            <a href="<?= url('home/search?cat=pc-linh-kien') ?>" class="btn btn--sm">Build Ngay</a>
        </div>
        <div class="promo-banner" style="background-image: url('<?= url('assets/images/promo-banner-2.jpg') ?>');">
            <div class="promo-banner__content">
                <h3>BẢO HÀNH CHÍNH HÃNG</h3>
                <p>Cam kết bảo hành chính hãng 12 tháng cho tất cả sản phẩm.</p>
            </div>
            <a href="<?= url('home/search') ?>" class="btn btn--sm">Xem ngay</a>
        </div>
        <div class="promo-banner" style="background-image: url('<?= url('assets/images/promo-banner-3.jpg') ?>');">
            <div class="promo-banner__content">
                <h3>Thu Cũ Đổi Mới</h3>
                <p>Nâng cấp máy mới trợ giá lên tới 15%.</p>
            </div>
            <a href="<?= url('post') ?>" class="btn btn--sm">Đổi Máy Ngay</a>
        </div>
    </div>
</section>

<!-- ===== MOBILE EXPLORE BY CATEGORIES (Mobile-Only) ===== -->
<section class="container section mobile-explore-section">
    <div class="mobile-explore-header">
        <h2>Khám phá theo danh mục</h2>
        <a href="<?= url('home/search?cat=laptop') ?>" class="section__more" id="mobileExploreSeeAll">Xem tất cả <i class="fa-solid fa-chevron-right"></i></a>
    </div>
    
    <!-- Tab Navigation di động cuộn ngang -->
    <div class="mobile-explore-tabs scroll-x-container">
        <button class="explore-tab-btn active" data-target="m-tab-laptop" data-url="<?= url('home/search?cat=laptop') ?>">Laptop</button>
        <button class="explore-tab-btn" data-target="m-tab-pc" data-url="<?= url('home/search?cat=pc-build-san') ?>">PC Build</button>
        <button class="explore-tab-btn" data-target="m-tab-components" data-url="<?= url('home/search?cat=pc-linh-kien') ?>">Linh kiện</button>
        <button class="explore-tab-btn" data-target="m-tab-gear" data-url="<?= url('home/search?cat=gaming-gear') ?>">Gaming Gear</button>
        <button class="explore-tab-btn" data-target="m-tab-monitor" data-url="<?= url('home/search?cat=man-hinh') ?>">Màn hình</button>
    </div>

    <!-- Tab Contents panels -->
    <div class="mobile-explore-content">
        <!-- Tab Laptop -->
        <div class="explore-tab-panel active" id="m-tab-laptop">
            <div class="product-grid">
                <?php foreach (array_slice(array_merge($laptopGaming, $laptopVanPhong), 0, 4) as $p): ?>
                    <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Tab PC Build -->
        <div class="explore-tab-panel" id="m-tab-pc">
            <div class="product-grid">
                <?php foreach (array_slice($pcBuildSan, 0, 4) as $p): ?>
                    <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Tab Linh kiện -->
        <div class="explore-tab-panel" id="m-tab-components">
            <div class="product-grid">
                <?php foreach (array_slice($pcLinhKien, 0, 4) as $p): ?>
                    <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Tab Gaming Gear -->
        <div class="explore-tab-panel" id="m-tab-gear">
            <div class="product-grid">
                <?php foreach (array_slice($gamingGear, 0, 4) as $p): ?>
                    <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Tab Màn hình -->
        <div class="explore-tab-panel" id="m-tab-monitor">
            <div class="product-grid">
                <?php foreach (array_slice($monHinh, 0, 4) as $p): ?>
                    <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.explore-tab-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.explore-tab-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                document.querySelectorAll('.explore-tab-panel').forEach(p => p.classList.remove('active'));
                const targetId = this.getAttribute('data-target');
                document.getElementById(targetId)?.classList.add('active');
                
                const seeAllBtn = document.getElementById('mobileExploreSeeAll');
                const targetUrl = this.getAttribute('data-url');
                if (seeAllBtn && targetUrl) {
                    seeAllBtn.setAttribute('href', targetUrl);
                }
            });
        });
    </script>
</section>

<?php if (!empty($laptopGaming)): ?>
<!-- ===== LAPTOP GAMING ===== -->
<section class="container section">
    <div class="section__head">
        <h2>Laptop Gaming</h2>
        <a href="<?= url('home/search?cat=laptop&gpu=dedicated') ?>" class="section__more">Xem tất cả <i class="fa-solid fa-chevron-right"></i></a>
    </div>
    <div class="section-slider-wrapper">
        <button type="button" class="product-slider-arrow prev" aria-label="Sản phẩm trước"><i class="fa-solid fa-chevron-left"></i></button>
        <button type="button" class="product-slider-arrow next" aria-label="Sản phẩm tiếp"><i class="fa-solid fa-chevron-right"></i></button>
        <div class="product-slider-track">
            <?php foreach ($laptopGaming as $p): ?>
                <div>
                    <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===== BANNER GIAO HÀNG MIỄN PHÍ ===== -->
<section class="container section">
    <div class="promo-banner" style="background-image: url('<?= url('assets/images/installment-banner.jpg') ?>');">
        <div class="promo-banner__content">
            <h3>GIAO HÀNG MIỄN PHÍ TOÀN QUỐC</h3>
            <p>Giao hàng nhanh — kiểm tra trước, thanh toán sau (COD). Áp dụng cho đơn hàng từ 500.000đ.</p>
        </div>
        <a href="<?= url('home/search') ?>" class="btn btn--light">Mua ngay <i class="fa-solid fa-arrow-right"></i></a>
    </div>
</section>

<?php if (!empty($laptopVanPhong)): ?>
<!-- ===== LAPTOP VĂN PHÒNG ===== -->
<section class="container section">
    <div class="section__head">
        <h2>Laptop Văn Phòng</h2>
        <a href="<?= url('home/search?cat=laptop&gpu=integrated') ?>" class="section__more">Xem tất cả <i class="fa-solid fa-chevron-right"></i></a>
    </div>
    <div class="section-slider-wrapper">
        <button type="button" class="product-slider-arrow prev" aria-label="Sản phẩm trước"><i class="fa-solid fa-chevron-left"></i></button>
        <button type="button" class="product-slider-arrow next" aria-label="Sản phẩm tiếp"><i class="fa-solid fa-chevron-right"></i></button>
        <div class="product-slider-track">
            <?php foreach ($laptopVanPhong as $p): ?>
                <div>
                    <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($pcBuildSan)): ?>
<!-- ===== PC BUILD SẴN ===== -->
<section class="container section">
    <div class="section__head">
        <h2>PC Build Sẵn</h2>
        <a href="<?= url('home/search?cat=pc-build-san') ?>" class="section__more">Xem tất cả <i class="fa-solid fa-chevron-right"></i></a>
    </div>
    <div class="section-slider-wrapper">
        <button type="button" class="product-slider-arrow prev" aria-label="Sản phẩm trước"><i class="fa-solid fa-chevron-left"></i></button>
        <button type="button" class="product-slider-arrow next" aria-label="Sản phẩm tiếp"><i class="fa-solid fa-chevron-right"></i></button>
        <div class="product-slider-track">
            <?php foreach ($pcBuildSan as $p): ?>
                <div>
                    <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($pcLinhKien)): ?>
<!-- ===== 10. PC COMPONENTS ===== -->
<section class="container section">
    <div class="section__head">
        <h2>Linh Kiện PC</h2>
        <a href="<?= url('home/search?cat=pc-linh-kien') ?>" class="section__more">Xem tất cả <i class="fa-solid fa-chevron-right"></i></a>
    </div>
    <div class="section-slider-wrapper">
        <button type="button" class="product-slider-arrow prev" aria-label="Sản phẩm trước"><i class="fa-solid fa-chevron-left"></i></button>
        <button type="button" class="product-slider-arrow next" aria-label="Sản phẩm tiếp"><i class="fa-solid fa-chevron-right"></i></button>
        <div class="product-slider-track">
            <?php foreach ($pcLinhKien as $p): ?>
                <div>
                    <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($gamingGear)): ?>
<!-- ===== 11. GAMING GEAR ===== -->
<section class="container section">
    <div class="section__head">
        <h2>Gaming Gear</h2>
        <a href="<?= url('home/search?cat=gaming-gear') ?>" class="section__more">Xem tất cả <i class="fa-solid fa-chevron-right"></i></a>
    </div>
    <div class="section-slider-wrapper">
        <button type="button" class="product-slider-arrow prev" aria-label="Sản phẩm trước"><i class="fa-solid fa-chevron-left"></i></button>
        <button type="button" class="product-slider-arrow next" aria-label="Sản phẩm tiếp"><i class="fa-solid fa-chevron-right"></i></button>
        <div class="product-slider-track">
            <?php foreach ($gamingGear as $p): ?>
                <div>
                    <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($monHinh)): ?>
<!-- ===== MÀN HÌNH ===== -->
<section class="container section">
    <div class="section__head">
        <h2>Màn Hình</h2>
        <a href="<?= url('home/search?cat=man-hinh') ?>" class="section__more">Xem tất cả <i class="fa-solid fa-chevron-right"></i></a>
    </div>
    <div class="section-slider-wrapper">
        <button type="button" class="product-slider-arrow prev" aria-label="Sản phẩm trước"><i class="fa-solid fa-chevron-left"></i></button>
        <button type="button" class="product-slider-arrow next" aria-label="Sản phẩm tiếp"><i class="fa-solid fa-chevron-right"></i></button>
        <div class="product-slider-track">
            <?php foreach ($monHinh as $p): ?>
                <div>
                    <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($apple)): ?>
<!-- ===== MÁY TÍNH BỘ ===== -->
<section class="container section section-apple-zone">
    <div class="section__head">
        <h2>Máy tính bộ</h2>
        <a href="<?= url('home/search?cat=may-tinh-bo') ?>" class="section__more">Xem tất cả <i class="fa-solid fa-chevron-right"></i></a>
    </div>
    <div class="product-grid product-grid--6">
        <div class="apple-banner" style="background-image: linear-gradient(135deg, rgba(15,91,255,0.95), rgba(7,26,51,0.6)), url('<?= url('assets/images/apple-banner-bg.jpg') ?>');">
            <h3>Máy tính bộ đồng bộ</h3>
            <p>Trải nghiệm các bộ máy tính All-in-One, máy tính văn phòng và máy tính đồng bộ cấu hình cao tại TechPilot.</p>
            <a href="<?= url('home/search?cat=may-tinh-bo') ?>" class="btn btn--outline-light btn--sm">Khám phá ngay</a>
        </div>
        <?php foreach (array_slice($apple, 0, 4) as $p): ?>
            <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($posts)): ?>
<!-- ===== 12. TECHNOLOGY NEWS & AI HUB ===== -->
<section class="container section">
    <style>
        .news-ai-grid {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 24px;
            align-items: start;
        }
        .news-grid-home {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }
        @media (max-width: 1200px) {
            .news-grid-home {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 1024px) {
            .news-ai-grid {
                grid-template-columns: 1fr;
            }
            .news-grid-home {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        @media (max-width: 768px) {
            .news-grid-home {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 520px) {
            .news-grid-home {
                grid-template-columns: 1fr;
            }
        }
        .ai-sidebar-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .ai-sidebar-card:hover {
            transform: translateY(-4px);
        }
    </style>
    <div class="news-ai-grid">
        <!-- Left: News Section -->
        <div class="news-main-col">
            <div class="section__head" style="margin-bottom: 16px;">
                <h2><i class="fa-solid fa-newspaper" style="color: var(--primary); margin-right: 8px;"></i> Tin Tức Công Nghệ</h2>
                <a href="<?= url('post') ?>" class="section__more">Xem thêm <i class="fa-solid fa-chevron-right"></i></a>
            </div>
            <div class="news-grid news-grid-home">
                <?php foreach (array_slice($posts, 0, 4) as $post): ?>
                    <div class="news-card">
                        <a href="<?= url('post/detail/' . e($post['slug'])) ?>" class="news-card__thumb">
                            <img src="<?= postImageUrl($post['image']) ?>" alt="<?= e($post['title']) ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.outerHTML='<div style=\'background-color: var(--secondary); width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;\'><i class=\'fa-solid fa-newspaper\' style=\'font-size: 42px; color: var(--primary);\'></i></div>'">
                        </a>
                        <div class="news-card__body">
                            <span class="news-card__date"><i class="fa-regular fa-clock"></i> <?= date('d/m/Y', strtotime($post['created_at'])) ?></span>
                            <h3 class="news-card__title">
                                <a href="<?= url('post/detail/' . e($post['slug'])) ?>" style="text-decoration: none; color: inherit;">
                                    <?= e($post['title']) ?>
                                </a>
                            </h3>
                            <p class="news-card__summary"><?= e($post['summary']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Right: Prominent AI Modules Sidebar -->
        <div class="ai-sidebar-col" style="display: flex; flex-direction: column; gap: 16px;">
            <div class="section__head" style="margin-bottom: 16px;">
                <h2><i class="fa-solid fa-microchip" style="color: #8B5CF6; margin-right: 8px;"></i> Góc Trợ Lý AI</h2>
            </div>
            
            <!-- AI Recommendation Card -->
            <div class="ai-sidebar-card" style="background: linear-gradient(135deg, #0F172A 0%, #1E1B4B 100%); color: #FFFFFF; border-radius: 16px; padding: 20px; border: 1px solid rgba(139, 92, 246, 0.3); box-shadow: 0 10px 25px rgba(139, 92, 246, 0.15); display: flex; flex-direction: column; gap: 12px; position: relative; overflow: hidden;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 10.5px; font-weight: 800; text-transform: uppercase; background: rgba(139, 92, 246, 0.25); color: #C4B5FD; border: 1px solid rgba(139, 92, 246, 0.4); padding: 3px 8px; border-radius: 20px;">AI 4.0 Smart</span>
                    <i class="fa-solid fa-wand-magic-sparkles" style="font-size: 22px; color: #A78BFA;"></i>
                </div>
                <h3 style="font-size: 17px; font-weight: 700; margin: 0; color: #FFFFFF; line-height: 1.3;">Tư Vấn Chọn Máy AI</h3>
                <p style="font-size: 12.5px; color: #94A3B8; margin: 0; line-height: 1.5;">Khảo sát 5 câu hỏi nhanh để AI tìm 3 cấu hình tối ưu nhất theo ngân sách của bạn.</p>
                <a href="<?= url('ai-assistant') ?>" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: linear-gradient(135deg, #6D28D9 0%, #4C1D95 100%); color: #FFFFFF; text-decoration: none; padding: 10px 16px; border-radius: 10px; font-weight: 700; font-size: 13px; border: 1px solid rgba(167, 139, 250, 0.3); margin-top: 4px;">
                    Mở AI Tư Vấn <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <!-- AI Comparison Card -->
            <div class="ai-sidebar-card" style="background: linear-gradient(135deg, #0F172A 0%, #064E3B 100%); color: #FFFFFF; border-radius: 16px; padding: 20px; border: 1px solid rgba(16, 185, 129, 0.3); box-shadow: 0 10px 25px rgba(16, 185, 129, 0.15); display: flex; flex-direction: column; gap: 12px; position: relative; overflow: hidden;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 10.5px; font-weight: 800; text-transform: uppercase; background: rgba(16, 185, 129, 0.25); color: #6EE7B7; border: 1px solid rgba(16, 185, 129, 0.4); padding: 3px 8px; border-radius: 20px;">Chuyên Sâu</span>
                    <i class="fa-solid fa-scale-balanced" style="font-size: 22px; color: #34D399;"></i>
                </div>
                <h3 style="font-size: 17px; font-weight: 700; margin: 0; color: #FFFFFF; line-height: 1.3;">So Sánh Cấu Hình AI</h3>
                <p style="font-size: 12.5px; color: #94A3B8; margin: 0; line-height: 1.5;">Chọn 2–4 sản phẩm để đối chiếu bảng thông số thật và xem đánh giá ưu/nhược từ AI.</p>
                <a href="<?= url('compare') ?>" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #FFFFFF; text-decoration: none; padding: 10px 16px; border-radius: 10px; font-weight: 700; font-size: 13px; border: 1px solid rgba(52, 211, 153, 0.3); margin-top: 4px;">
                    Mở Trang So Sánh <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===== 13. FEATURED BRANDS (ĐỐI TÁC CHIẾN LƯỢC) ===== -->
<section class="container section">
    <div class="section__head section__head--brand-partners">
        <h2>ĐỐI TÁC CHIẾN LƯỢC</h2>
    </div>
    <div class="brand-slider">
        <div class="brand-slider__track">
            <?php 
            // Nhân đôi danh sách thương hiệu để hiệu ứng chạy marquee cuộn mượt không bị đứt đoạn
            $duplicatedBrands = array_merge($brands, $brands);
            foreach ($duplicatedBrands as $brand): 
                $logoPath = $brand['logo'] ?? null;
                $logoUrl = brandLogoUrl($logoPath);
            ?>
                <div class="brand-logo-card" title="<?= e($brand['name']) ?>">
                    <?php if ($logoUrl): ?>
                        <img src="<?= $logoUrl ?>" alt="<?= e($brand['name']) ?>" loading="lazy" decoding="async" onerror="handleBrandLogoError(this, '<?= e($brand['slug'] ?? '') ?>')">
                        <span class="brand-logo-text-fallback" style="display: none;"><?= e($brand['name']) ?></span>
                    <?php else: ?>
                        <span class="brand-logo-text-fallback"><?= e($brand['name']) ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== ĐÁNH GIÁ KHÁCH HÀNG ===== -->
<section class="container section">
    <div class="section__head">
        <h2>Khách hàng nói gì về TechPilot</h2>
    </div>
    <div class="review-strip">
        <?php foreach ($reviews as $rev): ?>
            <div class="review-card">
                <div class="review-card__head">
                    <i class="fa-solid fa-circle-user"></i>
                    <strong><?= e($rev['reviewer_name']) ?></strong>
                </div>
                <div class="stars">
                    <?= renderStars((float)$rev['rating']) ?>
                </div>
                <p>"<?= e($rev['comment']) ?>"</p>
            </div>
        <?php endforeach; ?>
    </div>
</section>
</div><!-- .home-page-wrapper -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    function setupProductSlider(wrapper) {
        if (!wrapper || wrapper.dataset.sliderInitialized) return;
        wrapper.dataset.sliderInitialized = 'true';

        const track = wrapper.querySelector('.product-slider-track');
        const prevBtn = wrapper.querySelector('.product-slider-arrow.prev');
        const nextBtn = wrapper.querySelector('.product-slider-arrow.next');
        if (!track) return;

        // Tự động clone các item nếu số item ít hơn 10 để slider cuộn mượt không bị nghẽn
        if (track.children.length > 0 && track.children.length < 10) {
            const originalChildren = Array.from(track.children);
            originalChildren.forEach(child => {
                const clone = child.cloneNode(true);
                track.appendChild(clone);
            });
        }

        let autoPlayTimer = null;

        function getCardScrollWidth() {
            const firstCard = track.children[0];
            if (!firstCard) return 220;
            const cardRect = firstCard.getBoundingClientRect();
            const gap = parseFloat(getComputedStyle(track).gap) || 16;
            return cardRect.width + gap;
        }

        function scrollNext() {
            if (track.scrollLeft + track.clientWidth >= track.scrollWidth - 15) {
                track.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                track.scrollBy({ left: getCardScrollWidth(), behavior: 'smooth' });
            }
        }

        function scrollPrev() {
            if (track.scrollLeft <= 15) {
                track.scrollTo({ left: track.scrollWidth - track.clientWidth, behavior: 'smooth' });
            } else {
                track.scrollBy({ left: -getCardScrollWidth(), behavior: 'smooth' });
            }
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                scrollNext();
                resetAutoPlay();
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                scrollPrev();
                resetAutoPlay();
            });
        }

        function startAutoPlay() {
            if (autoPlayTimer) clearInterval(autoPlayTimer);
            autoPlayTimer = setInterval(scrollNext, 3500);
        }

        function resetAutoPlay() {
            if (autoPlayTimer) clearInterval(autoPlayTimer);
            startAutoPlay();
        }

        startAutoPlay();
        wrapper.addEventListener('mouseenter', function() {
            if (autoPlayTimer) clearInterval(autoPlayTimer);
        });
        wrapper.addEventListener('mouseleave', startAutoPlay);
    }

    document.querySelectorAll('.section-slider-wrapper').forEach(setupProductSlider);

    // Re-init tabbed sliders on tab button click
    document.querySelectorAll('.tab-btn').forEach(function(tabBtn) {
        tabBtn.addEventListener('click', function() {
            setTimeout(function() {
                document.querySelectorAll('.tabs-content__panel.is-active .section-slider-wrapper').forEach(setupProductSlider);
            }, 60);
        });
    });
});
</script>
