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

// Khác
$brands = $brands ?? [];
$posts = $posts ?? [];
$reviews = $reviews ?? [];
?>

<!-- ===== 4. HERO SECTION ===== -->
<section class="container hero-section">
    <!-- Left: Vertical Category Menu (GearVN style Mega Menu) -->
    <div class="hero-section__left">
        <nav class="vertical-menu">
            <?php
            $verticalCategories = [
                [
                    'name' => 'Laptop',
                    'icon' => 'fa-solid fa-laptop',
                    'slug' => 'laptop-van-phong',
                    'columns' => [
                        'Thương hiệu' => ['ASUS', 'ACER', 'MSI', 'LENOVO', 'LG - Gram'],
                        'Giá bán' => ['Dưới 15 triệu', 'Từ 15 đến 20 triệu', 'Trên 20 triệu'],
                        'CPU Intel - AMD' => ['Intel Core i3', 'Intel Core i5', 'Intel Core i7', 'AMD Ryzen'],
                        'Nhu cầu sử dụng' => ['Đồ họa - Studio', 'Học sinh - Sinh viên', 'Mỏng nhẹ cao cấp'],
                        'Linh phụ kiện Laptop' => ['Ram laptop', 'SSD laptop', 'Ổ cứng di động']
                    ]
                ],
                [
                    'name' => 'Laptop Gaming',
                    'icon' => 'fa-solid fa-gamepad',
                    'slug' => 'laptop-gaming',
                    'columns' => [
                        'Thương hiệu' => ['ASUS ROG / TUF', 'MSI Gaming', 'Acer Predator / Nitro', 'Lenovo Legion', 'HP Omen / Victus', 'Dell G-Series'],
                        'Phân khúc giá' => ['Dưới 20 triệu', '20 - 30 triệu', '30 - 40 triệu', 'Trên 40 triệu'],
                        'Cấu hình GPU' => ['GeForce RTX 4050', 'GeForce RTX 4060', 'GeForce RTX 4070', 'GeForce RTX 4080 / 4090']
                    ]
                ],
                [
                    'name' => 'PC GVN (PC TechPilot)',
                    'icon' => 'fa-solid fa-desktop',
                    'slug' => 'pc-build-san',
                    'columns' => [
                        'PC TechPilot' => ['PC Gaming giá rẻ', 'PC Streamer - Creator', 'PC Render - Đồ họa', 'PC Office văn phòng'],
                        'PC Theo hãng' => ['PC Gaming Asus ROG', 'PC MSI Dragon Edition', 'PC Gigabyte Aorus', 'PC Corsair One']
                    ]
                ],
                [
                    'name' => 'Main, CPU, VGA',
                    'icon' => 'fa-solid fa-microchip',
                    'slug' => 'pc-linh-kien',
                    'columns' => [
                        'Bộ vi xử lý CPU' => ['Intel Core i5', 'Intel Core i7', 'Intel Core i9', 'AMD Ryzen 5', 'AMD Ryzen 7'],
                        'Bo mạch chủ Main' => ['Mainboard ASUS', 'Mainboard MSI', 'Mainboard Gigabyte'],
                        'Card màn hình VGA' => ['NVIDIA GeForce RTX', 'AMD Radeon RX']
                    ]
                ],
                [
                    'name' => 'Case, Nguồn, Tản',
                    'icon' => 'fa-solid fa-box',
                    'slug' => 'pc-linh-kien',
                    'columns' => [
                        'Bộ nguồn máy tính' => ['Nguồn dưới 650W', 'Nguồn 750W - 850W', 'Nguồn trên 1000W'],
                        'Tản nhiệt CPU' => ['Tản nhiệt khí CPU', 'Tản nhiệt nước AIO', 'Quạt tản nhiệt Case'],
                        'Vỏ Case PC' => ['Vỏ Case ATX / ITX', 'Vỏ Case Bể cá', 'Vỏ Case Gaming led RGB']
                    ]
                ],
                [
                    'name' => 'Ổ cứng, RAM, Thẻ nhớ',
                    'icon' => 'fa-solid fa-database',
                    'slug' => 'pc-linh-kien',
                    'columns' => [
                        'Bộ nhớ RAM' => ['RAM DDR4 8GB / 16GB', 'RAM DDR5 16GB / 32GB', 'RAM Laptop'],
                        'Ổ cứng SSD / HDD' => ['SSD M.2 NVMe', 'SSD SATA III 2.5"', 'Ổ cứng di động']
                    ]
                ],
                [
                    'name' => 'Loa, Micro, Webcam',
                    'icon' => 'fa-solid fa-volume-high',
                    'slug' => 'office-gear',
                    'columns' => [
                        'Âm thanh' => ['Loa máy tính', 'Tai nghe chụp tai', 'Loa Bluetooth di động'],
                        'Webcam & Ghi âm' => ['Webcam Full HD / 2K', 'Microphone livestream']
                    ]
                ],
                [
                    'name' => 'Màn hình',
                    'icon' => 'fa-solid fa-tv',
                    'slug' => 'man-hinh',
                    'columns' => [
                        'Hãng sản xuất' => ['Màn hình LG', 'Màn hình Samsung', 'Màn hình ASUS', 'Màn hình Dell', 'Màn hình MSI', 'Màn hình AOC'],
                        'Thông số & Nhu cầu' => ['Màn hình Gaming 144Hz+', 'Màn hình Cong Ultrawide', 'Màn hình 4K Đồ họa', 'Màn hình Văn phòng']
                    ]
                ],
                [
                    'name' => 'Bàn phím',
                    'icon' => 'fa-solid fa-keyboard',
                    'slug' => 'gaming-gear',
                    'columns' => [
                        'Bàn phím' => ['Bàn phím cơ', 'Bàn phím văn phòng', 'Phím giả cơ giá rẻ']
                    ]
                ],
                [
                    'name' => 'Chuột + Lót chuột',
                    'icon' => 'fa-solid fa-mouse',
                    'slug' => 'gaming-gear',
                    'columns' => [
                        'Chuột & Lót' => ['Chuột không dây', 'Chuột gaming', 'Lót chuột cỡ lớn']
                    ]
                ],
                [
                    'name' => 'Tai Nghe',
                    'icon' => 'fa-solid fa-headphones',
                    'slug' => 'gaming-gear',
                    'columns' => [
                        'Tai nghe' => ['Tai nghe chụp tai', 'Tai nghe in-ear', 'Tai nghe không dây']
                    ]
                ],
                [
                    'name' => 'Ghế - Bàn',
                    'icon' => 'fa-solid fa-chair',
                    'slug' => 'office-gear',
                    'columns' => [
                        'Ghế - Bàn' => ['Ghế Gaming', 'Ghế công thái học', 'Bàn chữ Z']
                    ]
                ],
                [
                    'name' => 'Phần mềm, mạng',
                    'icon' => 'fa-solid fa-network-wired',
                    'slug' => 'networking',
                    'columns' => [
                        'Mạng & Phần mềm' => ['Router Wifi 6', 'Switch chia cổng', 'Windows bản quyền', 'Office 365']
                    ]
                ],
                [
                    'name' => 'Handheld, Console',
                    'icon' => 'fa-solid fa-gamepad',
                    'slug' => 'gaming-gear',
                    'columns' => [
                        'Máy chơi game' => ['Nintendo Switch', 'PlayStation 5', 'ASUS ROG Ally', 'Steam Deck']
                    ]
                ],
                [
                    'name' => 'Phụ kiện (Hub, sạc...)',
                    'icon' => 'fa-solid fa-plug',
                    'slug' => 'networking',
                    'columns' => [
                        'Phụ kiện' => ['Hub chuyển đổi', 'Cáp HDMI/DisplayPort', 'Pin dự phòng']
                    ]
                ],
                [
                    'name' => 'Dịch vụ & Thông tin khác',
                    'icon' => 'fa-solid fa-circle-info',
                    'slug' => 'networking',
                    'columns' => [
                        'Hỗ trợ' => ['Vệ sinh PC', 'Lắp đặt tại nhà', 'Bảo hành mở rộng']
                    ]
                ]
            ];
            ?>
            <?php foreach ($verticalCategories as $item): ?>
                <div class="vertical-menu__item">
                    <a href="<?= url('home/search?q=' . urlencode($item['name'])) ?>" class="vertical-menu__link">
                        <div>
                            <i class="<?= e($item['icon']) ?>" style="width: 20px;"></i>
                            <span><?= e($item['name']) ?></span>
                        </div>
                        <i class="fa-solid fa-chevron-right arrow-right"></i>
                    </a>
                    
                    <?php if (!empty($item['columns'])): ?>
                        <div class="mega-menu">
                            <div class="mega-menu__inner">
                                <?php foreach ($item['columns'] as $title => $subitems): ?>
                                    <div class="mega-menu__column">
                                        <h5><?= e($title) ?></h5>
                                        <ul>
                                            <?php foreach ($subitems as $subitem): ?>
                                                <li><a href="<?= url('home/search?q=' . urlencode($subitem)) ?>"><?= e($subitem) ?></a></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </nav>
    </div>

    <!-- Center: Large Hero Banner Carousel -->
    <div class="hero-section__center" id="heroCarousel">
        <div class="carousel-slide is-active" style="background-image: linear-gradient(135deg, rgba(13, 27, 42, 0.95), rgba(13, 27, 42, 0.4)), url('<?= url('assets/images/rog-banner-bg.jpg') ?>');">
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
        
        <div class="carousel-slide" style="background-image: linear-gradient(135deg, rgba(29, 78, 216, 0.9), rgba(17, 24, 39, 0.6)), url('<?= url('assets/images/banner-rtx-bg.jpg') ?>');">
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
            <a href="#" class="btn btn--light">Đăng ký ngay <i class="fa-solid fa-envelope"></i></a>
        </div>

        <div class="carousel-controls">
            <div class="carousel-dot is-active" data-index="0"></div>
            <div class="carousel-dot" data-index="1"></div>
        </div>
    </div>

    <!-- Right: 3 Promotion Cards -->
    <div class="hero-section__right">
        <div class="promo-card promo-card--dark">
            <div>
                <h4>BUILD PC THEO YÊU CẦU</h4>
                <p>Tối ưu cấu hình - Cân mọi ngân sách</p>
            </div>
            <a href="#" class="btn btn--outline-light btn--sm">Xem ngay</a>
        </div>
        
        <div class="promo-card promo-card--blue">
            <div>
                <h4>TRẢ GÓP LÃI SUẤT 0%</h4>
                <p>Duyệt nhanh 3 phút - Không giữ giấy tờ</p>
            </div>
            <span class="promo-card__number">0%</span>
            <a href="#" class="btn btn--sm">Đăng ký</a>
        </div>
        
        <div class="promo-card">
            <div>
                <h4>THU CŨ ĐỔI MỚI MÁY CŨ</h4>
                <p>Trợ giá lên tới 6 triệu đồng</p>
            </div>
            <a href="#" class="btn btn--sm">Xem chi tiết</a>
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
            <i class="fa-solid fa-credit-card"></i>
            <span>Trả góp lãi suất 0%<br><small>Qua thẻ tín dụng</small></span>
        </div>
        <div class="feature-item">
            <i class="fa-solid fa-headset"></i>
            <span>Hỗ trợ kỹ thuật 24/7<br><small>Hotline: 1900 1234</small></span>
        </div>
    </div>
