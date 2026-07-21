<?php
$currentUri = $_SERVER['REQUEST_URI'] ?? '';
$currentPath = parse_url($currentUri, PHP_URL_PATH);
if (defined('BASE_URL') && BASE_URL !== '' && strpos($currentPath, BASE_URL) === 0) {
    $currentPath = substr($currentPath, strlen(BASE_URL));
}
$currentPath = trim($currentPath, '/');

$qParam = $_GET['q'] ?? '';
$catParam = $_GET['cat'] ?? '';
$promoParam = $_GET['promo'] ?? '';

$activeMenu = '';
if ($currentPath === '' || $currentPath === 'home' || $currentPath === 'home/index') {
    $activeMenu = 'home';
} elseif (strpos($currentPath, 'build-pc') !== false) {
    $activeMenu = 'build-pc';
} elseif (strpos($currentPath, 'post') !== false) {
    $activeMenu = 'post';
} elseif ($currentPath === 'home/search') {
    if ($promoParam === '1') {
        $activeMenu = 'promo';
    } elseif ($catParam === 'laptop-gaming') {
        $activeMenu = 'laptop-gaming';
    } elseif ($catParam === 'laptop-van-phong') {
        $activeMenu = 'laptop-van-phong';
    } elseif ($catParam === 'pc-linh-kien') {
        $activeMenu = 'pc-linh-kien';
    } elseif ($catParam === 'man-hinh') {
        $activeMenu = 'man-hinh';
    } elseif ($catParam === 'gaming-gear') {
        $activeMenu = 'gaming-gear';
    } elseif ($catParam === 'office-gear') {
        $activeMenu = 'office-gear';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' . APP_NAME : APP_NAME ?></title>
    <?php if (isset($metaDescription)): ?>
        <meta name="description" content="<?= e($metaDescription) ?>">
    <?php endif; ?>
    <?php if (isset($canonicalUrl)): ?>
        <link rel="canonical" href="<?= e($canonicalUrl) ?>">
    <?php endif; ?>
    <?php if (isset($ogType)): ?>
        <meta property="og:type" content="<?= e($ogType) ?>">
    <?php endif; ?>
    <?php if (isset($ogTitle)): ?>
        <meta property="og:title" content="<?= e($ogTitle) ?>">
    <?php endif; ?>
    <?php if (isset($ogDescription)): ?>
        <meta property="og:description" content="<?= e($ogDescription) ?>">
    <?php endif; ?>
    <?php if (isset($ogUrl)): ?>
        <meta property="og:url" content="<?= e($ogUrl) ?>">
    <?php endif; ?>
    <?php if (isset($ogImage)): ?>
        <meta property="og:image" content="<?= e($ogImage) ?>">
    <?php endif; ?>
    <?php if (isset($twitterCard)): ?>
        <meta name="twitter:card" content="<?= e($twitterCard) ?>">
    <?php endif; ?>
    <?php if (isset($structuredData)): ?>
        <script type="application/ld+json">
<?= $structuredData ?>
        </script>
    <?php endif; ?>
    <!-- Logo Favicon -->
    <link rel="icon" type="image/png" href="<?= url('assets/images/logo.png') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link class="main-stylesheet" rel="stylesheet" href="<?= url('assets/css/style.css?v=19.0') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/category-mega-menu.css?v=2.0') ?>">
    <?php foreach ($pageStyles ?? [] as $stylesheet): ?>
        <link rel="stylesheet" href="<?= url($stylesheet) ?>">
    <?php endforeach; ?>
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
                <button class="mobile-menu-toggle" id="mobileMenuToggle" type="button" aria-label="Menu Toggle">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <!-- Logo Thương hiệu -->
                <a href="<?= url('/') ?>" class="logo" style="display: flex; align-items: center; gap: 1px; text-decoration: none;">
                    <img src="<?= url('assets/images/logo.png') ?>" alt="TechPilot Logo" style="height: 40px; object-fit: contain; display: block;">
                    <div class="logo-brand-info">
                        <span class="logo-brand-title">Tech<span>Pilot</span></span>
                    </div>
                </a>

                <!-- Nút Danh mục sản phẩm -->
                <button type="button" class="category-toggle desktop-only-link" id="categoryMenuToggle" aria-expanded="false" aria-controls="categoryMegaDropdown">
                    <i class="fa-solid fa-bars"></i>
                    <span class="desktop-only-link">Danh mục</span>
                    <i class="category-toggle__chevron fa-solid fa-chevron-down"></i>
                </button>

                <!-- Search Bar với Category Dropdown -->
                <form class="search-bar" action="<?= url('home/search') ?>" method="get" id="headerSearchForm" onsubmit="return cleanSearchParams(this)">
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
                        <div class="header-actions__icon-wrapper">
                            <i class="fa-solid fa-cart-shopping"></i>
                            <span class="cart-badge"><?= (int)cartCount() ?></span>
                        </div>
                        <div class="header-actions__text">
                            <span>Giỏ</span>
                            <strong>Hàng</strong>
                        </div>
                    </a>
                    
                    <?php if ($u = currentUser()): ?>
                        <?php
                        // Fetch unread notifications count dynamically
                        $unreadNotificationsCount = 0;
                        try {
                            require_once ROOT_PATH . '/config/database.php';
                            $db = Database::getConnection();
                            if ($db) {
                                $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0");
                                $stmt->execute([':user_id' => $u['id']]);
                                $unreadNotificationsCount = (int)$stmt->fetchColumn();
                            }
                        } catch (Exception $e) {
                            // Fail silently
                        }
                        ?>
                        <a href="<?= url('profile/notifications') ?>" class="header-actions__item header-actions__notifications" style="position: relative; text-decoration: none; color: inherit;">
                            <i class="fa-solid fa-bell"></i>
                            <div class="header-actions__text">
                                <span>Thông</span>
                                <strong>Báo</strong>
                            </div>
                            <?php if ($unreadNotificationsCount > 0): ?>
                                <span class="notification-badge" style="position: absolute; top: 0; right: 0; background-color: #EF4444; color: #FFFFFF; font-size: 10px; font-weight: 700; min-width: 16px; height: 16px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 1.5px solid var(--bg-card); padding: 0 3px; transform: translate(30%, -30%);"><?= $unreadNotificationsCount ?></span>
                            <?php endif; ?>
                        </a>

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
                <a href="<?= url('home/search?promo=1') ?>" class="quick-cat-item text-hot">
                    <i class="fa-solid fa-fire"></i>
                    <span>Khuyến mãi</span>
                </a>
            </div>

            <!-- 3. Navigation Menu -->
            <nav class="main-nav">
                <button class="mobile-drawer-close" id="mobileDrawerClose" type="button" aria-label="Đóng Menu">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <div class="container main-nav__inner">
                    <ul class="main-nav__links">
                        <li><a href="<?= url('/') ?>" class="<?= $activeMenu === 'home' ? 'is-active' : '' ?>">Trang chủ</a></li>
                        <li class="desktop-only-link"><a href="<?= url('home/search?cat=laptop-gaming') ?>" class="<?= $activeMenu === 'laptop-gaming' ? 'is-active' : '' ?>">PC Gaming</a></li>
                        <li class="desktop-only-link"><a href="<?= url('home/search?cat=laptop-van-phong') ?>" class="<?= $activeMenu === 'laptop-van-phong' ? 'is-active' : '' ?>">Laptop</a></li>
                        <li class="desktop-only-link"><a href="<?= url('home/search?cat=pc-linh-kien') ?>" class="<?= $activeMenu === 'pc-linh-kien' ? 'is-active' : '' ?>">Linh kiện PC</a></li>
                        <li class="desktop-only-link"><a href="<?= url('home/search?cat=man-hinh') ?>" class="<?= $activeMenu === 'man-hinh' ? 'is-active' : '' ?>">Màn hình</a></li>
                        <li class="desktop-only-link"><a href="<?= url('build-pc') ?>" class="<?= $activeMenu === 'build-pc' ? 'is-active' : '' ?>" style="color: #FACC15; font-weight: 700;"><i class="fa-solid fa-screwdriver-wrench" style="margin-right: 4px;"></i> Xây dựng cấu hình</a></li>
                        <li class="desktop-only-link"><a href="<?= url('home/search?cat=gaming-gear') ?>" class="<?= $activeMenu === 'gaming-gear' ? 'is-active' : '' ?>">Gaming Gear</a></li>
                        <li class="desktop-only-link"><a href="<?= url('home/search?cat=office-gear') ?>" class="<?= $activeMenu === 'office-gear' ? 'is-active' : '' ?>">Thiết bị văn phòng</a></li>
                        <li><a href="<?= url('home/search?promo=1') ?>" class="text-hot <?= $activeMenu === 'promo' ? 'is-active' : '' ?>">Khuyến mãi <span class="dot-hot"></span></a></li>
                        <li><a href="<?= url('post') ?>" class="<?= $activeMenu === 'post' ? 'is-active' : '' ?>">Tin công nghệ</a></li>
                    </ul>
                </div>
            </nav>


            <!-- 4. Category Mega Menu Dropdown -->
            <?php if ($activeMenu !== 'home'): ?>
                <?php require ROOT_PATH . '/app/views/layouts/partials/category-mega-menu.php'; ?>
            <?php endif; ?>
        </header>
    </div> <!-- Close commerce-header-stack -->

    <main>