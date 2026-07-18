<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' . APP_NAME : APP_NAME ?></title>
    <!-- Logo Favicon -->
    <link rel="icon" type="image/png" href="<?= url('assets/images/logo.png') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= url('assets/css/style.css?v=17.1') ?>">
</head>

<body>

    <!-- 1. Top Announcement Bar -->
    <div class="top-bar">
        <div class="container top-bar__inner">
            <div class="top-bar__left">
                <span><i class="fa-solid fa-truck-fast"></i> Miễn phí giao hàng toàn quốc</span>
                <span><i class="fa-solid fa-shield-halved"></i> Bảo hành chính hãng 100%</span>
                <span><i class="fa-solid fa-headset"></i> Hỗ trợ 24/7 toàn quốc</span>
                <span><i class="fa-solid fa-rotate-left"></i> Hoàn tiền 7 ngày đổi mới</span>
            </div>
            <div class="top-bar__right">
                <a href="#"><i class="fa-solid fa-credit-card"></i> Trả góp 0% qua thẻ tín dụng</a>
                <a href="#"><i class="fa-solid fa-circle-question"></i> Hỗ trợ mua hàng</a>
            </div>
        </div>
    </div>

    <!-- 2. Main Header -->
    <header class="site-header">
        <div class="container site-header__inner">
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
                <a href="#" class="header-actions__item">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Cửa hàng</span>
                </a>
                <a href="#" class="header-actions__item">
                    <i class="fa-regular fa-heart"></i>
                    <span>Yêu thích</span>
                </a>
                <a href="<?= url('cart') ?>" class="header-actions__item">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span>Giỏ hàng</span>
                    <span class="cart-badge"><?= (int)cartCount() ?></span>
                </a>
                
                <?php if ($u = currentUser()): ?>
                    <div class="header-actions__item dropdown">
                        <i class="fa-solid fa-circle-user"></i>
                        <span><?= e($u['full_name']) ?></span>
                        <div class="dropdown__menu">
                            <a href="<?= url('auth/logout') ?>"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= url('auth/login') ?>" class="header-actions__item">
                        <i class="fa-regular fa-circle-user"></i>
                        <span>Tài khoản</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- 3. Navigation Menu -->
        <nav class="main-nav">
            <div class="container main-nav__inner">
                <div class="main-nav__categories">
                    <i class="fa-solid fa-bars"></i> Danh mục sản phẩm
                    <div class="main-nav__categories-panel">
                        <?php foreach ($categories as $cat): ?>
                            <a href="<?= url('home/search?cat=' . $cat['slug']) ?>">
                                <i class="<?= e($cat['icon'] ?? 'fa-solid fa-tag') ?>"></i>
                                <?= e($cat['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <ul class="main-nav__links">
                    <li><a href="<?= url('/') ?>" class="is-active">Trang chủ</a></li>
                    <li><a href="<?= url('home/search?cat=laptop-gaming') ?>">PC Gaming</a></li>
                    <li><a href="<?= url('home/search?cat=laptop-van-phong') ?>">Laptop</a></li>
                    <li><a href="<?= url('home/search?cat=pc-linh-kien') ?>">Linh kiện PC</a></li>
                    <li><a href="<?= url('home/search?cat=man-hinh') ?>">Màn hình</a></li>
                    <li><a href="#">Thiết bị mạng</a></li>
                    <li><a href="<?= url('home/search?cat=gaming-gear') ?>">Gaming Gear</a></li>
                    <li><a href="#">Thiết bị văn phòng</a></li>
                    <li><a href="#" class="text-hot">Khuyến mãi cực hot <span class="dot-hot"></span></a></li>
                    <li><a href="#">Tin công nghệ</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main>