</section>

<!-- ===== 6. FLASH SALE ===== -->
<section class="container section section-flash-sale">
    <div class="section__head section__head--flash">
        <h2><i class="fa-solid fa-bolt"></i> FLASH SALE</h2>
        <div class="countdown" id="flashCountdown" data-end-time="<?= !empty($flashSale) ? e($flashSale[0]['end_time']) : '' ?>">
            <div class="countdown-box">
                <span class="countdown-box__num" id="cd-h">02</span>
                <span class="countdown-box__label">Giờ</span>
            </div>
            <div class="countdown-separator">:</div>
            <div class="countdown-box">
                <span class="countdown-box__num" id="cd-m">15</span>
                <span class="countdown-box__label">Phút</span>
            </div>
            <div class="countdown-separator">:</div>
            <div class="countdown-box">
                <span class="countdown-box__num" id="cd-s">30</span>
                <span class="countdown-box__label">Giây</span>
            </div>
        </div>
        <a href="<?= url('home/search') ?>" class="section__more">Xem tất cả <i class="fa-solid fa-chevron-right"></i></a>
    </div>
    <div class="product-grid product-grid--6">
        <?php foreach ($flashSale as $p): ?>
            <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===== 7. POPULAR CATEGORIES ===== -->
<section class="container section">
    <div class="section__head">
        <h2>Danh mục sản phẩm</h2>
        <a href="<?= url('home/search') ?>" class="section__more">Xem tất cả <i class="fa-solid fa-chevron-right"></i></a>
    </div>
    <?php
    $popularCategoriesList = [
        ['name' => 'Laptop', 'slug' => 'laptop', 'query' => 'laptop'],
        ['name' => 'PC', 'slug' => 'pc', 'query' => 'pc'],
        ['name' => 'Màn hình', 'slug' => 'man-hinh', 'query' => 'man-hinh'],
        ['name' => 'Mainboard', 'slug' => 'mainboard', 'query' => 'mainboard'],
        ['name' => 'CPU', 'slug' => 'cpu', 'query' => 'cpu'],
        ['name' => 'VGA', 'slug' => 'vga', 'query' => 'vga'],
        ['name' => 'RAM', 'slug' => 'ram', 'query' => 'ram'],
        ['name' => 'Ổ cứng', 'slug' => 'o-cung', 'query' => 'ssd'],
        ['name' => 'Case', 'slug' => 'case', 'query' => 'case'],
        ['name' => 'Tản nhiệt', 'slug' => 'tan-nhiet', 'query' => 'tan-nhiet'],
        ['name' => 'Nguồn', 'slug' => 'nguon', 'query' => 'psu'],
        ['name' => 'Bàn phím', 'slug' => 'ban-phim', 'query' => 'ban+phim'],
        ['name' => 'Chuột', 'slug' => 'chuot', 'query' => 'chuot'],
        ['name' => 'Ghế', 'slug' => 'ghe', 'query' => 'ghe'],
        ['name' => 'Tai nghe', 'slug' => 'tai-nghe', 'query' => 'tai+nghe'],
        ['name' => 'Loa', 'slug' => 'loa', 'query' => 'loa'],
        ['name' => 'Console', 'slug' => 'console', 'query' => 'console'],
        ['name' => 'Phụ kiện', 'slug' => 'phu-kien', 'query' => 'cap'],
        ['name' => 'Thiết bị VP', 'slug' => 'thiet-bi-vp', 'query' => 'printer'],
        ['name' => 'Sạc DP', 'slug' => 'sac-dp', 'query' => 'sac+du+phong']
    ];
    ?>
    <div class="category-strip">
        <?php foreach ($popularCategoriesList as $item): ?>
            <a href="<?= url('home/search?q=' . urlencode($item['query'])) ?>" class="category-strip__item">
                <div class="category-strip__icon">
                    <img src="<?= url('assets/images/categories/' . e($item['slug']) . '.png') ?>" alt="<?= e($item['name']) ?>" onerror="this.outerHTML='<i class=\'fa-solid fa-tag\' style=\'font-size: 24px; color: var(--primary);\'></i>'">
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
        <a href="#" class="btn btn--light">Đăng ký thông tin <i class="fa-solid fa-bell"></i></a>
    </div>
