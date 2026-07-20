<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' . APP_NAME : APP_NAME ?></title>
    <!-- Logo Favicon -->
    <link rel="icon" type="image/png" href="<?= url('assets/images/logo.png') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link class="main-stylesheet" rel="stylesheet" href="<?= url('assets/css/style.css?v=18.9') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/category-mega-menu.css?v=1.7') ?>">
    <script>
        (() => {
            const stored = localStorage.getItem('techpilot-theme');
            const useDark = stored === 'dark' || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark-mode', useDark);
        })();
    </script>
</head>

<body>

    <?php require_once __DIR__ . '/partials/topbar.php'; ?>
    <div id="commerceHeaderSentinel" aria-hidden="true"></div>

    <div class="commerce-header-stack" id="commerceHeaderStack">
        <!-- 2. Main Header -->
        <header class="site-header">
        <div class="container site-header__inner">
            <!-- Hamburger menu toggle for mobile -->
            <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Menu Toggle">
                <i class="fa-solid fa-bars"></i>
            </button>

            <!-- Logo Thương hiệu -->
            <a href="<?= url('/') ?>" class="logo" style="display: flex; align-items: center; gap: 8px; text-decoration: none;">
                <img src="<?= url('assets/images/logo.png') ?>" alt="TechPilot Logo" style="height: 40px; object-fit: contain; display: block;">
                <div class="logo-brand-info">
                    <span class="logo-brand-title">Tech<span>Pilot</span></span>
                </div>
            </a>

            <!-- Nút Danh mục sản phẩm -->
            <button type="button" class="header-category-btn desktop-only-link" id="headerCategoryTrigger" aria-expanded="false" aria-controls="globalCategoryOverlay">
                <i class="fa-solid fa-bars"></i>
                <span class="desktop-only-link">Danh mục</span>
            </button>

            <!-- Search Bar với Category Dropdown -->
            <form class="search-bar" action="<?= url('home/search') ?>" method="get">
                <input type="text" name="q" placeholder="Bạn muốn mua gì hôm nay? Đang giảm giá 50%..." required>
                <select name="cat" class="search-bar__select">
                    <option value="">Tất cả danh mục</option>
                    <?php
                    $categoriesList = $globalCategories ?? [];
                    foreach ($categoriesList as $cat): ?>
                        <option value="<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>

            <!-- Actions buttons -->
            <div class="header-actions">
                <button type="button" class="header-actions__item theme-toggle" id="themeToggle" aria-label="Chuyển chế độ tối">
                    <i class="fa-solid fa-moon"></i>
                    <div class="header-actions__text">
                        <span>Giao</span>
                        <strong>Diện</strong>
                    </div>
                </button>

                <a href="<?= url('profile/wishlist') ?>" class="header-actions__item header-actions__wishlist desktop-only-link">
                    <i class="fa-regular fa-heart"></i>
                    <div class="header-actions__text">
                        <span>Yêu</span>
                        <strong>Thích</strong>
                    </div>
                </a>
                <a href="<?= url('cart') ?>" class="header-actions__item header-actions__cart">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <div class="header-actions__text">
                        <span>Giỏ</span>
                        <strong>Hàng</strong>
                    </div>
                    <span class="cart-badge"><?= (int)cartCount() ?></span>
                </a>
                
                <?php if ($u = currentUser()): ?>
                    <div class="header-actions__item dropdown header-actions__account">
                        <i class="fa-solid fa-circle-user"></i>
                        <span><?= e($u['full_name']) ?></span>
                        <div class="dropdown__menu">
                            <a href="<?= url('profile') ?>"><i class="fa-solid fa-user"></i> Trang cá nhân</a>
                            <a href="<?= url('profile/orders') ?>"><i class="fa-solid fa-box-open"></i> Đơn hàng của tôi</a>
                            <?php if (($u['role'] ?? '') === 'admin'): ?>
                                <a href="<?= url('admin') ?>" style="color: var(--primary); font-weight: 600;"><i class="fa-solid fa-user-shield"></i> Trang quản trị</a>
                            <?php endif; ?>
                            <a href="<?= url('auth/logout') ?>"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= url('auth/login') ?>" class="header-actions__item header-actions__account">
                        <i class="fa-regular fa-circle-user"></i>
                        <div class="header-actions__text">
                            <span>Đăng</span>
                            <strong>Nhập</strong>
                        </div>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- 2.5. Mobile Search and Quick Categories (Display: None on Desktop) -->
        <div class="mobile-search-container">
            <form class="mobile-search-bar" action="<?= url('home/search') ?>" method="get">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" name="q" placeholder="Tìm sản phẩm..." aria-label="Tìm sản phẩm" required>
            </form>
        </div>

        <div class="mobile-quick-categories">
            <a href="<?= url('home/search?cat=laptop-gaming') ?>" class="quick-cat-item">
                <i class="fa-solid fa-laptop"></i>
                <span>Laptop</span>
            </a>
            <a href="<?= url('home/search?cat=laptop-van-phong') ?>" class="quick-cat-item">
                <i class="fa-solid fa-desktop"></i>
                <span>PC</span>
            </a>
            <a href="<?= url('home/search?cat=pc-linh-kien') ?>" class="quick-cat-item">
                <i class="fa-solid fa-microchip"></i>
                <span>Linh kiện</span>
            </a>
            <a href="<?= url('home/search?cat=man-hinh') ?>" class="quick-cat-item">
                <i class="fa-solid fa-tv"></i>
                <span>Màn hình</span>
            </a>
            <a href="<?= url('home/search?cat=gaming-gear') ?>" class="quick-cat-item">
                <i class="fa-solid fa-gamepad"></i>
                <span>Gaming Gear</span>
            </a>
            <a href="#" class="quick-cat-item text-hot">
                <i class="fa-solid fa-fire"></i>
                <span>Khuyến mãi</span>
            </a>
        </div>

        <!-- 3. Navigation Menu -->
        <nav class="main-nav">
            <!-- Nút đóng Drawer trên Mobile -->
            <button class="mobile-drawer-close" id="mobileDrawerClose" aria-label="Đóng Menu">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div class="container main-nav__inner">
                <ul class="main-nav__links">
                    <li><a href="<?= url('/') ?>" class="is-active">Trang chủ</a></li>
                    <li class="desktop-only-link"><a href="<?= url('home/search?cat=laptop-gaming') ?>">PC Gaming</a></li>
                    <li class="desktop-only-link"><a href="<?= url('home/search?cat=laptop-van-phong') ?>">Laptop</a></li>
                    <li class="desktop-only-link"><a href="<?= url('home/search?cat=pc-linh-kien') ?>">Linh kiện PC</a></li>
                    <li class="desktop-only-link"><a href="<?= url('home/search?cat=man-hinh') ?>">Màn hình</a></li>
                    <li class="desktop-only-link"><a href="<?= url('build-pc') ?>" style="color: #FACC15; font-weight: 700;"><i class="fa-solid fa-screwdriver-wrench" style="margin-right: 4px;"></i> Xây dựng cấu hình</a></li>
                    <li class="desktop-only-link"><a href="<?= url('home/search?cat=gaming-gear') ?>">Gaming Gear</a></li>
                    <li class="desktop-only-link"><a href="<?= url('home/search?cat=thiet-bi-van-phong') ?>">Thiết bị văn phòng</a></li>
                    <li><a href="<?= url('home/search') ?>" class="text-hot">Khuyến mãi <span class="dot-hot"></span></a></li>
                    <li><a href="<?= url('post') ?>">Tin công nghệ</a></li>
                </ul>
            </div>
        </nav>
    </header>
    </div> <!-- Close commerce-header-stack -->

    <!-- Global Category Overlay -->
    <div id="globalCategoryOverlay" class="catalog-overlay" aria-hidden="true">
        <div class="catalog-overlay__backdrop" id="categoryBackdrop"></div>
        <div id="overlayCategorySlot">
            <?php require ROOT_PATH . '/app/views/components/category-menu.php'; ?>
        </div>
    </div>

    <main>