<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' . APP_NAME : APP_NAME ?></title>
    <!-- Logo Favicon -->
    <link rel="icon" type="image/png" href="<?= url('assets/images/logo.png') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link class="main-stylesheet" rel="stylesheet" href="<?= url('assets/css/style.css?v=18.7') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/category-mega-menu.css?v=1.5') ?>">
    <script>
        (() => {
            const stored = localStorage.getItem('techpilot-theme');
            const useDark = stored === 'dark' || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark-mode', useDark);
        })();
    </script>
</head>

<body>

    <!-- 1. Top Announcement Bar -->
    <div class="top-bar">
        <div class="container top-bar__inner">
            <div class="top-bar__left">
                <span><i class="fa-solid fa-truck-fast"></i> Miễn phí giao hàng toàn quốc</span>
            </div>
            <div class="top-bar__right">
                <span><i class="fa-solid fa-phone"></i> Hỗ trợ: 1800 9999 (miễn cước)</span>
            </div>
        </div>
    </div>

    <!-- 2. Main Header -->
    <header class="site-header">
        <div class="container site-header__inner">
            <!-- Hamburger menu toggle for mobile -->
            <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Menu Toggle">
                <i class="fa-solid fa-bars"></i>
            </button>

            <!-- Logo Thương hiệu -->
            <a href="<?= url('/') ?>" class="logo" style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
                <img src="<?= url('assets/images/logo.png') ?>" alt="TechPilot Logo" style="height: 40px; object-fit: contain; display: block;">
                <div class="logo-brand-info">
                    <span class="logo-brand-title">Tech<span>Pilot</span></span>
                    <span class="logo-brand-tagline">Technology • Trust • Future</span>
                </div>
            </a>

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

            <!-- Actions buttons (Locator, Wishlist, Cart, Account, Darkmode) -->
            <div class="header-actions">
                <button type="button" class="header-actions__item theme-toggle" id="themeToggle" aria-label="Chuyển chế độ tối">
                    <i class="fa-solid fa-moon"></i>
                    <span>Tối</span>
                </button>
                <a href="<?= url('post') ?>" class="header-actions__item">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Cửa hàng</span>
                </a>
                <a href="<?= url('profile/wishlist') ?>" class="header-actions__item header-actions__wishlist">
                    <i class="fa-regular fa-heart"></i>
                    <span>Yêu thích</span>
                </a>
                <a href="<?= url('cart') ?>" class="header-actions__item header-actions__cart">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span>Giỏ hàng</span>
                    <span class="cart-badge"><?= (int)cartCount() ?></span>
                </a>
                
                <?php if ($u = currentUser()): ?>
                    <div class="header-actions__item dropdown header-actions__account">
                        <i class="fa-solid fa-circle-user"></i>
                        <span><?= e($u['full_name']) ?></span>
                        <div class="dropdown__menu">
                            <a href="<?= url('profile') ?>"><i class="fa-solid fa-user"></i> Trang cá nhân</a>
                            <a href="<?= url('profile/orders') ?>"><i class="fa-solid fa-box-open"></i> Đơn hàng của tôi</a>
                            <a href="<?= url('auth/logout') ?>"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= url('auth/login') ?>" class="header-actions__item header-actions__account">
                        <i class="fa-regular fa-circle-user"></i>
                        <span>Tài khoản</span>
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
    </header>

    <div id="mainNavSentinel" aria-hidden="true"></div>

    <!-- 3. Navigation Menu -->
    <nav class="main-nav">
        <!-- Nút đóng Drawer trên Mobile -->
        <button class="mobile-drawer-close" id="mobileDrawerClose" aria-label="Đóng Menu">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <div class="container main-nav__inner">
            <button type="button" class="main-nav__categories" id="headerCategoryTrigger" aria-expanded="false" aria-controls="globalCategoryOverlay">
                <i class="fa-solid fa-bars"></i> Danh mục sản phẩm
            </button>
            
            <ul class="main-nav__links">
                <li><a href="<?= url('/') ?>" class="is-active">Trang chủ</a></li>
                <li class="desktop-only-link"><a href="<?= url('home/search?cat=laptop-gaming') ?>">PC Gaming</a></li>
                <li class="desktop-only-link"><a href="<?= url('home/search?cat=laptop-van-phong') ?>">Laptop</a></li>
                <li class="desktop-only-link"><a href="<?= url('home/search?cat=pc-linh-kien') ?>">Linh kiện PC</a></li>
                <li class="desktop-only-link"><a href="<?= url('home/search?cat=man-hinh') ?>">Màn hình</a></li>
                <li><a href="<?= url('home/search?cat=thiet-bi-mang') ?>" class="desktop-only-link">Thiết bị mạng</a></li>
                <li class="desktop-only-link"><a href="<?= url('home/search?cat=gaming-gear') ?>">Gaming Gear</a></li>
                <li class="desktop-only-link"><a href="<?= url('home/search?cat=thiet-bi-van-phong') ?>">Thiết bị văn phòng</a></li>
                <li><a href="<?= url('home/search') ?>" class="text-hot">Khuyến mãi <span class="dot-hot"></span></a></li>
                <li><a href="<?= url('post') ?>">Tin công nghệ</a></li>
            </ul>
        </div>
    </nav>

    <!-- Global Category Overlay -->
    <div id="globalCategoryOverlay" class="catalog-overlay" aria-hidden="true">
        <div class="catalog-overlay__backdrop" id="categoryBackdrop"></div>
        <div id="overlayCategorySlot">
            <?php require ROOT_PATH . '/app/views/components/category-menu.php'; ?>
        </div>
    </div>

    <main>