</section>

<!-- ===== 8. BEST SELLER PRODUCTS WITH TABS ===== -->
<section class="container section">
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
            <div class="product-grid product-grid--6">
                <?php foreach ($bestSellersLaptop as $p): ?>
                    <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- Tab Gaming -->
        <div class="tabs-content__panel" id="tab-gaming">
            <div class="product-grid product-grid--6">
                <?php foreach ($bestSellersGaming as $p): ?>
                    <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- Tab Components -->
        <div class="tabs-content__panel" id="tab-components">
            <div class="product-grid product-grid--6">
                <?php foreach ($bestSellersComponents as $p): ?>
                    <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- Tab Monitor -->
        <div class="tabs-content__panel" id="tab-monitor">
            <div class="product-grid product-grid--6">
                <?php foreach ($bestSellersMonitor as $p): ?>
                    <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- Tab Accessories -->
        <div class="tabs-content__panel" id="tab-accessories">
            <div class="product-grid product-grid--6">
                <?php foreach ($bestSellersAccessories as $p): ?>
                    <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- ===== 9. TRIPLE PROMOTION BANNER ===== -->
<section class="container section">
    <div class="promo-banners-container">
        <div class="promo-banner" style="background-image: url('<?= url('assets/images/promo-banner-1.jpg') ?>');">
            <div class="promo-banner__content">
                <h3>Build PC Cực Chất</h3>
                <p>Tặng kèm tản nhiệt khí khí lắp PC nguyên bộ.</p>
            </div>
            <a href="#" class="btn btn--sm">Build Ngay</a>
        </div>
        <div class="promo-banner" style="background-image: url('<?= url('assets/images/promo-banner-2.jpg') ?>');">
            <div class="promo-banner__content">
                <h3>Trả góp 0% Lãi Suất</h3>
                <p>Duyệt hồ sơ nhanh chóng qua Home Credit.</p>
            </div>
            <a href="#" class="btn btn--sm">Xem Chi Tiết</a>
        </div>
        <div class="promo-banner" style="background-image: url('<?= url('assets/images/promo-banner-3.jpg') ?>');">
            <div class="promo-banner__content">
                <h3>Thu Cũ Đổi Mới</h3>
                <p>Nâng cấp máy mới trợ giá lên tới 15%.</p>
            </div>
            <a href="#" class="btn btn--sm">Đổi Máy Ngay</a>
        </div>
    </div>
</section>

<!-- ===== LAPTOP GAMING ===== -->
<section class="container section">
    <div class="section__head">
        <h2>Laptop Gaming</h2>
        <a href="<?= url('home/search?cat=laptop-gaming') ?>" class="section__more">Xem tất cả <i class="fa-solid fa-chevron-right"></i></a>
    </div>
    <div class="product-grid product-grid--6">
        <?php foreach ($laptopGaming as $p): ?>
            <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===== BANNER TRẢ GÓP ===== -->
<section class="container section">
    <div class="promo-banner" style="background-image: url('<?= url('assets/images/installment-banner.jpg') ?>');">
        <div class="promo-banner__content">
            <h3>TRẢ GÓP 0% LÃI SUẤT QUA THẺ TÍN DỤNG</h3>
            <p>Hỗ trợ hơn 25 ngân hàng liên kết, kỳ hạn linh hoạt 3 - 6 - 9 - 12 tháng không phụ phí.</p>
        </div>
        <a href="#" class="btn btn--light">Tìm hiểu thêm <i class="fa-solid fa-circle-info"></i></a>
    </div>
</section>

<!-- ===== LAPTOP VĂN PHÒNG ===== -->
<section class="container section">
    <div class="section__head">
        <h2>Laptop Văn Phòng</h2>
        <a href="<?= url('home/search?cat=laptop-van-phong') ?>" class="section__more">Xem tất cả <i class="fa-solid fa-chevron-right"></i></a>
    </div>
    <div class="product-grid product-grid--6">
        <?php foreach ($laptopVanPhong as $p): ?>
            <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===== PC BUILD SẴN ===== -->
<section class="container section">
    <div class="section__head">
        <h2>PC Build Sẵn</h2>
        <a href="<?= url('home/search?cat=pc-build-san') ?>" class="section__more">Xem tất cả <i class="fa-solid fa-chevron-right"></i></a>
    </div>
    <div class="product-grid product-grid--6">
        <?php foreach ($pcBuildSan as $p): ?>
            <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===== 10. PC COMPONENTS ===== -->
<section class="container section">
    <div class="section__head">
        <h2>Linh Kiện PC</h2>
        <a href="<?= url('home/search?cat=pc-linh-kien') ?>" class="section__more">Xem tất cả <i class="fa-solid fa-chevron-right"></i></a>
    </div>
    <div class="product-grid product-grid--6">
        <?php foreach ($pcLinhKien as $p): ?>
            <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===== 11. GAMING GEAR ===== -->
<section class="container section">
    <div class="section__head">
        <h2>Gaming Gear</h2>
        <a href="<?= url('home/search?cat=gaming-gear') ?>" class="section__more">Xem tất cả <i class="fa-solid fa-chevron-right"></i></a>
    </div>
    <div class="product-grid product-grid--6">
        <?php foreach ($gamingGear as $p): ?>
            <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===== MÀN HÌNH ===== -->
<section class="container section">
    <div class="section__head">
        <h2>Màn Hình</h2>
        <a href="<?= url('home/search?cat=man-hinh') ?>" class="section__more">Xem tất cả <i class="fa-solid fa-chevron-right"></i></a>
    </div>
    <div class="product-grid product-grid--6">
        <?php foreach ($monHinh as $p): ?>
            <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===== APPLE ZONE ===== -->
<section class="container section">
    <div class="section__head">
        <h2>Apple Zone</h2>
        <a href="<?= url('home/search?cat=apple') ?>" class="section__more">Xem tất cả <i class="fa-solid fa-chevron-right"></i></a>
    </div>
    <div class="product-grid product-grid--6">
        <div class="apple-banner" style="background-image: linear-gradient(135deg, rgba(0,0,0,0.95), rgba(0,0,0,0.4)), url('<?= url('assets/images/apple-banner-bg.jpg') ?>');">
            <h3>Apple Authorized Reseller</h3>
            <p>Trải nghiệm sản phẩm Apple chính hãng (VNA) tại hệ thống ủy quyền TechPilot với mức giá tốt nhất.</p>
            <a href="<?= url('home/search?cat=apple') ?>" class="btn btn--outline-light btn--sm">Khám phá ngay</a>
        </div>
        <?php foreach (array_slice($apple, 0, 4) as $p): ?>
            <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===== 12. TECHNOLOGY NEWS ===== -->
<section class="container section">
    <div class="section__head">
        <h2>Tin Tức Công Nghệ</h2>
        <a href="#" class="section__more">Xem thêm tin tức <i class="fa-solid fa-chevron-right"></i></a>
    </div>
    <div class="news-grid">
        <?php foreach ($posts as $post): ?>
            <div class="news-card">
                <a href="#" class="news-card__thumb">
                    <img src="<?= url('assets/images/news/' . e($post['image'])) ?>" alt="<?= e($post['title']) ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.outerHTML='<div style=\'background-color: var(--secondary); width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;\'><i class=\'fa-solid fa-newspaper\' style=\'font-size: 42px; color: var(--primary);\'></i></div>'">
                </a>
                <div class="news-card__body">
                    <span class="news-card__date"><i class="fa-regular fa-clock"></i> <?= date('d/m/Y', strtotime($post['created_at'])) ?></span>
                    <h3 class="news-card__title"><?= e($post['title']) ?></h3>
                    <p class="news-card__summary"><?= e($post['summary']) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

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
            foreach ($duplicatedBrands as $brand): ?>
                <div class="brand-logo-card" title="<?= e($brand['name']) ?>">
                    <img src="<?= url('assets/images/brands/' . e($brand['logo'])) ?>?v=3.0" alt="<?= e($brand['name']) ?>" onerror="this.outerHTML='<span><?= e($brand['name']) ?></span>'">
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